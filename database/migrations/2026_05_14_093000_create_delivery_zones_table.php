<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        DB::table('delivery_zones')->insert([
            [
                'name' => 'Flex',
                'code' => 'FLX',
                'price' => 7000,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Normal',
                'code' => 'NRM',
                'price' => 8000,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Zona Extra',
                'code' => 'EXT',
                'price' => 10000,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        Schema::table('shipments', function (Blueprint $table) {
            $table->foreignId('delivery_zone_id')->nullable()->after('zone')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('delivery_zone_id');
        });

        Schema::dropIfExists('delivery_zones');
    }
};
