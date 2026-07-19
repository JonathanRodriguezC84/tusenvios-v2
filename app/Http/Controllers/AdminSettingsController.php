<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Http\Request;

class AdminSettingsController extends Controller
{
    public function activity(Request $request): \Illuminate\View\View
    {
        $this->authorize('access-admin');

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', 'max:50'],
            'tenant_id' => ['nullable', 'integer', 'exists:tenants,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $shipments = Shipment::query()
            ->with(['tenant'])
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(function ($q) use ($search) {
                $q->where('guide_number', 'like', "%{$search}%")
                  ->orWhere('recipient_name', 'like', "%{$search}%")
                  ->orWhereHas('tenant', fn ($t) => $t->where('name', 'like', "%{$search}%"));
            }))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['tenant_id'] ?? null, fn ($query, $tenantId) => $query->where('tenant_id', $tenantId))
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $tenants = \App\Models\Tenant::orderBy('name')->get(['id', 'name']);

        $statusLabels = [
            'created' => 'Por imprimir', 'printed' => 'Impresa', 'in_warehouse' => 'Preparando',
            'in_sorting' => 'Preparando', 'assigned' => 'Asignada', 'on_route' => 'En camino',
            'delivered' => 'Entregada', 'failed_delivery' => 'Novedad', 'rescheduled' => 'Reprogramada',
            'return_pending' => 'Devuelve', 'returned' => 'Devuelta', 'cancelled' => 'Cancelada',
        ];

        return view('admin.activity', compact('shipments', 'filters', 'statusLabels', 'tenants'));
    }

    public function exportActivity(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $this->authorize('access-admin');

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', 'max:50'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $fileName = 'actividad-admin-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($filters) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Guia', 'Cliente', 'Destinatario', 'Telefono', 'Direccion', 'Estado', 'Zona', 'Recaudo', 'Fecha']);

            Shipment::query()
                ->with(['tenant'])
                ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(function ($q) use ($search) {
                    $q->where('guide_number', 'like', "%{$search}%")
                      ->orWhere('recipient_name', 'like', "%{$search}%")
                      ->orWhereHas('tenant', fn ($t) => $t->where('name', 'like', "%{$search}%"));
                }))
                ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
                ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
                ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
                ->latest()
                ->chunk(200, function ($shipments) use ($handle) {
                    foreach ($shipments as $s) {
                        fputcsv($handle, [
                            $s->guide_number,
                            $s->tenant?->name,
                            $s->recipient_name . ' ' . $s->recipient_lastname,
                            $s->recipient_phone,
                            $s->recipient_address,
                            $s->status,
                            $s->zone,
                            $s->collection_value,
                            $s->created_at->format('Y-m-d H:i'),
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function systemSettings(): \Illuminate\View\View
    {
        $this->authorize('access-admin');

        return view('admin.settings');
    }

    public function updateSystemSettings(Request $request): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('access-admin');

        $validated = $request->validate([
            'trial_guide_limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        if (isset($validated['trial_guide_limit'])) {
            \Illuminate\Support\Facades\Cache::forever('system:trial_guide_limit', (int) $validated['trial_guide_limit']);
        }

        return back()->with('status', 'Configuracion guardada.');
    }

    public function carriers(): \Illuminate\View\View
    {
        $this->authorize('access-admin');

        $carriers = config('shipping.carriers', []);
        $rawKey = config('services.carrier_api.key');
        $apiKey = $rawKey ? substr($rawKey, 0, 4) . str_repeat('•', max(0, strlen($rawKey) - 4)) : '';
        $baseUrl = config('app.url', url('/'));

        return view('admin.carriers', compact('carriers', 'apiKey', 'rawKey', 'baseUrl'));
    }

    public function apiDocs(): \Illuminate\View\View
    {
        $this->authorize('access-admin');

        return view('admin.api-docs');
    }

    public function whatsapp(): \Illuminate\View\View
    {
        $this->authorize('access-admin');

        return view('admin.whatsapp');
    }

    public function profile(): \Illuminate\View\View
    {
        $this->authorize('access-admin');

        return view('admin.profile');
    }

    public function updatePassword(Request $request): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('access-admin');

        $validated = $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        auth()->user()->update(['password' => bcrypt($validated['password'])]);

        return back()->with('status', 'Contrasena actualizada correctamente.');
    }
}
