<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\Tenant;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShipmentApiController extends Controller
{
    public function store(Request $request)
    {
        $tenant = $request->__tenant;

        $validated = $request->validate([
            'recipient_name' => ['required', 'string', 'max:255'],
            'recipient_lastname' => ['nullable', 'string', 'max:255'],
            'recipient_phone' => ['required', 'string', 'max:50'],
            'recipient_address' => ['required', 'string', 'max:255'],
            'recipient_neighborhood' => ['nullable', 'string', 'max:255'],
            'recipient_locality' => ['nullable', 'string', 'max:255'],
            'recipient_city' => ['nullable', 'string', 'max:255'],
            'recipient_notes' => ['nullable', 'string', 'max:1000'],
            'content_description' => ['nullable', 'string', 'max:1000'],
            'package_type' => ['nullable', 'string', 'max:50'],
            'pieces' => ['nullable', 'integer', 'min:1'],
            'declared_value' => ['nullable', 'numeric', 'min:0'],
            'shipping_value' => ['nullable', 'numeric', 'min:0'],
            'collection_value' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'zone' => ['nullable', 'string', 'max:255'],
        ]);

        $shipment = DB::transaction(function () use ($tenant, $validated) {
            $guideNumber = $this->generateGuideNumber($tenant);

            $shipment = Shipment::create([
                'tenant_id' => $tenant->id,
                'guide_number' => $guideNumber,
                'status' => 'created',
            'sender_name' => $tenant->name,
            'sender_phone' => $tenant->phone,
            'sender_address' => $tenant->brandData()['address'] ?? 'Direccion principal',
            'sender_locality' => $tenant->brandData()['locality'] ?? 'Bogota',
            'recipient_name' => strtoupper($validated['recipient_name']),
            'recipient_lastname' => isset($validated['recipient_lastname']) ? strtoupper($validated['recipient_lastname']) : null,
            'recipient_phone' => $validated['recipient_phone'],
            'recipient_address' => strtoupper($validated['recipient_address']),
            'recipient_neighborhood' => isset($validated['recipient_neighborhood']) ? strtoupper($validated['recipient_neighborhood']) : null,
            'recipient_locality' => isset($validated['recipient_locality']) ? strtoupper($validated['recipient_locality']) : null,
            'recipient_city' => $validated['recipient_city'] ?? null,
            'recipient_notes' => $validated['recipient_notes'] ?? null,
            'content_description' => $validated['content_description'] ?? null,
            'package_type' => $validated['package_type'] ?? 'package',
            'pieces' => $validated['pieces'] ?? 1,
            'declared_value' => $validated['declared_value'] ?? 0,
            'shipping_value' => $validated['shipping_value'] ?? 0,
            'collection_value' => $validated['collection_value'] ?? 0,
            'payment_method' => $validated['payment_method'] ?? 'cod',
            'zone' => $validated['zone'] ?? null,
                'service_type' => 'standard',
            ]);

            Audit::log('shipment.api_created', $shipment, "Guia {$guideNumber} creada via API por {$tenant->name}.");

            return $shipment;
        });

        return response()->json([
            'success' => true,
            'guide_number' => $shipment->guide_number,
            'barcode' => $shipment->barcodeValue(),
            'label_url' => url("/track/{$shipment->guide_number}"),
            'tracking_url' => url("/track/{$shipment->guide_number}"),
        ], 201);
    }

    public function index(Request $request)
    {
        $tenant = $request->__tenant;

        $shipments = Shipment::query()
            ->where('tenant_id', $tenant->id)
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(max(1, min((int) ($request->per_page ?? 20), 100)));

        return response()->json($shipments);
    }

    public function show(Request $request, Shipment $shipment)
    {
        $tenant = $request->__tenant;

        if ($shipment->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        return response()->json([
            'guide_number' => $shipment->guide_number,
            'barcode' => $shipment->barcodeValue(),
            'status' => $shipment->status,
            'recipient_name' => $shipment->recipient_name . ' ' . $shipment->recipient_lastname,
            'recipient_phone' => $shipment->recipient_phone,
            'recipient_address' => $shipment->recipient_address,
            'recipient_locality' => $shipment->recipient_locality,
            'content_description' => $shipment->content_description,
            'pieces' => $shipment->pieces,
            'shipping_value' => $shipment->shipping_value,
            'collection_value' => $shipment->collection_value,
            'payment_method' => $shipment->payment_method,
            'zone' => $shipment->zone,
            'label_url' => url("/shipments/{$shipment->id}/print"),
            'tracking_url' => url("/track/{$shipment->guide_number}"),
            'created_at' => $shipment->created_at,
        ]);
    }

    private function generateGuideNumber(Tenant $tenant): string
    {
        $prefix = $tenant->guide_prefix ?: 'RCI';

        return DB::transaction(function () use ($tenant, $prefix) {
            $last = Shipment::where('tenant_id', $tenant->id)
                ->where('guide_number', 'like', strtoupper($prefix) . '%')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            $count = $last ? ((int) preg_replace('/[^0-9]/', '', substr($last->guide_number, -6))) + 1 : 1;

            return strtoupper($prefix) . now()->format('Y') . str_pad((string) $count, 6, '0', STR_PAD_LEFT);
        });
    }
}
