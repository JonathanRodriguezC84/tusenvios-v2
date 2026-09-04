<?php

namespace App\Console\Commands;

use App\Models\AffiliatedCompany;
use App\Models\Shipment;
use App\Models\ShipmentEvent;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SeedDashboardData extends Command
{
    protected $signature = 'dashboard:seed-demo {--tenant= : ID del tenant} {--clean : Borrar guias demo anteriores}';
    protected $description = 'Genera 50 guias realistas con costos y estados para poblar todas las tarjetas del dashboard';

    public function handle(): int
    {
        $tenantId = $this->option('tenant');

        if ($tenantId) {
            $tenant = Tenant::find($tenantId);
        } else {
            $tenant = Tenant::query()->where('status', 'active')->first() ?? Tenant::first();
        }

        if (! $tenant) {
            $this->error('No se encontro ningun tenant en la base de datos.');
            return 1;
        }

        $admin = User::query()
            ->where('tenant_id', $tenant->id)
            ->whereIn('role', ['tenant_admin', 'superadmin'])
            ->first() ?? User::query()->whereIn('role', ['superadmin', 'tenant_admin'])->first() ?? User::first();

        $courier = User::query()
            ->where('tenant_id', $tenant->id)
            ->where('role', 'courier')
            ->first() ?? $admin;

        $this->info("Usando Tenant: [ID {$tenant->id}] {$tenant->name} ({$tenant->subdomain})");
        $this->info("Usando Usuario Creador: [ID {$admin->id}] {$admin->name} ({$admin->email})");

        if ($this->option('clean')) {
            $demoIds = Shipment::query()->where('tenant_id', $tenant->id)->where('guide_number', 'like', 'DEMO-%')->pluck('id');
            ShipmentEvent::query()->whereIn('shipment_id', $demoIds)->delete();
            $deleted = Shipment::query()->whereIn('id', $demoIds)->delete();
            $this->warn("Se eliminaron {$deleted} guias demo anteriores y sus eventos.");
        }

        // Catalogo de productos con costos y precios realistas para ecommerce / logistica en Colombia
        $catalog = [
            [
                'name' => 'Audífonos Bluetooth Pro ANC',
                'sku' => 'AUD-ANC-01',
                'cost' => 38000,
                'price' => 89900,
            ],
            [
                'name' => 'Funda Silicona Magnética iPhone',
                'sku' => 'CSE-MAG-02',
                'cost' => 12000,
                'price' => 39900,
            ],
            [
                'name' => 'Cargador Carga Rápida 65W GaN',
                'sku' => 'CHG-GAN-65',
                'cost' => 24000,
                'price' => 64900,
            ],
            [
                'name' => 'Smartwatch Deportivo Ultra Fit',
                'sku' => 'SMW-FIT-04',
                'cost' => 52000,
                'price' => 129000,
            ],
            [
                'name' => 'Soporte Magnético Giratorio Auto',
                'sku' => 'HLD-CAR-05',
                'cost' => 11000,
                'price' => 34900,
            ],
            [
                'name' => 'Termo Inteligente Digital 500ml',
                'sku' => 'TRM-DIG-06',
                'cost' => 18500,
                'price' => 49900,
            ],
        ];

        // Destinatarios y zonas de Bogota
        $recipients = [
            ['Laura Pineda', 'Calle 72 # 10-34', 'Chapinero'],
            ['Mateo Rojas', 'Carrera 92 # 145-20', 'Suba'],
            ['Paula Gómez', 'Av. Primero de Mayo # 68-12', 'Kennedy'],
            ['Santiago Ruiz', 'Calle 116 # 19-45', 'Usaquén'],
            ['Valentina Mora', 'Carrera 15 # 93-60', 'Chicó'],
            ['Juan David Castro', 'Transversal 76 # 81-10', 'Engativá'],
            ['Daniela Peña', 'Calle 8 Sur # 38-20', 'Puente Aranda'],
            ['Andrés Gil', 'Carrera 100 # 22-15', 'Fontibón'],
            ['Camila Torres', 'Calle 26 # 25-50', 'Teusaquillo'],
            ['Felipe Ríos', 'Carrera 24 # 63-18', 'Barrios Unidos'],
            ['Mariana Duque', 'Carrera 7 # 156-30', 'Cedritos'],
            ['Carlos Méndez', 'Calle 53 # 16-20', 'Galerías'],
            ['Carolina Ospina', 'Carrera 58 # 127-10', 'Niza'],
            ['Sebastián Vargas', 'Calle 134 # 9-40', 'Contador'],
            ['Natalia Morales', 'Transversal 39 # 26-15', 'Salitre'],
        ];

        $now = Carbon::now();
        $guideSeq = 1001;

        // Generamos 20 guias en el periodo anterior (hace 7 a 13 dias) para dar métricas de crecimiento
        $this->info("Generando 20 guias del periodo anterior para métricas comparativas...");
        for ($i = 0; $i < 20; $i++) {
            $createdDaysAgo = 7 + ($i % 7); // Entre 7 y 13 dias atras
            $createdAt = (clone $now)->subDays($createdDaysAgo)->subHours(rand(2, 18))->subMinutes(rand(5, 55));
            $product = $catalog[$i % count($catalog)];
            $recipient = $recipients[$i % count($recipients)];
            $st = ($i % 3 === 0) ? 'delivered' : (($i % 5 === 0) ? 'returned' : 'delivered');

            $guideNum = 'DEMO-PREV-' . str_pad((string) ($guideSeq++), 5, '0', STR_PAD_LEFT);
            $qty = ($i % 5 === 0) ? 2 : 1;
            $itemCost = $product['cost'];
            $itemPrice = $product['price'];

            $shipment = new Shipment([
                'tenant_id' => $tenant->id,
                'created_by' => $admin->id,
                'courier_id' => $courier->id,
                'guide_number' => $guideNum,
                'status' => $st,
                'service_type' => 'standard',
                'sender_name' => $tenant->name,
                'sender_phone' => $tenant->phone ?? '3001234567',
                'sender_address' => 'Centro Logístico Principal',
                'sender_neighborhood' => 'Zona Franca',
                'sender_locality' => 'Fontibón',
                'recipient_name' => explode(' ', $recipient[0])[0],
                'recipient_lastname' => explode(' ', $recipient[0])[1] ?? 'Restrepo',
                'recipient_phone' => '3' . rand(100000000, 299999999),
                'recipient_address' => $recipient[1],
                'recipient_neighborhood' => $recipient[2],
                'recipient_locality' => $recipient[2],
                'recipient_city' => 'Bogotá',
                'package_type' => 'package',
                'pieces' => 1,
                'weight_kg' => 1.2,
                'content_description' => $product['name'] . ($qty > 1 ? " x{$qty}" : ''),
                'inventory_snapshot' => [
                    [
                        'name' => $product['name'],
                        'quantity' => $qty,
                        'cost' => $itemCost,
                        'price' => $itemPrice,
                    ],
                ],
                'declared_value' => $itemPrice * $qty,
                'shipping_value' => 8500,
                'payment_method' => 'cod',
                'collection_value' => $itemPrice * $qty,
                'zone' => strtoupper($recipient[2]),
                'delivery_attempts' => 1,
            ]);

            $shipment->created_at = $createdAt;
            $shipment->updated_at = (clone $createdAt)->addHours(24);
            $shipment->save();
        }

        $this->info("Generando las 50 guias principales distribuidas en los ultimos 7 dias...");

        // Distribucion estrategica de estados para 50 guias:
        // - Entregadas: 24 (48%)
        // - En camino: 14 (en bodega: 6, en ruta: 8)
        // - Novedades: 4 (failed_delivery: 3, rescheduled: 1)
        // - Devoluciones: 5 (return_pending: 3, returned: 2)
        // - Canceladas: 3 (6%)
        // Total = 50 guias
        $statusDistribution = [
            'delivered' => 24,
            'on_route' => 5,
            'assigned' => 3,
            'in_warehouse' => 4,
            'in_sorting' => 2,
            'failed_delivery' => 3,
            'rescheduled' => 1,
            'return_pending' => 3,
            'returned' => 2,
            'cancelled' => 3,
        ];

        // Distribucion diaria a lo largo de los ultimos 7 dias (0 = hoy, 6 = hace 6 dias)
        $dayDistribution = [
            0 => 9,  // Hoy
            1 => 8,  // Ayer
            2 => 8,  // Hace 2 dias
            3 => 7,  // Hace 3 dias
            4 => 7,  // Hace 4 dias
            5 => 6,  // Hace 5 dias
            6 => 5,  // Hace 6 dias
        ]; // Suma = 50

        $dayList = [];
        foreach ($dayDistribution as $day => $amt) {
            for ($k = 0; $k < $amt; $k++) {
                $dayList[] = $day;
            }
        }
        // No usar shuffle aleatorio completo para que cada dia tenga entregadas y operacion balanceada
        // pero asegurar variedad
        $counter = 0;

        foreach ($statusDistribution as $status => $count) {
            for ($j = 0; $j < $count; $j++) {
                $dayOffset = $dayList[$counter % count($dayList)];
                $createdAt = (clone $now)->subDays($dayOffset)->subHours(rand(1, 16))->subMinutes(rand(1, 55));
                $recipient = $recipients[$counter % count($recipients)];

                // Seleccionar 1 o 2 productos para el snapshot con costos y precios
                $pIndex = ($counter * 2 + $j) % count($catalog);
                $prod1 = $catalog[$pIndex];
                $qty1 = ($counter % 6 === 0) ? 2 : 1;

                $snapshot = [
                    [
                        'name' => $prod1['name'],
                        'sku' => $prod1['sku'],
                        'quantity' => $qty1,
                        'cost' => $prod1['cost'],
                        'price' => $prod1['price'],
                    ]
                ];

                // Algunas ordenes tienen un segundo producto
                if ($counter % 4 === 0) {
                    $prod2 = $catalog[($pIndex + 1) % count($catalog)];
                    $snapshot[] = [
                        'name' => $prod2['name'],
                        'sku' => $prod2['sku'],
                        'quantity' => 1,
                        'cost' => $prod2['cost'],
                        'price' => $prod2['price'],
                    ];
                }

                $totalSales = 0;
                $totalCost = 0;
                $descParts = [];
                foreach ($snapshot as $item) {
                    $totalSales += $item['quantity'] * $item['price'];
                    $totalCost += $item['quantity'] * $item['cost'];
                    $descParts[] = $item['name'] . ($item['quantity'] > 1 ? " x{$item['quantity']}" : '');
                }

                $guideNum = 'DEMO-BOG-' . str_pad((string) ($guideSeq++), 5, '0', STR_PAD_LEFT);
                $shippingValue = match ($recipient[2]) {
                    'Chapinero', 'Teusaquillo', 'Barrios Unidos', 'Chicó', 'Galerías' => 7500,
                    'Suba', 'Engativá', 'Kennedy', 'Usaquén', 'Fontibón', 'Salitre' => 8500,
                    default => 9500,
                };

                $paymentMethod = in_array($status, ['cancelled', 'returned'], true) ? 'credit' : 'cod';
                $collectionValue = ($paymentMethod === 'cod') ? $totalSales : 0;

                // Para entregadas, updated_at debe estar dentro del rango (despues de createdAt pero antes o igual a now)
                $updatedAt = (clone $createdAt)->addHours(rand(2, 20));
                if ($updatedAt->isAfter($now)) {
                    $updatedAt = (clone $now)->subMinutes(rand(5, 60));
                }

                $issueReason = null;
                if ($status === 'failed_delivery') {
                    $reasons = ['Destinatario ausente en primer intento', 'Dirección con número incompleto', 'Teléfono no responde'];
                    $issueReason = $reasons[$counter % count($reasons)];
                } elseif ($status === 'rescheduled') {
                    $issueReason = 'Cliente solicitó entrega en horario de la tarde';
                }

                $shipment = new Shipment([
                    'tenant_id' => $tenant->id,
                    'created_by' => $admin->id,
                    'courier_id' => in_array($status, ['on_route', 'delivered', 'failed_delivery', 'rescheduled']) ? $courier->id : null,
                    'guide_number' => $guideNum,
                    'status' => $status,
                    'service_type' => ($counter % 4 === 0) ? 'express' : 'standard',
                    'sender_name' => $tenant->name,
                    'sender_phone' => $tenant->phone ?? '3001234567',
                    'sender_address' => 'Bodega Central de Despachos',
                    'sender_neighborhood' => 'Zona Industrial',
                    'sender_locality' => 'Puente Aranda',
                    'recipient_name' => explode(' ', $recipient[0])[0],
                    'recipient_lastname' => explode(' ', $recipient[0])[1] ?? 'Gómez',
                    'recipient_phone' => '3' . rand(100000000, 299999999),
                    'recipient_address' => $recipient[1],
                    'recipient_neighborhood' => $recipient[2],
                    'recipient_locality' => $recipient[2],
                    'recipient_city' => 'Bogotá',
                    'package_type' => 'package',
                    'pieces' => count($snapshot),
                    'weight_kg' => round(0.6 + ($counter % 4) * 0.4, 2),
                    'content_description' => implode(' + ', $descParts),
                    'inventory_snapshot' => $snapshot,
                    'declared_value' => $totalSales * 1.5,
                    'shipping_value' => $shippingValue,
                    'payment_method' => $paymentMethod,
                    'collection_value' => $collectionValue,
                    'zone' => strtoupper($recipient[2]),
                    'delivery_attempts' => in_array($status, ['failed_delivery', 'rescheduled', 'returned', 'return_pending']) ? 2 : 1,
                    'issue_reason' => $issueReason,
                ]);

                // Asignar explicitamente timestamps para que Eloquent no los sobreescriba con now()
                $shipment->created_at = $createdAt;
                $shipment->updated_at = $updatedAt;
                $shipment->save();

                // Eventos de trazabilidad
                ShipmentEvent::query()->create([
                    'shipment_id' => $shipment->id,
                    'user_id' => $admin->id,
                    'status' => 'created',
                    'location' => 'Sistema',
                    'notes' => 'Guía generada desde la plataforma.',
                    'recorded_at' => $createdAt,
                ]);

                if ($status !== 'created') {
                    ShipmentEvent::query()->create([
                        'shipment_id' => $shipment->id,
                        'user_id' => $courier->id,
                        'status' => $status,
                        'location' => $shipment->zone,
                        'notes' => $issueReason ?? "Estado actualizado a {$status}.",
                        'recorded_at' => $updatedAt,
                    ]);
                }

                $counter++;
            }
        }

        $this->info("¡Completado exitosamente! Se crearon {$counter} guías demo actuales con fechas y costos distribuidos.");
        $this->info("Todas las tarjetas del Dashboard (Operación, Logística, Novedades, Gráficas y Top Productos) ahora tienen datos vivos.");

        return 0;
    }
}
