<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $localities = [
        'Bogotá D.C.' => [
            'Bogotá' => ['Usaquén', 'Chapinero', 'Santa Fe', 'San Cristóbal', 'Usme', 'Tunjuelito', 'Bosa', 'Kennedy', 'Fontibón', 'Engativá', 'Suba', 'Barrios Unidos', 'Teusaquillo', 'Los Mártires', 'Antonio Nariño', 'Puente Aranda', 'La Candelaria', 'Rafael Uribe Uribe', 'Ciudad Bolívar', 'Sumapaz'],
        ],
        'Antioquia' => [
            'Medellín' => ['Popular', 'Santa Cruz', 'Manrique', 'Aranjuez', 'Castilla', 'Doce de Octubre', 'Robledo', 'Villa Hermosa', 'Buenos Aires', 'La Candelaria', 'Laureles-Estadio', 'La América', 'San Javier', 'El Poblado', 'Guayabal', 'Belén'],
        ],
        'Valle del Cauca' => [
            'Cali' => ['Comuna 1', 'Comuna 2', 'Comuna 3', 'Comuna 4', 'Comuna 5', 'Comuna 6', 'Comuna 7', 'Comuna 8', 'Comuna 9', 'Comuna 10', 'Comuna 11', 'Comuna 12', 'Comuna 13', 'Comuna 14', 'Comuna 15', 'Comuna 16', 'Comuna 17', 'Comuna 18', 'Comuna 19', 'Comuna 20', 'Comuna 21', 'Comuna 22'],
        ],
        'Atlántico' => [
            'Barranquilla' => ['Riomar', 'Norte-Centro Histórico', 'Metropolitana', 'Suroriente', 'Suroccidente'],
        ],
        'Bolívar' => [
            'Cartagena de Indias' => ['Histórica y del Caribe Norte', 'Industrial y de la Bahía', 'De la Virgen y Turística'],
        ],
        'Magdalena' => [
            'Santa Marta' => ['Centro Histórico', 'El Rodadero', 'Pescaíto', 'Gaira', 'Zona Norte', 'Zona Sur'],
        ],
        'Santander' => [
            'Bucaramanga' => ['Comuna 1', 'Comuna 2', 'Comuna 3', 'Comuna 4', 'Comuna 5', 'Comuna 6', 'Comuna 7', 'Comuna 8', 'Comuna 9', 'Comuna 10', 'Comuna 11', 'Comuna 12', 'Comuna 13', 'Comuna 14', 'Comuna 15', 'Comuna 16', 'Comuna 17'],
        ],
        'Risaralda' => [
            'Pereira' => ['Comuna 1', 'Comuna 2', 'Comuna 3', 'Comuna 4', 'Comuna 5', 'Comuna 6', 'Comuna 7', 'Comuna 8', 'Comuna 9', 'Comuna 10', 'Comuna 11', 'Comuna 12', 'Comuna 13', 'Comuna 14', 'Comuna 15', 'Comuna 16', 'Comuna 17', 'Comuna 18', 'Comuna 19'],
        ],
        'Norte de Santander' => [
            'Cúcuta' => ['Comuna 1', 'Comuna 2', 'Comuna 3', 'Comuna 4', 'Comuna 5', 'Comuna 6', 'Comuna 7', 'Comuna 8', 'Comuna 9', 'Comuna 10'],
        ],
        'Meta' => [
            'Villavicencio' => ['Comuna 1', 'Comuna 2', 'Comuna 3', 'Comuna 4', 'Comuna 5', 'Comuna 6', 'Comuna 7', 'Comuna 8'],
        ],
        'Tolima' => [
            'Ibagué' => ['Comuna 1', 'Comuna 2', 'Comuna 3', 'Comuna 4', 'Comuna 5', 'Comuna 6', 'Comuna 7', 'Comuna 8', 'Comuna 9', 'Comuna 10', 'Comuna 11', 'Comuna 12', 'Comuna 13'],
        ],
        'Sucre' => [
            'Sincelejo' => ['Comuna Centro', 'Comuna Sur', 'Comuna Norte', 'Comuna El Tunjuelito', 'Comuna La Esmeralda', 'Comuna Sabanas'],
        ],
        'Caldas' => [
            'Manizales' => ['Comuna Atardeceres', 'Comuna San José', 'Comuna Cumanday', 'Comuna La Estación', 'Comuna Ciudadela del Norte', 'Comuna Ecoturística Cerro de Oro', 'Comuna Universitaria', 'Comuna Tesorito', 'Comuna Palogrande', 'Comuna La Fuente', 'Comuna Macarena'],
        ],
        'Córdoba' => [
            'Montería' => ['Comuna 1', 'Comuna 2', 'Comuna 3', 'Comuna 4', 'Comuna 5', 'Comuna 6', 'Comuna 7', 'Comuna 8', 'Comuna 9'],
        ],
        'Huila' => [
            'Neiva' => ['Comuna 1', 'Comuna 2', 'Comuna 3', 'Comuna 4', 'Comuna 5', 'Comuna 6', 'Comuna 7', 'Comuna 8', 'Comuna 9', 'Comuna 10'],
        ],
        'Cesar' => [
            'Valledupar' => ['Comuna 1', 'Comuna 2', 'Comuna 3', 'Comuna 4', 'Comuna 5', 'Comuna 6'],
        ],
        'Cauca' => [
            'Popayán' => ['Centro', 'Campo Nuevo', 'La Esmeralda', 'Bolívar', 'El Tomal', 'Santa Bárbara', 'La Ladera', 'Las Quintas'],
        ],
        'Nariño' => [
            'Pasto' => ['Comuna 1', 'Comuna 2', 'Comuna 3', 'Comuna 4', 'Comuna 5', 'Comuna 6', 'Comuna 7', 'Comuna 8', 'Comuna 9', 'Comuna 10', 'Comuna 11', 'Comuna 12'],
        ],
        'Quindío' => [
            'Armenia' => ['Comuna 1', 'Comuna 2', 'Comuna 3', 'Comuna 4', 'Comuna 5', 'Comuna 6', 'Comuna 7', 'Comuna 8', 'Comuna 9'],
        ],
        'Boyacá' => [
            'Tunja' => ['Comuna 1', 'Comuna 2', 'Comuna 3', 'Comuna 4', 'Comuna 5', 'Comuna 6'],
        ],
    ];

    public function up(): void
    {
        Schema::create('localities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();

            $table->index(['city_id', 'name']);
        });

        if (DB::table('localities')->exists()) {
            return;
        }

        $now = now();

        foreach ($this->localities as $departmentName => $cities) {
            $departmentId = DB::table('departments')->where('name', $departmentName)->value('id');
            if (! $departmentId) {
                continue;
            }

            foreach ($cities as $cityName => $localityNames) {
                $cityId = DB::table('cities')
                    ->where('department_id', $departmentId)
                    ->where('name', $cityName)
                    ->value('id');

                if (! $cityId) {
                    continue;
                }

                $rows = array_map(fn ($name) => [
                    'city_id' => $cityId,
                    'name' => $name,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], $localityNames);

                DB::table('localities')->insert($rows);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('localities');
    }
};