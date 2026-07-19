<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(): \Illuminate\Http\JsonResponse
    {
        $this->authorize('use-inventory');

        $categories = $this->queryForOwner()->orderBy('name')->get();

        return response()->json($categories);
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('use-inventory');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
        ]);

        $keys = $this->ownerKeys();

        $exists = $this->queryForOwner()
            ->where('name', trim($validated['name']))
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Esta categoria ya existe.'], 409);
        }

        $category = Category::create([
            ...$keys,
            'name' => trim($validated['name']),
        ]);

        return response()->json($category, 201);
    }

    public function update(Request $request, Category $category): \Illuminate\Http\JsonResponse
    {
        $this->authorize('use-inventory');

        $this->ensureOwner($category);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
        ]);

        $duplicate = $this->queryForOwner()
            ->where('name', trim($validated['name']))
            ->where('id', '!=', $category->id)
            ->exists();

        if ($duplicate) {
            return response()->json(['message' => 'Esta categoria ya existe.'], 409);
        }

        $category->update(['name' => trim($validated['name'])]);

        return response()->json($category);
    }

    public function destroy(Category $category): \Illuminate\Http\JsonResponse
    {
        $this->authorize('use-inventory');

        $this->ensureOwner($category);

        $category->delete();

        return response()->json(['message' => 'Categoria eliminada.']);
    }

    private function ownerKeys(): array
    {
        $user = auth()->user();

        if ($user->role === 'affiliate' && $user->affiliated_company_id) {
            return [
                'tenant_id' => $user->tenant_id,
                'affiliated_company_id' => $user->affiliated_company_id,
            ];
        }

        $tenantId = $user->tenant_id ?: \App\Models\Tenant::query()->where('subdomain', 'demo-tus-envios')->value('id');

        return [
            'tenant_id' => $tenantId,
            'affiliated_company_id' => null,
        ];
    }

    private function queryForOwner()
    {
        $keys = $this->ownerKeys();

        return Category::query()
            ->when(
                $keys['affiliated_company_id'],
                fn ($query) => $query->where('affiliated_company_id', $keys['affiliated_company_id']),
                fn ($query) => $query->where('tenant_id', $keys['tenant_id'])->whereNull('affiliated_company_id')
            );
    }

    private function ensureOwner(Category $category): void
    {
        $keys = $this->ownerKeys();

        $owns = $category->tenant_id === $keys['tenant_id']
            && $category->affiliated_company_id === $keys['affiliated_company_id'];

        if (! $owns) {
            abort(403);
        }
    }
}