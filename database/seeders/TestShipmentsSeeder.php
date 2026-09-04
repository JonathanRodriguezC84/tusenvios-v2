<?php

namespace Database\Seeders;

use App\Models\Shipment;
use App\Models\ShipmentEvent;
use App\Models\Tenant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestShipmentsSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $tenant = Tenant::findOrFail(1);

        $statusPlan = [
            'created' => 5,
            'printed' => 4,
            'in_warehouse' => 4,
            'in_sorting' => 4,
            'assigned' => 4,
            'on_route' => 7,
            'failed_delivery' => 3,
            'rescheduled' => 3,
            'return_pending' => 3,
            'returned' => 4,
            'delivered' => 7,
            'cancelled' => 2,
        ];

        $recipients = [
            ['Laura Pineda', 'Tienda Norte', 'Chapinero'],
            ['Mateo Rojas', 'Comercial Andes', 'Suba'],
            ['Paula Gomez', 'Moda Express', 'Kennedy'],
            ['Santiago Ruiz', 'Electro Hogar', 'Usaquen'],
            ['Valentina Mora', 'Tienda Norte', 'Teusaquillo'],
            ['Juan Castro', 'Comercial Andes', 'Engativa'],
            ['Daniela Peña', 'Moda Express', 'Puente Aranda'],
            ['Andres Gil', 'Electro Hogar', 'Fontibon'],
            ['Camila Torres', 'Tienda Norte', 'Los Martires'],
            ['Felipe Rios', 'Comercial Andes', 'Barrios Unidos'],
        ];

        $products = [
            ['name' => 'ESTUCHE IPHONE 16 PROMAX', 'price' => 16000],
            ['name' => 'Funda silicona Galaxy S24', 'price' => 12000],
            ['name' => 'Cargador rapido USB-C 65W', 'price' => 35000],
            ['name' => 'Audifonos bluetooth TWS', 'price' => 45000],
            ['name' => 'Reloj smartwatch X1', 'price' => 89000],
            ['name' => 'Camiseta algodon talla M', 'price' => 25000],
            ['name' => 'Jeans skinny talla 30', 'price' => 65000],
            ['name' => 'Zapatos deportivos talla 42', 'price' => 120000],
            ['name' => 'Buzo oversize talla L', 'price' => 55000],
            ['name' => 'Vaso termico 500ml', 'price' => 28000],
        ];

        $startSequence = 205;
        $total = 0;
        $created = 0;

        foreach ($statusPlan as $status => $count) {
            for ($i = 0; $i < $count; $i++) {
                $sequence = $startSequence + $total;
                $total++;

                [$recipientName, $companyName, $locality] = $recipients[$total % count($recipients)];
                $product = $products[$total % count($products)];

                $shippingValue = match ($locality) {
                    'Chapinero', 'Teusaquillo', 'Puente Aranda', 'Los Martires', 'Barrios Unidos' => 7000,
                    'Suba', 'Engativa', 'Kennedy', 'Usaquen', 'Fontibon' => 8000,
                    default => 10000,
                };

                $declaredValue = $product['price'] * 2;
                $collectionValue = $product['price'];
                $paymentMethod = in_array($status, ['cancelled', 'returned', 'return_pending'], true) ? 'credit' : 'cod';

                $data = [
                    'tenant_id' => $tenant->id,
                    'created_by' => 2,
                    'guide_number' => "RCI-BOG-2026-".str_pad((string) $sequence, 5, '0', STR_PAD_LEFT),
                    'status' => $status,
                    'service_type' => $total % 3 === 0 ? 'express' : 'standard',
                    'sender_name' => $companyName,
                    'sender_phone' => '6015550101',
                    'sender_address' => 'Bodega principal Bogota',
                    'sender_neighborhood' => 'Industrial',
                    'sender_locality' => 'Puente Aranda',
                    'recipient_name' => explode(' ', $recipientName)[0],
                    'recipient_lastname' => explode(' ', $recipientName)[1] ?? 'Perez',
                    'recipient_phone' => '3'.str_pad((string) (100000000 + $total), 9, '0', STR_PAD_LEFT),
                    'recipient_address' => 'Calle '.($total + 10).' # '.($total * 2 % 90 + 1).'-'.($total % 30 + 1),
                    'recipient_neighborhood' => $locality,
                    'recipient_locality' => $locality,
                    'recipient_city' => 'Bogota',
                    'package_type' => 'package',
                    'pieces' => $total % 4 === 0 ? 2 : 1,
                    'weight_kg' => round(0.5 + ($total % 5) * 0.75, 2),
                    'content_description' => $product['name'],
                    'inventory_snapshot' => [
                        [
                            'name' => $product['name'],
                            'quantity' => $total % 4 === 0 ? 2 : 1,
                            'price' => $product['price'],
                        ],
                    ],
                    'declared_value' => $declaredValue,
                    'shipping_value' => $shippingValue,
                    'payment_method' => $paymentMethod,
                    'collection_value' => $collectionValue,
                    'zone' => strtoupper($locality),
                    'delivery_attempts' => in_array($status, ['failed_delivery', 'rescheduled', 'returned', 'return_pending'], true) ? 2 : 1,
                    'issue_reason' => in_array($status, ['failed_delivery', 'rescheduled'], true) ? 'Destinatario ausente' : null,
                    'created_at' => now()->subDays($total % 20)->subHours($total % 24),
                    'updated_at' => now(),
                ];

                if (in_array($status, ['cancelled', 'delivered', 'returned'], true)) {
                    $data['estimated_delivery_date'] = now()->subDays($total % 10);
                }

                $shipment = Shipment::query()->create($data);
                $created++;

                ShipmentEvent::query()->create([
                    'shipment_id' => $shipment->id,
                    'user_id' => 2,
                    'status' => 'created',
                    'location' => 'Sistema',
                    'notes' => 'Guia creada desde el panel.',
                    'recorded_at' => $shipment->created_at,
                ]);

                if ($status !== 'created') {
                    ShipmentEvent::query()->create([
                        'shipment_id' => $shipment->id,
                        'user_id' => 2,
                        'status' => $status,
                        'location' => $shipment->zone,
                        'notes' => "Estado actualizado a {$status}.",
                        'recorded_at' => now()->subHours($total % 48),
                    ]);
                }
            }
        }

        $this->command->info("Seeder TestShipmentsSeeder ejecutado: {$created} guias creadas.");
    }
}
