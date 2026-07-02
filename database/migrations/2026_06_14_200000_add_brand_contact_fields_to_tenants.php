<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (! Schema::hasColumn('tenants', 'brand_phone')) {
                $table->string('brand_phone')->nullable()->after('brand_message');
            }
            if (! Schema::hasColumn('tenants', 'brand_address')) {
                $table->string('brand_address')->nullable()->after('brand_phone');
            }
            if (! Schema::hasColumn('tenants', 'brand_neighborhood')) {
                $table->string('brand_neighborhood')->nullable()->after('brand_address');
            }
            if (! Schema::hasColumn('tenants', 'brand_locality')) {
                $table->string('brand_locality')->nullable()->after('brand_neighborhood');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'brand_phone',
                'brand_address',
                'brand_neighborhood',
                'brand_locality',
            ]);
        });
    }
};
