<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('merchandise_loan_items')) {
            // Permitir que inventory_product_id sea nulo cuando el producto proviene de quick_products
            try {
                DB::statement('ALTER TABLE merchandise_loan_items DROP FOREIGN KEY merchandise_loan_items_inventory_product_id_foreign');
            } catch (\Throwable $e) {
                // Si la FK no existe o SQLite, ignorar
            }

            try {
                DB::statement('ALTER TABLE merchandise_loan_items MODIFY inventory_product_id BIGINT UNSIGNED NULL');
            } catch (\Throwable $e) {
                // Ignorar si ya es nulo o dialecto diferente
            }

            Schema::table('merchandise_loan_items', function (Blueprint $table) {
                if (!Schema::hasColumn('merchandise_loan_items', 'quick_product_id')) {
                    $table->foreignId('quick_product_id')->nullable()->after('inventory_product_id')->constrained('quick_products')->nullOnDelete();
                }
                if (!Schema::hasColumn('merchandise_loan_items', 'product_type')) {
                    $table->string('product_type', 30)->default('quick')->after('quick_product_id');
                }
                if (!Schema::hasColumn('merchandise_loan_items', 'product_name')) {
                    $table->string('product_name', 190)->nullable()->after('product_type');
                }
                if (!Schema::hasColumn('merchandise_loan_items', 'product_sku')) {
                    $table->string('product_sku', 80)->nullable()->after('product_name');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('merchandise_loan_items')) {
            Schema::table('merchandise_loan_items', function (Blueprint $table) {
                if (Schema::hasColumn('merchandise_loan_items', 'quick_product_id')) {
                    $table->dropForeign(['quick_product_id']);
                    $table->dropColumn('quick_product_id');
                }
                if (Schema::hasColumn('merchandise_loan_items', 'product_type')) {
                    $table->dropColumn('product_type');
                }
                if (Schema::hasColumn('merchandise_loan_items', 'product_name')) {
                    $table->dropColumn('product_name');
                }
                if (Schema::hasColumn('merchandise_loan_items', 'product_sku')) {
                    $table->dropColumn('product_sku');
                }
            });
        }
    }
};
