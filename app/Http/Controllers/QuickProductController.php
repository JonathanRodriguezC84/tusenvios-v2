<?php

namespace App\Http\Controllers;

use App\Models\QuickProduct;
use App\Models\Tenant;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuickProductController extends Controller
{
    public function index(): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        $this->authorize('viewAny', QuickProduct::class);

        if (Auth::user()->canUseInventory()) {
            return redirect()->route('inventory.index');
        }

        $products = $this->queryForOwner()
            ->latest()
            ->paginate(20);

        return view('quick-products.index', compact('products'));
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('create', QuickProduct::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'package_type' => ['required', 'in:package,document,merchandise'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
        ]);

        $validated['price'] = $validated['price'] ?? 0;

        $product = QuickProduct::query()->create(array_merge($validated, $this->ownerKeys(), [
            'status' => 'active',
        ]));

        Audit::log('quick_product.created', $product, "Producto rapido {$product->name} creado.");

        return redirect()
            ->route('quick-products.index')
            ->with('status', 'Producto rapido creado correctamente.');
    }

    public function update(Request $request, QuickProduct $quickProduct): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('update', $quickProduct);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'package_type' => ['required', 'in:package,document,merchandise'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'status' => ['required', 'in:active,paused'],
        ]);

        $validated['price'] = $validated['price'] ?? 0;

        $quickProduct->update($validated);

        Audit::log('quick_product.updated', $quickProduct, "Producto rapido {$quickProduct->name} actualizado.");

        return redirect()
            ->route('quick-products.index')
            ->with('status', 'Producto rapido actualizado correctamente.');
    }

    public function destroy(QuickProduct $quickProduct): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('delete', $quickProduct);

        Audit::log('quick_product.deleted', $quickProduct, "Producto rapido {$quickProduct->name} eliminado.");

        $quickProduct->delete();

        return redirect()
            ->route('quick-products.index')
            ->with('status', 'Producto rapido eliminado correctamente.');
    }

    private function owner()
    {
        $user = Auth::user();

        if ($user->role === 'affiliate' && $user->affiliatedCompany) {
            return $user->affiliatedCompany;
        }

        return $user->tenant ?: Tenant::query()->where('subdomain', 'demo-tus-envios')->first();
    }

    private function ownerKeys(): array
    {
        $user = Auth::user();

        if ($user->role === 'affiliate' && $user->affiliated_company_id) {
            return [
                'tenant_id' => $user->tenant_id,
                'affiliated_company_id' => $user->affiliated_company_id,
            ];
        }

        $tenantId = $user->tenant_id ?: Tenant::query()->where('subdomain', 'demo-tus-envios')->value('id');

        return [
            'tenant_id' => $tenantId,
            'affiliated_company_id' => null,
        ];
    }

    private function queryForOwner()
    {
        $keys = $this->ownerKeys();

        return QuickProduct::query()
            ->when(
                $keys['affiliated_company_id'],
                fn ($query) => $query->where('affiliated_company_id', $keys['affiliated_company_id']),
                fn ($query) => $query->where('tenant_id', $keys['tenant_id'])->whereNull('affiliated_company_id')
            );
    }

}

