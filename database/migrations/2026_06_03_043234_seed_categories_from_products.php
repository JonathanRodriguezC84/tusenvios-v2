<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Category;
use App\Models\InventoryProduct;

return new class extends Migration
{
    public function up(): void
    {
        $categories = InventoryProduct::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->select('tenant_id', 'affiliated_company_id', 'category')
            ->distinct()
            ->get();

        foreach ($categories as $row) {
            Category::firstOrCreate([
                'tenant_id' => $row->tenant_id,
                'affiliated_company_id' => $row->affiliated_company_id,
                'name' => $row->category,
            ]);
        }
    }

    public function down(): void
    {
        Category::query()->delete();
    }
};
