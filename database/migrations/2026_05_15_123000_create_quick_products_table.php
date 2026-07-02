<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quick_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('affiliated_company_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('package_type')->default('package');
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['affiliated_company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quick_products');
    }
};
