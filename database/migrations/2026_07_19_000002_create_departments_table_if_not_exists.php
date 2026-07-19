<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('departments')) {
            Schema::create('departments', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->timestamps();
            });

            $names = [
                'Amazonas', 'Antioquia', 'Arauca', 'Atlántico', 'Bogotá D.C.',
                'Bolívar', 'Boyacá', 'Caldas', 'Caquetá', 'Casanare',
                'Cauca', 'Cesar', 'Chocó', 'Córdoba', 'Cundinamarca',
                'Guainía', 'Guaviare', 'Huila', 'La Guajira', 'Magdalena',
                'Meta', 'Nariño', 'Norte de Santander', 'Putumayo', 'Quindío',
                'Risaralda', 'Santander', 'San Andrés y Providencia', 'Sucre',
                'Tolima', 'Valle del Cauca', 'Vaupés', 'Vichada',
            ];

            $now = now();
            foreach ($names as $name) {
                DB::table('departments')->insert([
                    'name' => $name,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
    }
};
