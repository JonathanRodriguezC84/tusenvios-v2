<?php

namespace App\Http\Controllers;

use App\Models\AffiliatedCompany;
use App\Models\DeliveryZone;
use App\Models\Department;
use App\Models\InventoryProduct;
use App\Models\QuickProduct;
use App\Models\SenderProfile;
use App\Models\Shipment;
use App\Models\ShipmentEvent;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Support\Audit;
use App\Models\FrequentRecipient;
use App\Jobs\SendWhatsAppNotification;

class ShipmentController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', 'max:50'],
            'settlement_status' => ['nullable', 'in:pending,closed,paid'],
            'zone' => ['nullable', 'string', 'max:120'],
            'delivery_zone_id' => ['nullable', 'integer', 'exists:delivery_zones,id'],
            'date' => ['nullable', 'date'],
        ]);

        $shipments = $this->filteredShipments($filters)
            ->latest()
            ->paginate(15)
            ->withQueryString();
        $couriers = User::query()
            ->where('role', 'courier')
            ->where('status', 'active')
            ->when(! Auth::user()->isSuperAdmin(), fn ($query) => $query->where('tenant_id', Auth::user()->tenant_id))
            ->orderBy('name')
            ->get();
        $deliveryZones = $this->deliveryZonesForTenant(Auth::user()->tenant);

        return view('shipments.index', compact('shipments', 'filters', 'couriers', 'deliveryZones'));
    }

    public function export(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', 'max:50'],
            'settlement_status' => ['nullable', 'in:pending,closed,paid'],
            'zone' => ['nullable', 'string', 'max:120'],
            'delivery_zone_id' => ['nullable', 'integer', 'exists:delivery_zones,id'],
            'date' => ['nullable', 'date'],
        ]);

        $fileName = 'guias-tus-envios-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($filters) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Guia',
                'Codigo de barras',
                'Cliente',
                'Destinatario',
                'Telefono',
                'Direccion',
                'Zona',
                'Tarifa',
                'Estado',
                'Liquidacion',
                'Estado liquidacion',
                'Mensajero',
                'Valor envio',
                'Valor recaudo',
                'Fecha',
            ]);

            $this->filteredShipments($filters)
                ->latest()
                ->chunk(200, function ($shipments) use ($handle) {
                    foreach ($shipments as $shipment) {
                        $settlement = $shipment->settlementItems->first()?->settlement;
                        $settlementNumber = $settlement?->settlement_number
                            ?? ($shipment->affiliated_company_id ? 'Pendiente' : 'No aplica');
                        $settlementStatus = $settlement
                            ? ($settlement->status === 'paid' ? 'Pagada' : 'Pendiente de pago')
                            : ($shipment->affiliated_company_id ? 'Pendiente' : 'No aplica');

                        fputcsv($handle, [
                            $shipment->guide_number,
                            $shipment->barcodeValue(),
                            $shipment->affiliatedCompany?->name ?? 'RCI',
                            $shipment->recipient_name . ' ' . $shipment->recipient_lastname,
                            $shipment->recipient_phone,
                            $shipment->recipient_address,
                            $shipment->zone,
                            $shipment->deliveryZone?->name ?? 'Manual',
                            $shipment->status,
                            $settlementNumber,
                            $settlementStatus,
                            $shipment->courier?->name,
                            $shipment->shipping_value,
                            $shipment->collection_value,
                            $shipment->created_at->format('Y-m-d H:i:s'),
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportPdf(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', 'max:50'],
            'settlement_status' => ['nullable', 'in:pending,closed,paid'],
            'zone' => ['nullable', 'string', 'max:120'],
            'delivery_zone_id' => ['nullable', 'integer', 'exists:delivery_zones,id'],
            'date' => ['nullable', 'date'],
        ]);

        $shipments = $this->filteredShipments($filters)->latest()->get();

        $statusLabels = [
            'created' => 'Por imprimir', 'printed' => 'Impresa', 'in_warehouse' => 'Preparando',
            'in_sorting' => 'Preparando', 'assigned' => 'Asignada', 'on_route' => 'En camino',
            'delivered' => 'Entregada', 'failed_delivery' => 'Novedad', 'rescheduled' => 'Reprogramada',
            'return_pending' => 'Devuelve', 'returned' => 'Devuelta', 'cancelled' => 'Cancelada',
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('shipments.export-pdf', compact('shipments', 'statusLabels'));

        return $pdf->download('guias-tus-envios-'.now()->format('Y-m-d').'.pdf');
    }

    public function report(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', 'max:50'],
            'settlement_status' => ['nullable', 'in:pending,closed,paid'],
            'zone' => ['nullable', 'string', 'max:120'],
            'delivery_zone_id' => ['nullable', 'integer', 'exists:delivery_zones,id'],
            'date' => ['nullable', 'date'],
        ]);

        $shipments = $this->filteredShipments($filters)
            ->latest()
            ->limit(500)
            ->get();

        return view('shipments.report', compact('shipments', 'filters'));
    }

    private function filteredShipments(array $filters)
    {
        return Shipment::query()
            ->with(['affiliatedCompany', 'courier', 'deliveryZone', 'settlementItems.settlement'])
            ->visibleTo(Auth::user())
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $normalized = strtoupper(trim($search));
                    $compact = str_replace('-', '', $normalized);

                    $query
                        ->where('guide_number', 'like', "%{$search}%")
                        ->orWhereRaw("REPLACE(guide_number, '-', '') LIKE ?", ["%{$compact}%"])
                        ->orWhere('recipient_name', 'like', "%{$search}%")
                        ->orWhere('recipient_lastname', 'like', "%{$search}%")
                        ->orWhere('recipient_phone', 'like', "%{$search}%")
                        ->orWhere('recipient_address', 'like', "%{$search}%")
                        ->orWhereHas('affiliatedCompany', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when(($filters['settlement_status'] ?? null) === 'pending', function ($query) {
                $query
                    ->whereNotNull('affiliated_company_id')
                    ->whereDoesntHave('settlementItems');
            })
            ->when(($filters['settlement_status'] ?? null) === 'closed', function ($query) {
                $query->whereHas('settlementItems.settlement', fn ($query) => $query->where('status', 'closed'));
            })
            ->when(($filters['settlement_status'] ?? null) === 'paid', function ($query) {
                $query->whereHas('settlementItems.settlement', fn ($query) => $query->where('status', 'paid'));
            })
            ->when($filters['zone'] ?? null, fn ($query, $zone) => $query->where('zone', 'like', "%{$zone}%"))
            ->when($filters['delivery_zone_id'] ?? null, fn ($query, $deliveryZoneId) => $query->where('delivery_zone_id', $deliveryZoneId))
            ->when($filters['date'] ?? null, fn ($query, $date) => $query->whereDate('created_at', $date));
    }

    public function create()
    {
        abort_unless(Auth::user()->canCreateShipments(), 403);

        $tenant = Auth::user()->tenant_id
            ? Tenant::query()->find(Auth::user()->tenant_id)
            : Tenant::query()->where('subdomain', 'demo-tus-envios')->first();

        $companies = AffiliatedCompany::query()
            ->when($tenant, fn ($query) => $query->where('tenant_id', $tenant->id))
            ->when(Auth::user()->role === 'affiliate', fn ($query) => $query->where('id', Auth::user()->affiliated_company_id))
            ->orderBy('name')
            ->get();

        $deliveryZones = $this->deliveryZonesForTenant($tenant);
        $departments = Department::orderBy('name')->get(['id', 'name']);
        $deliveryZoneSuggestions = $this->deliveryZoneSuggestions($deliveryZones);
        $companyTerms = $this->companyTerms($companies);
        ['presets' => $senderPresets, 'companyDefaults' => $companySenderPresetKeys] = $this->senderPresetData($tenant, $companies);
        $quickProducts = $this->quickProductsForUser();
        $inventoryProducts = $this->inventoryProductsForUser();

        $useInventory = Auth::user()->canUseInventory();
        $planCode = $useInventory ? 'fundador' : 'emprende';

        return view('shipments.create', compact('companies', 'departments', 'deliveryZones', 'deliveryZoneSuggestions', 'companyTerms', 'senderPresets', 'companySenderPresetKeys', 'quickProducts', 'inventoryProducts', 'useInventory', 'planCode'));
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->canCreateShipments(), 403);

        $validated = $request->validate([
            'sender_name' => ['required', 'string', 'max:255'],
            'sender_phone' => ['required', 'string', 'max:50'],
            'sender_address' => ['required', 'string', 'max:255'],
            'sender_neighborhood' => ['nullable', 'string', 'max:255'],
            'sender_locality' => ['nullable', 'string', 'max:255'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'recipient_lastname' => ['required', 'string', 'max:255'],
            'recipient_phone' => ['required', 'string', 'max:50'],
            'recipient_alt_phone' => ['nullable', 'string', 'max:50'],
            'recipient_email' => ['nullable', 'email', 'max:255'],
            'recipient_address' => ['required', 'string', 'max:255'],
            'recipient_neighborhood' => ['required', 'string', 'max:255'],
            'recipient_department' => ['nullable', 'string', 'max:255'],
            'recipient_locality' => ['required', 'string', 'max:255'],
            'recipient_city' => ['nullable', 'string', 'max:255'],
            'package_type' => ['required', 'string', 'max:50'],
            'pieces' => ['required', 'integer', 'min:1'],
            'content_description' => ['nullable', 'string', 'max:1000'],
            'declared_value' => ['nullable', 'numeric', 'min:0'],
            'shipping_value' => ['nullable', 'numeric', 'min:0'],
            'delivery_zone_id' => ['nullable', 'exists:delivery_zones,id'],
            'payment_method' => ['required', 'string', 'max:50'],
            'collection_value' => ['nullable', 'numeric', 'min:0'],
            'zone' => ['nullable', 'string', 'max:255'],
            'recipient_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $tenant = Auth::user()->tenant_id
            ? Tenant::query()->findOrFail(Auth::user()->tenant_id)
            : Tenant::query()->where('subdomain', 'demo-tus-envios')->firstOrFail();

        $subscription = $tenant->currentSubscription;
        if ($subscription && $subscription->isTrial() && ! $subscription->canCreateTrialGuide()) {
            return redirect()
                ->route('billing.blocked')
                ->with('status', 'Ya usaste tus 10 guias gratis. Activa tu plan para crear mas guias.');
        }

        if (Auth::user()->role === 'affiliate') {
            $validated['affiliated_company_id'] = Auth::user()->affiliated_company_id;
        }

        $inventoryItems = $this->inventoryItemsFromRequest($request);

        $validated = $this->applySenderPresetFromRequest($request, $validated, $tenant);
        $validated = $this->applyCompanyTerms($validated);
        $validated = $this->applyDeliveryZoneRate($validated, $tenant);
        $recipientLocalityForZone = trim((string) ($validated['recipient_locality'] ?? ''));
        if ($recipientLocalityForZone !== '') {
            $validated['zone'] = strtoupper($recipientLocalityForZone);
        }
        $validated = $this->normalizeShipmentText($validated);

        $shipment = DB::transaction(function () use ($validated, $tenant, $inventoryItems) {
            $prefix = $this->guidePrefix($validated['affiliated_company_id'] ?? null, $tenant);
            $year = now()->format('Y');
            $baseGuide = $prefix.$year;
            $sequenceLength = 5;

            $lastGuide = Shipment::query()
                ->where('guide_number', 'like', "{$baseGuide}%")
                ->orderByDesc('id')
                ->value('guide_number');

            $lastSequence = $lastGuide && preg_match('/^'.preg_quote($baseGuide, '/').'(\d{'.$sequenceLength.'})$/', $lastGuide, $matches)
                ? (int) $matches[1]
                : 0;

            $guideNumber = $baseGuide.str_pad((string) ($lastSequence + 1), $sequenceLength, '0', STR_PAD_LEFT);

            $shipment = Shipment::query()->create(array_merge($validated, [
                'tenant_id' => $tenant->id,
                'created_by' => Auth::id(),
                'guide_number' => $guideNumber,
                'status' => 'created',
                'declared_value' => $validated['declared_value'] ?? 0,
                'shipping_value' => $validated['shipping_value'] ?? 0,
                'collection_value' => $validated['collection_value'] ?? 0,
            ]));

            ShipmentEvent::query()->create([
                'shipment_id' => $shipment->id,
                'user_id' => Auth::id(),
                'status' => 'created',
                'location' => 'Sistema',
                'notes' => 'Guia creada desde el panel.',
            ]);

Audit::log('shipment.created', $shipment, "Guia {$shipment->guide_number} creada.");

            FrequentRecipient::createFromShipment($shipment);

            SendWhatsAppNotification::dispatch($shipment, 'created');

            $this->reserveInventoryForShipment($shipment, $inventoryItems);

            return $shipment;
        });

        if ($subscription && $subscription->isTrial()) {
            $subscription->refresh();
            $subscription->markGuideCreated();
        }

        return redirect()
            ->route('shipments.index')
            ->with('status', "Guia {$shipment->guide_number} creada correctamente.");
    }

    private function guidePrefix(?int $affiliatedCompanyId, Tenant $tenant): string
    {
        $name = $tenant->name;
        $customPrefix = $tenant->guide_prefix;

        if ($affiliatedCompanyId) {
            $company = AffiliatedCompany::query()->find($affiliatedCompanyId);
            $name = $company?->name ?? $name;
            $customPrefix = $company?->guide_prefix ?: $customPrefix;
        }

        if ($customPrefix) {
            return strtoupper($customPrefix);
        }

        $letters = preg_replace('/[^A-Za-z]/', '', $name);
        $prefix = strtoupper(substr($letters ?: 'TE', 0, 3));

        return str_pad($prefix, 3, 'X');
    }

    private function deliveryZonesForTenant(?Tenant $tenant)
    {
        return DeliveryZone::query()
            ->where('status', 'active')
            ->where(function ($query) use ($tenant) {
                $query->whereNull('tenant_id');

                if ($tenant) {
                    $query->orWhere('tenant_id', $tenant->id);
                }
            })
            ->orderBy('price')
            ->orderBy('name')
            ->get();
    }

    private function applyDeliveryZoneRate(array $validated, ?Tenant $tenant): array
    {
        if (empty($validated['delivery_zone_id'])) {
            return $validated;
        }

        $deliveryZone = DeliveryZone::query()
            ->where('status', 'active')
            ->where('id', $validated['delivery_zone_id'])
            ->where(function ($query) use ($tenant) {
                $query->whereNull('tenant_id');

                if ($tenant) {
                    $query->orWhere('tenant_id', $tenant->id);
                }
            })
            ->first();

        if (! $deliveryZone) {
            throw ValidationException::withMessages([
                'delivery_zone_id' => 'La tarifa seleccionada no esta disponible.',
            ]);
        }

        $validated['shipping_value'] = $deliveryZone->price;

        return $validated;
    }

    private function deliveryZoneSuggestions($deliveryZones): array
    {
        return $deliveryZones
            ->map(fn ($zone) => [
                'id' => $zone->id,
                'name' => $zone->name,
                'price' => (int) $zone->price,
                'keywords' => $zone->coverage_keywords,
            ])
            ->values()
            ->all();
    }

    private function companyTerms($companies): array
    {
        return $companies
            ->mapWithKeys(fn ($company) => [
                $company->id => [
                    'default_payment_method' => $company->default_payment_method,
                    'allows_cod' => $company->allows_cod,
                ],
            ])
            ->all();
    }

    private function quickProductsForUser()
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id ?: Tenant::query()->where('subdomain', 'demo-tus-envios')->value('id');

        $products = QuickProduct::query()
            ->where('status', 'active')
            ->when(
                $user->role === 'affiliate' && $user->affiliated_company_id,
                fn ($query) => $query->where('affiliated_company_id', $user->affiliated_company_id),
                fn ($query) => $query->where('tenant_id', $tenantId)->whereNull('affiliated_company_id')
            )
            ->orderBy('name')
            ->get();

        if ($products->isNotEmpty()) {
            return $products;
        }

        return collect([
            new QuickProduct(['name' => 'Ropa', 'package_type' => 'merchandise']),
            new QuickProduct(['name' => 'Accesorios', 'package_type' => 'merchandise']),
            new QuickProduct(['name' => 'Cosmeticos', 'package_type' => 'merchandise']),
            new QuickProduct(['name' => 'Zapatos', 'package_type' => 'package']),
            new QuickProduct(['name' => 'Pedido mixto', 'package_type' => 'package']),
        ]);
    }

    private function inventoryProductsForUser()
    {
        if (! Auth::user()->canUseInventory()) {
            return collect();
        }

return $this->inventoryQueryForUser()
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get();
    }

    private function inventoryItemsFromRequest(Request $request): array
    {
        if (! Auth::user()->canUseInventory()) {
            return [];
        }

        $rawItems = json_decode((string) $request->input('inventory_items', '[]'), true);

        if (! is_array($rawItems)) {
            return [];
        }

        $items = [];

        foreach ($rawItems as $item) {
            $productId = (int) ($item['id'] ?? 0);
            $quantity = (int) ($item['quantity'] ?? 0);

            if ($productId <= 0 || $quantity <= 0) {
                continue;
            }

            $items[$productId] = ($items[$productId] ?? 0) + $quantity;
        }

        return collect($items)
            ->map(fn ($quantity, $productId) => ['id' => (int) $productId, 'quantity' => (int) $quantity])
            ->values()
            ->all();
    }

    private function reserveInventoryForShipment(Shipment $shipment, array $inventoryItems): void
    {
        if (empty($inventoryItems)) {
            return;
        }

        $snapshot = [];

        foreach ($inventoryItems as $item) {
            $product = $this->inventoryQueryForUser()
                ->where('id', $item['id'])
                ->lockForUpdate()
                ->first();

            if (! $product) {
                throw ValidationException::withMessages([
                    'inventory_items' => 'Uno de los productos de inventario ya no esta disponible. Actualiza la pagina y vuelve a seleccionarlo.',
                ]);
            }

            if ($product->status !== 'active') {
                throw ValidationException::withMessages([
                    'inventory_items' => "{$product->name} esta pausado en inventario. Activalo antes de crear la guia.",
                ]);
            }

            if ($product->stock < $item['quantity']) {
                throw ValidationException::withMessages([
                    'inventory_items' => "No hay stock suficiente para {$product->name}. Disponible: {$product->stock}.",
                ]);
            }

            $product->decrement('stock', $item['quantity']);
            $product->refresh();

            $snapshot[] = [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'category' => $product->category,
                'cost' => (float) $product->cost,
                'price' => (float) $product->price,
                'quantity' => (int) $item['quantity'],
                'stock_after' => (int) $product->stock,
            ];

            $product->movements()->create([
                'tenant_id' => $product->tenant_id,
                'affiliated_company_id' => $product->affiliated_company_id,
                'shipment_id' => $shipment->id,
                'type' => 'shipment',
                'quantity_delta' => -1 * $item['quantity'],
                'stock_after' => $product->stock,
                'notes' => "Descuento por guia {$shipment->guide_number}",
            ]);
        }

        if ($snapshot) {
            $shipment->update(['inventory_snapshot' => $snapshot]);
        }
    }

    private function restoreInventoryForShipment(Shipment $shipment): void
    {
        if ($shipment->inventoryMovements()->where('type', 'restock')->exists()) {
            return;
        }

        $movements = $shipment->inventoryMovements()
            ->where('type', 'shipment')
            ->with('product')
            ->get();

        foreach ($movements as $movement) {
            $product = $movement->product;

            if (! $product) {
                continue;
            }

            $restoreQuantity = abs((int) $movement->quantity_delta);
            $product->increment('stock', $restoreQuantity);
            $product->refresh();

            $product->movements()->create([
                'tenant_id' => $product->tenant_id,
                'affiliated_company_id' => $product->affiliated_company_id,
                'shipment_id' => $shipment->id,
                'type' => 'restock',
                'quantity_delta' => $restoreQuantity,
                'stock_after' => $product->stock,
                'notes' => "Reposicion por cancelacion de guia {$shipment->guide_number}",
            ]);
        }
    }

    private function inventoryQueryForUser()
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id ?: Tenant::query()->where('subdomain', 'demo-tus-envios')->value('id');

        return InventoryProduct::query()
            ->when(
                $user->role === 'affiliate' && $user->affiliated_company_id,
                fn ($query) => $query->where('affiliated_company_id', $user->affiliated_company_id),
                fn ($query) => $query->where('tenant_id', $tenantId)->whereNull('affiliated_company_id')
            );
    }

    private function senderPresetData(?Tenant $tenant, $companies): array
    {
        $presets = [
            'default' => [
                'label' => 'Tus Envios',
                'affiliated_company_id' => '',
                'name' => $tenant?->name ?? 'RCI',
                'phone' => $tenant?->phone ?? '',
                'address' => 'Bodega principal Bogota',
                'neighborhood' => '',
                'locality' => 'Bogota',
            ],
        ];
        $companyDefaultKeys = ['' => 'default'];

        $savedSenders = SenderProfile::query()
            ->with('affiliatedCompany')
            ->where('status', 'active')
            ->when($tenant, fn ($query) => $query->where('tenant_id', $tenant->id))
            ->where(function ($query) use ($companies) {
                $query->whereNull('affiliated_company_id')
                    ->orWhereIn('affiliated_company_id', $companies->pluck('id'));
            })
            ->orderByDesc('is_default')
            ->orderBy('label')
            ->get();

        foreach ($savedSenders as $sender) {
            $key = 'sender_'.$sender->id;
            $companyId = $sender->affiliated_company_id ? (string) $sender->affiliated_company_id : '';
            $presets[$key] = [
                'label' => $sender->affiliatedCompany?->name
                    ? $sender->affiliatedCompany->name.' - '.$sender->label
                    : $sender->label,
                'affiliated_company_id' => $companyId,
                'name' => $sender->name,
                'phone' => $sender->phone ?? '',
                'address' => $sender->address,
                'neighborhood' => $sender->neighborhood ?? '',
                'locality' => $sender->locality ?? '',
            ];

            if ($sender->is_default || ! isset($companyDefaultKeys[$companyId])) {
                $companyDefaultKeys[$companyId] = $key;
            }
        }

        foreach ($companies as $company) {
            $key = 'company_'.$company->id;
            $presets[$key] = [
                'label' => $company->name,
                'affiliated_company_id' => (string) $company->id,
                'name' => $company->name,
                'phone' => $company->phone ?? '',
                'address' => 'Bodega principal Bogota',
                'neighborhood' => '',
                'locality' => 'Bogota',
            ];

            $companyDefaultKeys[(string) $company->id] ??= $key;
        }

        return [
            'presets' => $presets,
            'companyDefaults' => $companyDefaultKeys,
        ];
    }

    private function applySenderPresetFromRequest(Request $request, array $validated, ?Tenant $tenant): array
    {
        $preset = $request->input('sender_preset');

        if (! is_string($preset) || ! str_starts_with($preset, 'sender_')) {
            return $validated;
        }

        $senderId = (int) str_replace('sender_', '', $preset);
        $sender = SenderProfile::query()
            ->where('id', $senderId)
            ->where('status', 'active')
            ->when($tenant, fn ($query) => $query->where('tenant_id', $tenant->id))
            ->first();

        if (! $sender) {
            return $validated;
        }

        if (Auth::user()->role === 'affiliate' && $sender->affiliated_company_id !== Auth::user()->affiliated_company_id) {
            return $validated;
        }

        $validated['affiliated_company_id'] = $sender->affiliated_company_id;
        $validated['sender_name'] = $sender->name;
        $validated['sender_phone'] = $sender->phone;
        $validated['sender_address'] = $sender->address;
        $validated['sender_neighborhood'] = $sender->neighborhood;
        $validated['sender_locality'] = $sender->locality;

        return $validated;
    }

    private function applyCompanyTerms(array $validated): array
    {
        if (empty($validated['affiliated_company_id'])) {
            return $validated;
        }

        $company = AffiliatedCompany::query()->find($validated['affiliated_company_id']);

        if ($company && ! $company->allows_cod && $validated['payment_method'] === 'cod') {
            $validated['payment_method'] = $company->default_payment_method === 'cod'
                ? 'cash'
                : $company->default_payment_method;
            $validated['collection_value'] = 0;
        }

        return $validated;
    }

private function normalizeShipmentText(array $validated): array
    {
        $titleFields = [
            'sender_name',
            'recipient_name',
            'recipient_lastname',
            'sender_address',
            'recipient_address',
            'sender_neighborhood',
            'recipient_neighborhood',
            'sender_locality',
            'recipient_locality',
            'recipient_city',
            'content_description',
            'zone',
            'recipient_notes',
        ];

        foreach ($titleFields as $field) {
            if (! array_key_exists($field, $validated) || ! is_string($validated[$field])) {
                continue;
            }

            $value = trim($validated[$field]);
            $validated[$field] = function_exists('mb_convert_case')
                ? mb_convert_case($value, MB_CASE_TITLE, 'UTF-8')
                : ucwords(strtolower($value));
        }

        return $validated;
    }
    public function show(Shipment $shipment)
    {
        abort_unless($shipment->isVisibleTo(Auth::user()), 403);

        $shipment->load([
            'affiliatedCompany',
            'tenant',
            'creator',
            'courier',
            'deliveryZone',
            'events.user',
            'inventoryMovements.product',
            'settlementItems.settlement.affiliatedCompany',
        ]);
        $couriers = User::query()
            ->where('role', 'courier')
            ->where('status', 'active')
            ->when(! Auth::user()->isSuperAdmin(), fn ($query) => $query->where('tenant_id', Auth::user()->tenant_id))
            ->orderBy('name')
            ->get();
$nextStatuses = Shipment::STATUS_FLOW[$shipment->status] ?? [];
        $printFormats = $this->printFormats();

        return view('shipments.show', compact('shipment', 'couriers', 'nextStatuses', 'printFormats'));
    }

    public function edit(Shipment $shipment)
    {
        abort_unless($shipment->isVisibleTo(Auth::user()), 403);
        abort_unless(Auth::user()->canEditShipments(), 403);
        abort_unless($shipment->canBeEdited(), 403);

        $tenant = Auth::user()->tenant_id
            ? Tenant::query()->find(Auth::user()->tenant_id)
            : $shipment->tenant;

        $companies = AffiliatedCompany::query()
            ->when($tenant, fn ($query) => $query->where('tenant_id', $tenant->id))
            ->when(Auth::user()->role === 'affiliate', fn ($query) => $query->where('id', Auth::user()->affiliated_company_id))
            ->orderBy('name')
            ->get();

        $deliveryZones = $this->deliveryZonesForTenant($tenant);
        $deliveryZoneSuggestions = $this->deliveryZoneSuggestions($deliveryZones);
        ['presets' => $senderPresets, 'companyDefaults' => $companySenderPresetKeys] = $this->senderPresetData($tenant, $companies);
        $shipment->load('inventoryMovements.product');
        $usesInventory = $shipment->inventoryMovements->where('type', 'shipment')->isNotEmpty();

        $senderPresets = array_merge([
            'current' => [
                'label' => 'Remitente actual',
                'affiliated_company_id' => (string) ($shipment->affiliated_company_id ?? ''),
                'name' => $shipment->sender_name,
                'phone' => $shipment->sender_phone ?? '',
                'address' => $shipment->sender_address,
                'neighborhood' => $shipment->sender_neighborhood ?? '',
                'locality' => $shipment->sender_locality ?? '',
            ],
        ], $senderPresets);

        return view('shipments.edit', compact('shipment', 'companies', 'deliveryZones', 'deliveryZoneSuggestions', 'senderPresets', 'companySenderPresetKeys', 'usesInventory'));
    }

    public function update(Request $request, Shipment $shipment)
    {
        abort_unless($shipment->isVisibleTo(Auth::user()), 403);
        abort_unless(Auth::user()->canEditShipments(), 403);
        abort_unless($shipment->canBeEdited(), 403);

        $validated = $request->validate([
            'affiliated_company_id' => ['nullable', 'exists:affiliated_companies,id'],
            'service_type' => ['required', 'string', 'max:50'],
            'sender_name' => ['required', 'string', 'max:255'],
            'sender_phone' => ['nullable', 'string', 'max:50'],
            'sender_address' => ['required', 'string', 'max:255'],
            'sender_neighborhood' => ['nullable', 'string', 'max:255'],
            'sender_locality' => ['nullable', 'string', 'max:255'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'recipient_lastname' => ['nullable', 'string', 'max:255'],
            'recipient_phone' => ['required', 'string', 'max:50'],
            'recipient_alt_phone' => ['nullable', 'string', 'max:50'],
            'recipient_address' => ['required', 'string', 'max:255'],
            'recipient_neighborhood' => ['nullable', 'string', 'max:255'],
            'recipient_locality' => ['nullable', 'string', 'max:255'],
            'recipient_city' => ['nullable', 'string', 'max:255'],
            'package_type' => ['required', 'string', 'max:50'],
            'pieces' => ['required', 'integer', 'min:1'],
            'content_description' => ['nullable', 'string', 'max:1000'],
            'declared_value' => ['nullable', 'numeric', 'min:0'],
            'shipping_value' => ['nullable', 'numeric', 'min:0'],
            'delivery_zone_id' => ['nullable', 'exists:delivery_zones,id'],
            'payment_method' => ['required', 'string', 'max:50'],
            'collection_value' => ['nullable', 'numeric', 'min:0'],
            'zone' => ['nullable', 'string', 'max:255'],
            'recipient_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if (Auth::user()->role === 'affiliate') {
            $validated['affiliated_company_id'] = Auth::user()->affiliated_company_id;
        }

        $validated = $this->applySenderPresetFromRequest($request, $validated, $shipment->tenant);
        $validated = $this->applyCompanyTerms($validated);
        $validated = $this->applyDeliveryZoneRate($validated, $shipment->tenant);
        $recipientLocalityForZone = trim((string) ($validated['recipient_locality'] ?? ''));
        if ($recipientLocalityForZone !== '') {
            $validated['zone'] = strtoupper($recipientLocalityForZone);
        }
        $validated = $this->normalizeShipmentText($validated);

        if ($shipment->inventoryMovements()->where('type', 'shipment')->exists()) {
            $validated['package_type'] = $shipment->package_type;
            $validated['pieces'] = $shipment->pieces;
            $validated['content_description'] = $shipment->content_description;
            $validated['declared_value'] = $shipment->declared_value;
            $validated['collection_value'] = $shipment->collection_value;
        }

        $shipment->update($validated);

        ShipmentEvent::query()->create([
            'shipment_id' => $shipment->id,
            'user_id' => Auth::id(),
            'status' => 'updated',
            'location' => 'Sistema',
            'notes' => 'Datos de la guia actualizados.',
        ]);

        Audit::log('shipment.updated', $shipment, "Guia {$shipment->guide_number} actualizada.");

        return redirect()
            ->route('shipments.show', $shipment)
            ->with('status', 'Guia actualizada correctamente.');
    }

    public function print(Request $request, Shipment $shipment)
    {
        abort_unless($shipment->isVisibleTo(Auth::user()), 403);

        $shipment->load(['affiliatedCompany', 'tenant', 'deliveryZone']);

        if ($shipment->status === 'created') {
            $shipment->update(['status' => 'printed']);

            ShipmentEvent::query()->create([
                'shipment_id' => $shipment->id,
                'user_id' => Auth::id(),
                'status' => 'printed',
                'location' => 'Sistema',
                'notes' => 'Guia impresa.',
            ]);

            Audit::log('shipment.printed', $shipment, "Guia {$shipment->guide_number} impresa.");
        }

$printFormats = $this->printFormats();
        $defaultFormat = $shipment->affiliatedCompany?->default_print_format
            ?? $shipment->tenant?->default_print_format
            ?? '100x150';
        if (!array_key_exists($defaultFormat, $printFormats)) {
            $defaultFormat = '100x150';
        }
        $selectedPrintFormat = array_key_exists($request->query('format'), $printFormats)
            ? $request->query('format')
            : $defaultFormat;
        $printFormat = $printFormats[$selectedPrintFormat];

        return view('shipments.print', compact('shipment', 'printFormats', 'selectedPrintFormat', 'printFormat'));
    }

    public function printPdf(Request $request, Shipment $shipment)
    {
        abort_unless($shipment->isVisibleTo(Auth::user()), 403);
        $shipment->load(['affiliatedCompany', 'tenant', 'deliveryZone']);

$printFormats = $this->printFormats();
        $defaultFormat = $shipment->affiliatedCompany?->default_print_format
            ?? $shipment->tenant?->default_print_format
            ?? '100x150';
        if (!array_key_exists($defaultFormat, $printFormats)) {
            $defaultFormat = '100x150';
        }
        $selectedPrintFormat = array_key_exists($request->query('format'), $printFormats)
            ? $request->query('format')
            : $defaultFormat;
        $printFormat = $printFormats[$selectedPrintFormat];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('shipments.print-pdf', compact('shipment', 'printFormat', 'selectedPrintFormat'));

        if (in_array($selectedPrintFormat, ['letter', 'a4'], true)) {
            $pdf->setPaper($selectedPrintFormat === 'a4' ? 'a4' : 'letter');
        }

        return $pdf->download('guia-'.$shipment->guide_number.'.pdf');
    }
    public function assignCourier(Request $request, Shipment $shipment)
    {
        abort_unless($shipment->isVisibleTo(Auth::user()), 403);
        abort_unless(in_array(Auth::user()->role, ['superadmin', 'tenant_admin', 'warehouse'], true), 403);

        $validated = $request->validate([
            'courier_id' => ['nullable', 'exists:users,id'],
        ]);

        $courier = null;

        if ($validated['courier_id']) {
            $courier = User::query()
                ->where('role', 'courier')
                ->where('status', 'active')
                ->findOrFail($validated['courier_id']);
        }

        if ($courier && ! $shipment->canTransitionTo('assigned')) {
            return back()
                ->withErrors(['courier_id' => 'La guia debe estar en clasificacion antes de asignar mensajero.']);
        }

        $shipment->update([
            'courier_id' => $courier?->id,
            'status' => $courier ? 'assigned' : $shipment->status,
        ]);

        ShipmentEvent::query()->create([
            'shipment_id' => $shipment->id,
            'user_id' => Auth::id(),
            'status' => $courier ? 'assigned' : 'courier_unassigned',
            'location' => 'Operacion',
            'notes' => $courier ? "Asignada al mensajero {$courier->name}." : 'Mensajero retirado de la guia.',
        ]);

        Audit::log(
            $courier ? 'shipment.courier_assigned' : 'shipment.courier_unassigned',
            $shipment,
            $courier ? "Guia {$shipment->guide_number} asignada a {$courier->name}." : "Mensajero retirado de guia {$shipment->guide_number}."
        );

        return redirect()
            ->route('shipments.show', $shipment)
            ->with('status', 'Mensajero actualizado correctamente.');
    }

    public function bulkAssignCourier(Request $request)
    {
        abort_unless(in_array(Auth::user()->role, ['superadmin', 'tenant_admin', 'warehouse'], true), 403);

        $validated = $request->validate([
            'shipment_ids' => ['required', 'array', 'min:1'],
            'shipment_ids.*' => ['integer', 'exists:shipments,id'],
            'courier_id' => ['required', 'exists:users,id'],
        ]);

        $courier = User::query()
            ->where('role', 'courier')
            ->where('status', 'active')
            ->findOrFail($validated['courier_id']);

        $shipments = Shipment::query()
            ->visibleTo(Auth::user())
            ->whereIn('id', $validated['shipment_ids'])
            ->get();

        $assigned = 0;
        $skipped = 0;

        foreach ($shipments as $shipment) {
            if (! $shipment->canTransitionTo('assigned')) {
                $skipped++;
                continue;
            }

            $shipment->update([
                'courier_id' => $courier->id,
                'status' => 'assigned',
            ]);

            ShipmentEvent::query()->create([
                'shipment_id' => $shipment->id,
                'user_id' => Auth::id(),
                'status' => 'assigned',
                'location' => 'Operacion',
                'notes' => "Asignacion masiva al mensajero {$courier->name}.",
            ]);

            Audit::log('shipment.bulk_courier_assigned', $shipment, "Guia {$shipment->guide_number} asignada masivamente a {$courier->name}.");

            $assigned++;
        }

        $message = "{$assigned} guia(s) asignada(s) a {$courier->name}.";

        if ($skipped) {
            $message .= " {$skipped} guia(s) omitida(s) por estado no permitido.";
        }

        return redirect()
            ->route('shipments.index', $request->query())
            ->with('status', $message);
    }

    public function bulkUpdateStatus(Request $request)
    {
        abort_unless(Auth::user()->canScanShipments() || Auth::user()->canEditShipments(), 403);

        $validated = $request->validate([
            'shipment_ids' => ['required', 'array', 'min:1'],
            'shipment_ids.*' => ['integer', 'exists:shipments,id'],
            'status' => ['required', 'string', 'max:50'],
        ]);

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

    public function bulkPrint(Request $request)
    {
        $validated = $request->validate([
            'shipment_ids' => ['required', 'array', 'min:1'],
            'shipment_ids.*' => ['integer', 'exists:shipments,id'],
            'format' => ['nullable', 'string'],
        ]);

        $shipments = Shipment::query()
            ->with(['affiliatedCompany', 'tenant', 'deliveryZone'])
            ->visibleTo(Auth::user())
            ->whereIn('id', $validated['shipment_ids'])
            ->get();

        foreach ($shipments as $shipment) {
            if ($shipment->status === 'created') {
                $shipment->update(['status' => 'printed']);

                ShipmentEvent::query()->create([
                    'shipment_id' => $shipment->id,
                    'user_id' => Auth::id(),
                    'status' => 'printed',
                    'location' => 'Sistema',
                    'notes' => 'Guia impresa en lote.',
                ]);

                Audit::log('shipment.printed', $shipment, "Guia {$shipment->guide_number} impresa en lote.");
            }
        }

$printFormats = $this->printFormats();
        $defaultFormat = Auth::user()->affiliatedCompany?->default_print_format
            ?? Auth::user()->tenant?->default_print_format
            ?? '100x150';
        if (!array_key_exists($defaultFormat, $printFormats)) {
            $defaultFormat = '100x150';
        }
        $selectedPrintFormat = array_key_exists($validated['format'] ?? null, $printFormats)
            ? $validated['format']
            : $defaultFormat;
        $printFormat = $printFormats[$selectedPrintFormat];

        return view('shipments.bulk-print', compact('shipments', 'printFormats', 'selectedPrintFormat', 'printFormat'));
    }

private function printFormats(): array
    {
        return [
            '100x150' => [
                'label' => 'Etiqueta 100 x 150 mm',
                'short_label' => '100 x 150',
                'page' => '100mm 150mm',
                'width' => '100mm',
                'height' => '150mm',
                'scale' => '1',
                'padding' => '0',
                'help' => 'Impresora termica estandar.',
                'multi' => false,
            ],
            '100x100' => [
                'label' => 'Etiqueta 100 x 100 mm',
                'short_label' => '100 x 100',
                'page' => '100mm 100mm',
                'width' => '100mm',
                'height' => '100mm',
                'scale' => '.66',
                'padding' => '0',
                'help' => 'Etiqueta cuadrada.',
                'multi' => false,
            ],
            '80x50' => [
                'label' => 'Etiqueta 50 x 80 mm (vertical)',
                'short_label' => '50 x 80',
                'page' => '50mm 80mm',
                'width' => '50mm',
                'height' => '80mm',
                'scale' => '.53',
                'padding' => '0',
                'help' => 'Etiqueta termica pequena, vertical.',
            ],
'half-letter' => [
                'label' => 'Media carta',
                'short_label' => 'Media carta',
                'page' => '5.5in 8.5in',
                'width' => '5.5in',
                'height' => '8.5in',
                'scale' => '1.2',
                'padding' => '10mm',
                'help' => 'Impresora normal, media hoja.',
            ],
            'letter' => [
                'label' => 'Carta (1 por hoja)',
                'short_label' => 'Carta',
                'page' => 'letter',
                'width' => '8.5in',
                'height' => '11in',
                'scale' => '1.73',
                'padding' => '10mm',
                'help' => 'Impresora normal de oficina, 1 guia por hoja.',
            ],
            'letter' => [
                'label' => 'Carta (1 por hoja)',
                'short_label' => 'Carta',
                'page' => 'letter',
                'width' => '8.5in',
                'height' => '11in',
                'scale' => '1.35',
                'padding' => '10mm',
                'help' => 'Impresora normal de oficina, 1 guia por hoja.',
                'multi' => false,
            ],
        ];
    }
    public function updateStatus(Request $request, Shipment $shipment)
    {
        abort_unless($shipment->isVisibleTo(Auth::user()), 403);
        abort_unless(Auth::user()->canScanShipments() || Auth::user()->canEditShipments(), 403);

        $validated = $request->validate([
            'status' => ['required', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if (! $shipment->canTransitionTo($validated['status'])) {
            return back()
                ->withErrors(['status' => 'Ese cambio de estado no esta permitido para esta guia.']);
        }

        $shipment->update([
            'status' => $validated['status'],
        ]);

        ShipmentEvent::query()->create([
            'shipment_id' => $shipment->id,
            'user_id' => Auth::id(),
            'status' => $validated['status'],
            'location' => 'Operacion',
            'notes' => $validated['notes'] ?? 'Estado actualizado desde el panel.',
        ]);

        Audit::log('shipment.status_updated', $shipment, "Guia {$shipment->guide_number} cambio a {$validated['status']}.");

        $whatsappEvent = match ($validated['status']) {
            'in_transit', 'in_transit' => 'in_transit',
            'delivered' => 'delivered',
            default => null,
        };
        if ($whatsappEvent) {
            SendWhatsAppNotification::dispatch($shipment, $whatsappEvent);
        }

        // Webhook
        $tenant = $shipment->tenant;
        if ($tenant && $tenant->webhook_url) {
            $events = $tenant->webhook_events ?? ['delivered', 'failed_delivery', 'cancelled'];
            if (in_array($validated['status'], $events)) {
                \App\Jobs\DispatchWebhook::dispatch($tenant->webhook_url, $shipment, $validated['status']);
            }
        }

        return back()->with('status', 'Estado actualizado correctamente.');
    }

    public function cancel(Request $request, Shipment $shipment)
    {
        abort_unless($shipment->isVisibleTo(Auth::user()), 403);
        abort_unless(Auth::user()->canEditShipments(), 403);
        abort_unless($shipment->canBeCancelled(), 403);

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($shipment, $validated) {
            $shipment->update(['status' => 'cancelled']);

            $this->restoreInventoryForShipment($shipment);

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
}


