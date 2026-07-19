<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\ShipmentEvent;
use App\Http\Requests\BulkUpdateShipmentStatusRequest;
use App\Http\Requests\UpdateShipmentStatusRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Support\Audit;
use App\Jobs\SendWhatsAppNotification;

class ShipmentStatusController extends Controller
{
    public function updateStatus(UpdateShipmentStatusRequest $request, Shipment $shipment): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validated();

        if (! $shipment->canTransitionTo($validated['status'])) {
            return back()
                ->withErrors(['status' => 'Ese cambio de estado no esta permitido para esta guia.']);
        }

        $shipment->update(['status' => $validated['status']]);

        ShipmentEvent::query()->create([
            'shipment_id' => $shipment->id,
            'user_id' => Auth::id(),
            'status' => $validated['status'],
            'location' => 'Operacion',
            'notes' => $validated['notes'] ?? 'Estado actualizado desde el panel.',
        ]);

        Audit::log('shipment.status_updated', $shipment, "Guia {$shipment->guide_number} cambio a {$validated['status']}.");

        $whatsappEvent = match ($validated['status']) {
            'in_transit' => 'in_transit',
            'delivered' => 'delivered',
            default => null,
        };
        if ($whatsappEvent) {
            SendWhatsAppNotification::dispatch($shipment, $whatsappEvent);
        }

        $tenant = $shipment->tenant;
        if ($tenant && $tenant->webhook_url) {
            $events = $tenant->webhook_events ?? ['delivered', 'failed_delivery', 'cancelled'];
            if (in_array($validated['status'], $events)) {
                \App\Jobs\DispatchWebhook::dispatch($tenant->webhook_url, $shipment, $validated['status']);
            }
        }

        if ($request->boolean('daily_mode')) {
            $nextShipment = $this->nextDailyPendingShipment(Auth::user(), $shipment->id);

            if ($nextShipment) {
                return redirect()
                    ->route('shipments.show', ['shipment' => $nextShipment, 'daily' => 1])
                    ->with('status', 'Estado actualizado. Continuamos con la siguiente guia pendiente.');
            }

            return redirect()
                ->route('daily-tasks.index')
                ->with('status', 'Estado actualizado. Ya no quedan guias pendientes en tu jornada.');
        }

        return back()->with('status', 'Estado actualizado correctamente.');
    }

    public function cancel(Request $request, Shipment $shipment): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('cancel', $shipment);

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($shipment, $validated) {
            $shipment->update(['status' => 'cancelled']);

            app(ShipmentController::class)->restoreInventoryForShipment($shipment);

            ShipmentEvent::query()->create([
                'shipment_id' => $shipment->id,
                'user_id' => Auth::id(),
                'status' => 'cancelled',
                'location' => 'Sistema',
                'notes' => $validated['notes'] ?: 'Guia cancelada antes de impresion.',
            ]);

            Audit::log('shipment.cancelled', $shipment, "Guia {$shipment->guide_number} cancelada.");
        });

        return redirect()
            ->route('shipments.show', $shipment)
            ->with('status', 'Guia cancelada correctamente.');
    }

    public function bulkUpdateStatus(BulkUpdateShipmentStatusRequest $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validated();

        $shipments = Shipment::query()
            ->visibleTo(Auth::user())
            ->whereIn('id', $validated['shipment_ids'])
            ->get();

        $updated = 0;
        $skipped = 0;

        foreach ($shipments as $shipment) {
            if (! $shipment->canTransitionTo($validated['status'])) {
                $skipped++;
                continue;
            }

            $shipment->update(['status' => $validated['status']]);

            ShipmentEvent::query()->create([
                'shipment_id' => $shipment->id,
                'user_id' => Auth::id(),
                'status' => $validated['status'],
                'location' => 'Operacion',
                'notes' => 'Cambio de estado masivo.',
            ]);

            Audit::log('shipment.bulk_status_updated', $shipment, "Guia {$shipment->guide_number} actualizada masivamente a {$validated['status']}.");

            $updated++;
        }

        $message = "{$updated} guia(s) actualizada(s).";

        if ($skipped) {
            $message .= " {$skipped} guia(s) omitida(s) por estado no permitido.";
        }

        return redirect()
            ->route('shipments.index', $request->query())
            ->with('status', $message);
    }
}
