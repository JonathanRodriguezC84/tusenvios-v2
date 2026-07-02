<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_zones', function (Blueprint $table) {
            $table->text('coverage_keywords')->nullable()->after('price');
        });

        DB::table('delivery_zones')->where('code', 'FLX')->update([
            'coverage_keywords' => 'Chapinero, Teusaquillo, Barrios Unidos, Puente Aranda, Los Martires, Santa Fe, La Candelaria',
        ]);

        DB::table('delivery_zones')->where('code', 'NRM')->update([
            'coverage_keywords' => 'Usaquen, Suba, Engativa, Fontibon, Kennedy, Bosa, Tunjuelito, Antonio Narino, Rafael Uribe Uribe, San Cristobal, Usme, Ciudad Bolivar',
        ]);

        DB::table('delivery_zones')->where('code', 'EXT')->update([
            'coverage_keywords' => 'Soacha, Chia, Cota, Mosquera, Funza, Madrid, Cajica, La Calera, Siberia, Bojaca, Facatativa, Sumapaz',
        ]);
    }

    public function down(): void
    {
        Schema::table('delivery_zones', function (Blueprint $table) {
            $table->dropColumn('coverage_keywords');
        });
    }
};
