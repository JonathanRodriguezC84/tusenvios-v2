<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('affiliated_company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('courier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('guide_number')->unique();
            $table->string('status')->default('created');
            $table->string('service_type')->default('standard');
            $table->date('estimated_delivery_date')->nullable();

            $table->string('sender_name');
            $table->string('sender_document')->nullable();
            $table->string('sender_phone')->nullable();
            $table->string('sender_address');
            $table->string('sender_neighborhood')->nullable();
            $table->string('sender_locality')->nullable();
            $table->text('sender_notes')->nullable();

            $table->string('recipient_name');
            $table->string('recipient_document')->nullable();
            $table->string('recipient_phone');
            $table->string('recipient_alt_phone')->nullable();
            $table->string('recipient_address');
            $table->string('recipient_neighborhood')->nullable();
            $table->string('recipient_locality')->nullable();
            $table->text('recipient_notes')->nullable();

            $table->string('package_type')->default('package');
            $table->unsignedInteger('pieces')->default(1);
            $table->decimal('weight_kg', 8, 2)->nullable();
            $table->text('content_description')->nullable();
            $table->decimal('declared_value', 12, 2)->default(0);
            $table->decimal('shipping_value', 12, 2)->default(0);
            $table->string('payment_method')->default('cash');
            $table->decimal('collection_value', 12, 2)->default(0);

            $table->string('zone')->nullable();
            $table->unsignedInteger('delivery_attempts')->default(0);
            $table->string('issue_reason')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'created_at']);
            $table->index(['courier_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
