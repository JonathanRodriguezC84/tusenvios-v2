<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminClientController extends Controller
{
    public function clients(Request $request): \Illuminate\View\View
    {
        $this->authorize('access-admin');

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:active,paused,suspended,cancelled'],
            'plan_id' => ['nullable', 'integer', 'exists:subscription_plans,id'],
        ]);

        $clients = $this->clientQuery($filters)
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $plans = SubscriptionPlan::query()->where('is_active', true)->where('code', 'emprende')->orderBy('monthly_price')->get();

        return view('admin.clients', compact('clients', 'plans', 'filters'));
    }

    public function showClient(Tenant $tenant): \Illuminate\View\View
    {
        $this->authorize('access-admin');

        $tenant->loadCount(['shipments', 'users']);
        $tenant->load(['currentSubscription.plan', 'subscriptions' => fn ($q) => $q->latest()->limit(5)]);
        $plans = SubscriptionPlan::orderBy('monthly_price')->get();

        $clientUsers = $tenant->users()->orderBy('name')->get();
        $clientShipments = $tenant->shipments()
            ->with('creator')
            ->latest()
            ->take(30)
            ->get();
        $operationMetrics = $this->operationMetrics($tenant);

        return view('admin.client-detail', compact(
            'tenant',
            'plans',
            'clientUsers',
            'clientShipments',
            'operationMetrics'
        ));
    }

    public function createClient(): \Illuminate\View\View
    {
        $this->authorize('access-admin');
        $plans = SubscriptionPlan::where('is_active', true)->where('code', 'emprende')->orderBy('monthly_price')->get();

        return view('admin.client-create', compact('plans'));
    }

    public function storeClient(Request $request): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('access-admin');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:tenants,email'],
            'phone' => ['nullable', 'string', 'max:80'],
            'subdomain' => ['required', 'string', 'max:100', 'unique:tenants,subdomain', 'regex:/^[a-z0-9-]+$/'],
            'guide_prefix' => ['nullable', 'string', 'max:10'],
            'plan_id' => [
                'required',
                Rule::exists('subscription_plans', 'id')
                    ->where('code', 'emprende')
                    ->where('is_active', true),
            ],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'admin_password' => ['required', 'string', 'min:8'],
        ]);

        $tenant = Tenant::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'subdomain' => $validated['subdomain'],
            'guide_prefix' => $validated['guide_prefix'] ?? null,
            'status' => 'active',
        ]);

        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);
        TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'start_mode' => 'paid',
            'starts_at' => now(),
            'next_payment_at' => now()->addMonth(),
        ]);

        User::create([
            'tenant_id' => $tenant->id,
            'name' => $validated['admin_name'],
            'email' => $validated['admin_email'],
            'password' => bcrypt($validated['admin_password']),
            'role' => 'tenant_admin',
        ]);

        // --- INICIO DE AUTOCREADO DE INVENTARIO DESDE OMS ---
        try {
            $omsEnvPath = '/home/rcicomco/public_html/oms.rci.com.co/.env';
            if (file_exists($omsEnvPath)) {
                $omsDbHost = '127.0.0.1';
                $omsDbPort = '3306';
                $omsDbName = '';
                $omsDbUser = '';
                $omsDbPass = '';

                $lines = file($omsEnvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                        list($key, $value) = explode('=', $line, 2);
                        $key = trim($key);
                        $value = trim($value, " \t\n\r\0\x0B\"'");
                        if ($key === 'DB_DATABASE') $omsDbName = $value;
                        if ($key === 'DB_USERNAME') $omsDbUser = $value;
                        if ($key === 'DB_PASSWORD') $omsDbPass = $value;
                        if ($key === 'DB_HOST') $omsDbHost = $value;
                        if ($key === 'DB_PORT') $omsDbPort = $value;
                    }
                }

                if (!empty($omsDbName)) {
                    $dsn = "mysql:host={$omsDbHost};port={$omsDbPort};dbname={$omsDbName};charset=utf8mb4";
                    $omsPdo = new \PDO($dsn, $omsDbUser, $omsDbPass);
                    $omsPdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

                    // Leer las referencias y el stock de bodega_principal del OMS
                    $stmt = $omsPdo->query('
                        SELECT r.sku, r.name, r.cost_price, r.sale_price, s.quantity
                        FROM `references` r
                        JOIN `stocks` s ON r.id = s.reference_id
                        WHERE s.channel = "bodega_principal"
                    ');
                    $omsItems = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                    foreach ($omsItems as $item) {
                        $cost = $item['cost_price'] ?: 8000;
                        $price = 0; // El precio de venta debe ser $0 por defecto
                        $stock = $item['quantity'] ?: 0;

                        // Insertar en quick_products (Productos Rápidos)
                        \App\Models\QuickProduct::create([
                            'tenant_id' => $tenant->id,
                            'name' => $item['name'],
                            'sku' => $item['sku'],
                            'package_type' => 'package',
                            'cost' => $cost,
                            'price' => $price,
                            'stock' => $stock,
                            'status' => 'active'
                        ]);

                        // Insertar en inventory_products (Inventario Completo)
                        \App\Models\InventoryProduct::create([
                            'tenant_id' => $tenant->id,
                            'name' => $item['name'],
                            'sku' => $item['sku'],
                            'price' => $price,
                            'cost' => $cost,
                            'stock' => $stock,
                            'stock_minimum' => 5,
                            'status' => 'active'
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error auto-seeding products from OMS for Tenant {$tenant->id}: " . $e->getMessage());
        }
        // --- FIN DE AUTOCREADO DE INVENTARIO DESDE OMS ---

        return redirect()->route('admin.clients.show', $tenant)->with('status', 'Cliente creado correctamente.');
    }

    public function updateClientStatus(Request $request, Tenant $tenant): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('access-admin');

        $validated = $request->validate([
            'status' => ['required', 'in:active,paused,suspended,cancelled'],
        ]);

        $tenant->update(['status' => $validated['status']]);

        return back()->with('status', 'Estado del cliente actualizado.');
    }

    public function generateToken(Tenant $tenant): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('access-admin');

        $plainToken = 'te_' . Str::random(48);
        $tenant->update(['api_token' => hash('sha256', $plainToken)]);

        return redirect()->route('admin.clients.show', $tenant)->with('new_token', $plainToken);
    }

    public function updateWebhook(Request $request, Tenant $tenant): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('access-admin');

        $validated = $request->validate([
            'webhook_url' => ['nullable', 'url', 'max:500'],
            'webhook_events' => ['nullable', 'array'],
            'webhook_events.*' => ['in:delivered,failed_delivery,cancelled,on_route,returned'],
        ]);

        $tenant->update([
            'webhook_url' => $validated['webhook_url'] ?? null,
            'webhook_events' => $validated['webhook_events'] ?? null,
        ]);

        return back()->with('status', 'Webhook actualizado.');
    }

    public function walletTransaction(Request $request, Tenant $tenant): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('access-admin');

        $validated = $request->validate([
            'type' => ['required', 'in:deposit,withdrawal,adjustment'],
            'amount' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $amount = (int) $validated['amount'];
        if ($validated['type'] !== 'deposit') {
            $amount = -abs($amount);
        }

        $tenant->balance += $amount;
        $tenant->save();

        \App\Models\WalletTransaction::create([
            'tenant_id' => $tenant->id,
            'type' => $validated['type'],
            'amount' => $amount,
            'balance_after' => $tenant->balance,
            'notes' => $validated['notes'] ?? null,
            'reference' => 'ADM-' . now()->format('YmdHis'),
            'created_by' => auth()->id(),
        ]);

        return back()->with('status', 'Billetera actualizada. Saldo: $' . number_format($tenant->balance, 0, ',', '.'));
    }

    private function operationMetrics(Tenant $tenant): array
    {
        $rows = Shipment::query()
            ->where('tenant_id', $tenant->id)
            ->selectRaw('status, COUNT(*) as total, COALESCE(SUM(collection_value), 0) as value')
            ->groupBy('status')
            ->get();

        $groups = [];
        foreach (Shipment::STATUS_GROUPS as $key => $group) {
            $groups[$key] = ['label' => $group['label'], 'count' => 0, 'value' => 0];
        }

        $totals = ['count' => 0, 'value' => 0];

        foreach ($rows as $row) {
            $groupKey = Shipment::statusGroupKey($row->status);
            $groups[$groupKey]['count'] += (int) $row->total;
            $groups[$groupKey]['value'] += (float) $row->value;
            $totals['count'] += (int) $row->total;
            $totals['value'] += (float) $row->value;
        }

        return [
            'groups' => $groups,
            'totals' => $totals,
            'collection_pending' => $groups['on_route']['value'],
            'collection_received' => $groups['delivered']['value'],
            'month_count' => Shipment::query()
                ->where('tenant_id', $tenant->id)
                ->where('created_at', '>=', now()->startOfMonth())
                ->count(),
        ];
    }

    private function clientQuery(array $filters = [])
    {
        return Tenant::query()
            ->with(['currentSubscription.plan'])
            ->withCount(['shipments', 'users'])
            ->withMax('shipments', 'created_at')
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('subdomain', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['plan_id'] ?? null, function ($query, $planId) {
                $query->whereHas('currentSubscription', fn ($subscription) => $subscription->where('subscription_plan_id', $planId));
            });
    }
}
