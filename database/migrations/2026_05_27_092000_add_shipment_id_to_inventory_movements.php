<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->foreignId('shipment_id')->nullable()->after('affiliated_company_id')->constrained()->nullOnDelete();
            $table->index(['shipment_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropIndex(['shipment_id', 'type']);
            $table->dropConstrainedForeignId('shipment_id');
        });
    }
};
