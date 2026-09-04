<?php

namespace Database\Seeders;

use App\Models\Shipment;
use App\Models\ShipmentEvent;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminTestDataSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $plan = SubscriptionPlan::query()->where('code', 'emprende')->firstOrFail();

        $clients = [
            [
                'tenant' => [
                    'name' => 'Tienda Norte',
                    'email' => 'contacto@tiendanorte.com',
                    'phone' => '6012345678',
                    'subdomain' => 'tiendanorte',
                    'guide_prefix' => 'TDN',
                    'status' => 'active',
                    'balance' => 45000,
                    'brand_color' => '#0EA5E9',
                ],
                'admin' => ['name' => 'Marcela Ortiz', 'email' => 'admin@tiendanorte.com'],
                'courier' => ['name' => 'Jhon Cardona', 'email' => 'mensajero@tiendanorte.com'],
                'subscription' => ['status' => 'active', 'next_payment' => now()->addDays(4)],
            ],
            [
                'tenant' => [
                    'name' => 'Comercial Andes',
                    'email' => 'ventas@comercialandes.com',
                    'phone' => '6023456789',
                    'subdomain' => 'comercialandes',
                    'guide_prefix' => 'CDA',
                    'status' => 'active',
                    'balance' => 12000,
                    'brand_color' => '#F97316',
                ],
                'admin' => ['name' => 'Carlos Velez', 'email' => 'admin@comercialandes.com'],
                'courier' => ['name' => 'Diego Mora', 'email' => 'mensajero@comercialandes.com'],
                'subscription' => ['status' => 'active', 'next_payment' => now()->addDays(23)],
            ],
            [
                'tenant' => [
                    'name' => 'Moda Express',
                    'email' => 'info@modaexpress.co',
                    'phone' => '6034567890',
                    'subdomain' => 'modaexpress',
                    'guide_prefix' => 'MDX',
                    'status' => 'active',
                    'balance' => 0,
                    'brand_color' => '#EC4899',
                ],
                'admin' => ['name' => 'Luisa Fernandez', 'email' => 'admin@modaexpress.co'],
                'courier' => ['name' => 'Sebastian Rojas', 'email' => 'mensajero@modaexpress.co'],
                'subscription' => ['status' => 'past_due', 'next_payment' => now()->subDays(3)],
            ],
            [
                'tenant' => [
                    'name' => 'Electro Hogar',
                    'email' => 'pedidos@electrohogar.com',
                    'phone' => '6045678901',
                    'subdomain' => 'electrohogar',
                    'guide_prefix' => 'ELH',
                    'status' => 'paused',
                    'balance' => 0,
                    'brand_color' => '#6366F1',
                ],
                'admin' => ['name' => 'Andres Pardo', 'email' => 'admin@electrohogar.com'],
                'courier' => ['name' => 'Natalia Gomez', 'email' => 'mensajero@electrohogar.com'],
                'subscription' => ['status' => 'cancelled', 'next_payment' => null],
            ],
            [
                'tenant' => [
                    'name' => 'Cafe La Colina',
                    'email' => 'hola@cafelacolina.co',
                    'phone' => '6056789012',
                    'subdomain' => 'cafelacolina',
                    'guide_prefix' => 'CLC',
                    'status' => 'active',
                    'balance' => 8000,
                    'brand_color' => '#10B981',
                ],
                'admin' => ['name' => 'Diana Lopez', 'email' => 'admin@cafelacolina.co'],
                'courier' => ['name' => 'Mauricio Torres', 'email' => 'mensajero@cafelacolina.co'],
                'subscription' => [
                    'status' => 'active',
                    'start_mode' => 'trial_guides',
                    'trial_limit' => 30,
                    'trial_used' => 12,
                    'next_payment' => now()->addDays(15),
                ],
            ],
        ];

        $recipientPools = [
            ['Valentina Mora', 'Calle 5 # 12-34', 'Chapinero'],
            ['Santiago Ruiz', 'Carrera 7 # 88-12', 'Usaquen'],
            ['Daniela Peña', 'Av 68 # 45-10', 'Engativa'],
            ['Felipe Rios', 'Calle 32 # 19-45', 'Teusaquillo'],
            ['Camila Torres', 'Carrera 15 # 93-60', 'Barrios Unidos'],
        ];

        $productNames = [
            'Camiseta algodon talla M', 'Cargador rapido USB-C 65W', 'Audifonos bluetooth TWS',
            'Vaso termico 500ml', 'Jeans skinny talla 30', 'Buzo oversize talla L',
        ];

        $sequenceByTenant = [];

        foreach ($clients as $client) {
            $tenantData = $client['tenant'];
            $tenant = Tenant::query()->updateOrCreate(
                ['subdomain' => $tenantData['subdomain']],
                array_merge($tenantData, [
                    'legal_name' => $tenantData['name'] . ' SAS',
                    'document_number' => '900' . str_pad((string) rand(100000, 999999), 6, '0', STR_PAD_LEFT),
                    'brand_message' => 'Gracias por tu compra en ' . $tenantData['name'] . '.',
                ])
            );

            $subscription = $client['subscription'];
            $isTrial = ($subscription['start_mode'] ?? null) === 'trial_guides';

            $sub = TenantSubscription::query()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'subscription_plan_id' => $plan->id],
                [
                    'status' => $subscription['status'],
                    'start_mode' => $isTrial ? 'trial_guides' : 'paid',
                    'trial_guide_limit' => $isTrial ? $subscription['trial_limit'] : 0,
                    'trial_guide_used' => $isTrial ? $subscription['trial_used'] : 0,
                    'starts_at' => now()->subMonths(4),
                    'ends_at' => null,
                    'next_payment_at' => $subscription['next_payment'],
                    'notes' => 'Datos de prueba para panel admin.',
                ]
            );

            $admin = User::query()->updateOrCreate(
                ['email' => $client['admin']['email']],
                [
                    'tenant_id' => $tenant->id,
                    'name' => $client['admin']['name'],
                    'password' => bcrypt('password'),
                    'role' => 'tenant_admin',
                    'status' => 'active',
                ]
            );

            User::query()->updateOrCreate(
                ['email' => $client['courier']['email']],
                [
                    'tenant_id' => $tenant->id,
                    'name' => $client['courier']['name'],
                    'password' => bcrypt('password'),
                    'role' => 'courier',
                    'status' => 'active',
                ]
            );

            $this->createPayments($tenant, $sub, $plan, $client['subscription']['status']);

            $sequenceByTenant[$tenant->id] = 1;
            foreach ($recipientPools as $i => [$recipientName, $address, $locality]) {
                if ($i >= 4) {
                    break;
                }
                $this->createShipment($tenant, $admin, $plan, $client['subscription'], $recipientName, $address, $locality, $productNames, $sequenceByTenant);
            }

            $this->command->info("Cliente {$tenant->name} ({$tenant->subdomain}) listo.");
        }
    }

    private function createPayments(Tenant $tenant, TenantSubscription $subscription, SubscriptionPlan $plan, string $subStatus): void
    {
        $monthly = (int) $plan->monthly_price;

        for ($offset = 5; $offset >= 1; $offset--) {
            $paidAt = now()->subMonths($offset)->startOfMonth()->addDays(rand(1, 20));
            SubscriptionPayment::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'tenant_subscription_id' => $subscription->id,
                    'reference' => 'TE-' . $tenant->id . '-' . $paidAt->format('Ymd'),
                ],
                [
                    'subscription_plan_id' => $plan->id,
                    'provider' => 'bold',
                    'provider_link_id' => 'link_' . $tenant->id . '_' . $paidAt->format('Ymd'),
                    'provider_transaction_id' => 'txn_' . $tenant->id . '_' . $paidAt->format('Ymd'),
                    'status' => 'paid',
                    'amount' => $monthly,
                    'currency' => 'COP',
                    'payment_url' => 'https://checkout.bold.co/' . $tenant->id . '/' . $paidAt->format('Ymd'),
                    'paid_at' => $paidAt,
                    'expires_at' => $paidAt->copy()->addMonth(),
                ]
            );
        }

        if ($subStatus === 'past_due' || $subStatus === 'cancelled') {
            SubscriptionPayment::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'tenant_subscription_id' => $subscription->id,
                    'reference' => 'TE-' . $tenant->id . '-' . now()->format('Ymd') . '-PENDING',
                ],
                [
                    'subscription_plan_id' => $plan->id,
                    'provider' => 'bold',
                    'status' => 'pending',
                    'amount' => $monthly,
                    'currency' => 'COP',
                    'expires_at' => now()->addDays(2),
                ]
            );
        }
    }

    private function createShipment(Tenant $tenant, User $admin, SubscriptionPlan $plan, array $subscription, string $recipientName, string $address, string $locality, array $productNames, array &$sequenceByTenant): void
    {
        $sequence = $sequenceByTenant[$tenant->id];
        $sequenceByTenant[$tenant->id] = $sequence + 1;

        $statusByIndex = [0 => 'delivered', 1 => 'on_route', 2 => 'returned', 3 => 'delivered'];
        $status = $statusByIndex[($sequence - 1) % 4] ?? 'delivered';

        $prefix = strtoupper($tenant->guide_prefix ?: 'TE');
        $guide = $prefix . '-BOG-2026-' . str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
        $product = $productNames[($sequence - 1) % count($productNames)];
        $price = [25000, 35000, 45000, 28000, 65000, 55000][($sequence - 1) % 6];

        $shippingValue = match ($locality) {
            'Chapinero', 'Teusaquillo', 'Barrios Unidos' => 7000,
            'Usaquen', 'Engativa' => 8000,
            default => 8000,
        };

        $shipment = Shipment::query()->create([
            'tenant_id' => $tenant->id,
            'created_by' => $admin->id,
            'guide_number' => $guide,
            'status' => $status,
            'service_type' => $sequence % 3 === 0 ? 'express' : 'standard',
            'sender_name' => $tenant->name,
            'sender_phone' => $tenant->phone,
            'sender_address' => 'Bodega principal Bogota',
            'sender_neighborhood' => 'Industrial',
            'sender_locality' => 'Puente Aranda',
            'recipient_name' => explode(' ', $recipientName)[0],
            'recipient_lastname' => explode(' ', $recipientName)[1] ?? 'Garcia',
            'recipient_phone' => '3' . str_pad((string) (300000000 + $sequence * 7 + $tenant->id), 9, '0', STR_PAD_LEFT),
            'recipient_address' => $address,
            'recipient_neighborhood' => $locality,
            'recipient_locality' => $locality,
            'recipient_city' => 'Bogota',
            'package_type' => 'package',
            'pieces' => 1,
            'weight_kg' => 1.25,
            'content_description' => $product,
            'inventory_snapshot' => [['name' => $product, 'quantity' => 1, 'price' => $price]],
            'declared_value' => $price * 2,
            'shipping_value' => $shippingValue,
            'payment_method' => $status === 'delivered' ? 'cod' : 'credit',
            'collection_value' => $price,
            'zone' => strtoupper($locality),
            'delivery_attempts' => 1,
            'estimated_delivery_date' => $status === 'delivered' ? now()->subDays($sequence) : now()->addDays(1),
            'created_at' => now()->subDays($sequence * 3)->subHours($sequence),
            'updated_at' => now(),
        ]);

        ShipmentEvent::query()->create([
            'shipment_id' => $shipment->id,
            'user_id' => $admin->id,
            'status' => 'created',
            'location' => 'Sistema',
            'notes' => 'Guia creada desde el panel.',
            'recorded_at' => $shipment->created_at,
        ]);

        if ($status !== 'created') {
            ShipmentEvent::query()->create([
                'shipment_id' => $shipment->id,
                'user_id' => $admin->id,
                'status' => $status,
                'location' => $shipment->zone,
                'notes' => "Estado actualizado a {$status}.",
                'recorded_at' => now()->subDays($sequence * 2)->subHours($sequence),
            ]);
        }
    }
}
