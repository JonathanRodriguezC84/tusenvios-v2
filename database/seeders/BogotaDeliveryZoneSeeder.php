<?php

namespace Database\Seeders;

use App\Models\DeliveryZone;
use Illuminate\Database\Seeder;

class BogotaDeliveryZoneSeeder extends Seeder
{
    public function run(): void
    {
        $zones = [
            [
                'name' => 'Flex',
                'code' => 'FLX',
                'price' => 7000,
                'keywords' => [
                    'Chapinero', 'Chapinero Alto', 'Chapinero Central', 'Chico', 'Chico Norte', 'Chico Navarra',
                    'Lago', 'El Lago', 'Virrey', 'Antiguo Country', 'Quinta Camacho', 'Rosales', 'El Nogal',
                    'Los Rosales', 'La Cabrera', 'Porciuncula', 'Espartillal', 'Pardo Rubio', 'San Luis',
                    'Teusaquillo', 'Galerias', 'Palermo', 'La Soledad', 'Quinta Paredes', 'Ciudad Salitre Oriental',
                    'Nicolas de Federman', 'Campin', 'El Campin', 'Pablo VI', 'La Esmeralda',
                    'Barrios Unidos', 'Siete de Agosto', 'Doce de Octubre', 'Alcazares', 'La Castellana',
                    'Rionegro', 'San Fernando', 'Metrópolis', 'Metropolis', 'Polo Club',
                    'Puente Aranda', 'Industrial Centenario', 'San Rafael Industrial', 'Pensilvania',
                    'Ciudad Montes', 'Carvajal Osorio', 'Los Ejidos', 'Comuneros',
                    'Los Martires', 'La Sabana', 'Santa Isabel', 'Ricaurte', 'Paloquemao', 'Samper Mendoza',
                    'Santa Fe', 'Las Nieves', 'San Diego', 'La Macarena', 'Bosque Izquierdo',
                    'La Candelaria', 'Centro', 'Egipto', 'Belen', 'Las Aguas', 'La Concordia',
                ],
            ],
            [
                'name' => 'Normal',
                'code' => 'NRM',
                'price' => 8000,
                'keywords' => [
                    'Usaquen', 'Cedritos', 'Santa Barbara', 'Santa Ana', 'Country Club', 'Toberin',
                    'Verbenal', 'San Cristobal Norte', 'Barrancas', 'La Calleja', 'Contador',
                    'Suba', 'Niza', 'Colina Campestre', 'Mazuren', 'Prado Veraniego', 'Tibabuyes',
                    'Rincon de Suba', 'Aures', 'La Gaitana', 'Bilbao', 'Britalia', 'Villa del Prado',
                    'Engativa', 'Normandia', 'Modelia', 'Boyaca Real', 'Santa Maria del Lago',
                    'Villas de Granada', 'Garces Navas', 'Alamos', 'Minuto de Dios', 'Quirigua',
                    'Fontibon', 'Hayuelos', 'Capellania', 'Villemar', 'Modelia Occidental',
                    'Zona Franca', 'Granjas de Techo', 'Ciudad Salitre Occidental', 'El Tintal',
                    'Kennedy', 'Castilla', 'Marsella', 'Patio Bonito', 'Tintal', 'Tintala',
                    'Ciudad Kennedy', 'Timiza', 'Roma', 'Carvajal', 'Las Delicias', 'Mandalay',
                    'Bosa', 'Bosa Centro', 'Bosa Occidental', 'Bosa Porvenir', 'El Recreo',
                    'La Libertad', 'San Bernardino', 'Tunjuelito', 'Venecia', 'Tunal', 'San Carlos',
                    'Abraham Lincoln', 'Antonio Narino', 'Restrepo', 'Ciudad Jardin', 'La Fragua',
                    'Rafael Uribe Uribe', 'Quiroga', 'Marruecos', 'Diana Turbay', 'San Jorge',
                    'Marco Fidel Suarez', 'San Cristobal', '20 de Julio', 'Veinte de Julio',
                    'La Victoria', 'Sosiego', 'Las Cruces', 'Usme', 'Yomasa', 'Santa Librada',
                    'Gran Yomasa', 'Danubio', 'La Aurora', 'Ciudad Bolivar', 'Candelaria La Nueva',
                    'Lucero', 'Jerusalen', 'Meissen', 'Perdomo', 'San Francisco', 'Arborizadora Alta',
                    'Arborizadora Baja',
                ],
            ],
            [
                'name' => 'Zona Extra',
                'code' => 'EXT',
                'price' => 10000,
                'keywords' => [
                    'Soacha', 'Ciudad Verde', 'Compartir', 'San Mateo', 'Ducales', 'Bosa San Jose',
                    'Chia', 'Cajica', 'Cota', 'Siberia', 'Funza', 'Mosquera', 'Madrid', 'Facatativa',
                    'Bojaca', 'Tenjo', 'Tabio', 'Zipaquira', 'La Calera', 'Sopo', 'Tocancipa',
                    'Gachancipa', 'Sibate', 'El Rosal', 'Subachoque', 'Sumapaz',
                ],
            ],
        ];

        foreach ($zones as $zone) {
            DeliveryZone::query()->updateOrCreate(
                ['tenant_id' => null, 'code' => $zone['code']],
                [
                    'name' => $zone['name'],
                    'price' => $zone['price'],
                    'coverage_keywords' => implode(', ', array_unique($zone['keywords'])),
                    'status' => 'active',
                ]
            );
        }
    }
}
