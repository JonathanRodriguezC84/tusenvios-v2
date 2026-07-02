<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('affiliated_company_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->string('category')->nullable();
            $table->decimal('cost', 12, 2)->default(0);
            $table->decimal('price', 12, 2)->default(0);
            $table->integer('stock')->default(0);
            $table->integer('stock_minimum')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['affiliated_company_id', 'status']);
            $table->index(['tenant_id', 'sku']);
            $table->index(['affiliated_company_id', 'sku']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_products');
    }
};
