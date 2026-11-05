<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('merchandise_loans')) {
            Schema::create('merchandise_loans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('affiliated_company_id')->nullable()->constrained()->nullOnDelete();
                $table->string('loan_number', 50);
                $table->string('borrower_name', 150);
                $table->string('borrower_phone', 40)->nullable();
                $table->string('borrower_document', 50)->nullable();
                $table->string('borrower_address', 255)->nullable();
                $table->date('loan_date');
                $table->date('due_date')->nullable();
                $table->string('status', 30)->default('active')->index(); // active, partial, returned, sold, cancelled
                $table->integer('total_items')->default(0);
                $table->decimal('total_value', 12, 2)->default(0);
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['tenant_id', 'affiliated_company_id', 'status']);
                $table->index(['tenant_id', 'loan_number']);
            });
        }

        if (!Schema::hasTable('merchandise_loan_items')) {
            Schema::create('merchandise_loan_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('merchandise_loan_id')->constrained()->cascadeOnDelete();
                $table->foreignId('inventory_product_id')->constrained()->cascadeOnDelete();
                $table->integer('quantity_lent');
                $table->integer('quantity_returned')->default(0);
                $table->integer('quantity_sold')->default(0);
                $table->decimal('unit_price', 12, 2)->default(0);
                $table->decimal('unit_cost', 12, 2)->default(0);
                $table->string('notes', 255)->nullable();
                $table->timestamps();

                $table->index(['merchandise_loan_id', 'inventory_product_id'], 'loan_product_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('merchandise_loan_items');
        Schema::dropIfExists('merchandise_loans');
    }
};
