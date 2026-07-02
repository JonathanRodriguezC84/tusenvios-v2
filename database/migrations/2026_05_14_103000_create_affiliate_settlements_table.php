<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('affiliated_company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('settlement_number')->unique();
            $table->date('date_from');
            $table->date('date_to');
            $table->unsignedInteger('shipments_count')->default(0);
            $table->decimal('shipping_total', 12, 2)->default(0);
            $table->decimal('collection_total', 12, 2)->default(0);
            $table->decimal('commission_total', 12, 2)->default(0);
            $table->decimal('net_collection', 12, 2)->default(0);
            $table->decimal('total_to_invoice', 12, 2)->default(0);
            $table->string('status')->default('closed');
            $table->text('notes')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'affiliated_company_id', 'date_from', 'date_to'], 'settlements_company_period_idx');
        });

        Schema::create('affiliate_settlement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_settlement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
            $table->string('guide_number');
            $table->string('recipient_name');
            $table->string('status');
            $table->string('payment_method');
            $table->string('delivery_zone_name')->nullable();
            $table->decimal('shipping_value', 12, 2)->default(0);
            $table->decimal('collection_value', 12, 2)->default(0);
            $table->decimal('commission_value', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['affiliate_settlement_id', 'shipment_id'], 'settlement_item_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_settlement_items');
        Schema::dropIfExists('affiliate_settlements');
    }
};
