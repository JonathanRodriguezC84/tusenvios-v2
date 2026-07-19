<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\ShipmentEvent;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CarrierApiController extends Controller
{
    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'api_key' => ['required', 'string'],
        ]);

        $validKey = config('services.carrier_api.key');
        if (! hash_equals($validKey, $validated['api_key'])) {
            return response()->json(['message' => 'API key invalida'], 401);
        }

        $user = User::query()
            ->where('email', $validated['email'])
            ->where('role', 'courier')
            ->where('status', 'active')
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json(['message' => 'Credenciales invalidas'], 401);
        }

        return response()->json([
            'api_key' => $validKey,
            'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
        ]);
    }

    public function shipments(Request $request): \Illuminate\Http\JsonResponse
    {
        $courierId = $request->input('courier_id');
        if (! $courierId) {
            return response()->json(['message' => 'courier_id requerido'], 400);
        }

        $shipments = Shipment::query()
            ->with(['tenant', 'deliveryZone'])
            ->where('courier_id', $courierId)
            ->whereIn('status', ['assigned', 'on_route', 'failed_delivery'])
            ->latest('updated_at')
            ->paginate(20);

        return response()->json($shipments);
    }

    public function updateStatus(Request $request, Shipment $shipment): \Illuminate\Http\JsonResponse
    {
        $courierId = $request->input('courier_id');
        if ($shipment->courier_id !== (int) $courierId) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:on_route,delivered,failed_delivery,returned'],
            'notes' => ['nullable', 'string', 'max:500'],
            'location' => ['nullable', 'string', 'max:255'],
            'recipient_phone' => ['nullable', 'string'],
        ]);

        $previousStatus = $shipment->status;
        $shipment->update(['status' => $validated['status']]);

        ShipmentEvent::query()->create([
            'shipment_id' => $shipment->id,
            'user_id' => $courierId,
            'status' => $validated['status'],
            'location' => $validated['location'] ?? 'App movil',
            'notes' => $validated['notes'] ?? "Estado actualizado a {$validated['status']} via API",
        ]);

        if ($validated['recipient_phone'] ?? false) {
            app(NotificationService::class)->sendShipmentStatusUpdate(
                $shipment->guide_number,
                $validated['status'],
                $validated['recipient_phone']
            );
        }

        return response()->json([
            'message' => 'Estado actualizado',
            'shipment' => $shipment->fresh(),
        ]);
    }

    public function scan(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'guide_number' => ['required', 'string', 'max:50'],
        ]);

        $shipment = Shipment::query()
            ->with(['tenant', 'deliveryZone'])
            ->where('guide_number', $validated['guide_number'])
            ->first();

        if (! $shipment) {
            return response()->json(['message' => 'Guia no encontrada'], 404);
        }

        return response()->json($shipment);
    }
}
