<?php

namespace Database\Seeders;

use App\Models\AffiliatedCompany;
use App\Models\Shipment;
use App\Models\ShipmentEvent;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@tusenvios.com.co'],
            ['name' => 'Administrador RCI', 'password' => 'password']
        );

        $tenant = Tenant::query()->updateOrCreate(
            ['subdomain' => 'rci'],
            [
                'name' => 'RCI Envios',
                'legal_name' => 'RCI Envios',
                'document_number' => '900000000',
                'email' => 'operaciones@rci.com.co',
                'phone' => '3000000000',
                'status' => 'active',
            ]
        );

        $companies = collect([
            ['name' => 'Tienda Norte', 'contact_name' => 'Laura Pineda'],
            ['name' => 'Comercial Andes', 'contact_name' => 'Mateo Rojas'],
            ['name' => 'Moda Express', 'contact_name' => 'Paula Gomez'],
            ['name' => 'Electro Hogar', 'contact_name' => 'Santiago Ruiz'],
        ])->map(fn ($company) => AffiliatedCompany::query()->updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => $company['name']],
            [
                'contact_name' => $company['contact_name'],
                'email' => strtolower(str_replace(' ', '.', $company['name'])).'@example.com',
                'phone' => '3100000000',
                'status' => 'active',
            ]
        ));

        $shipments = [
            [
                'company' => $companies[0],
                'guide_number' => 'RCI-BOG-2026-000184',
                'status' => 'in_sorting',
                'recipient_name' => 'Laura Pineda',
                'recipient_address' => 'Calle 72 # 10-34',
                'recipient_phone' => '3111111111',
                'recipient_locality' => 'Chapinero',
                'zone' => 'Chapinero',
            ],
            [
                'company' => $companies[1],
                'guide_number' => 'RCI-BOG-2026-000183',
                'status' => 'on_route',
                'recipient_name' => 'Mateo Rojas',
                'recipient_address' => 'Carrera 92 # 145-20',
                'recipient_phone' => '3222222222',
                'recipient_locality' => 'Suba',
                'zone' => 'Suba',
            ],
            [
                'company' => $companies[2],
                'guide_number' => 'RCI-BOG-2026-000182',
                'status' => 'delivered',
                'recipient_name' => 'Paula Gomez',
                'recipient_address' => 'Avenida Primero de Mayo # 68-12',
                'recipient_phone' => '3333333333',
                'recipient_locality' => 'Kennedy',
                'zone' => 'Kennedy',
            ],
            [
                'company' => $companies[3],
                'guide_number' => 'RCI-BOG-2026-000181',
                'status' => 'failed_delivery',
                'recipient_name' => 'Santiago Ruiz',
                'recipient_address' => 'Calle 116 # 19-45',
                'recipient_phone' => '3444444444',
                'recipient_locality' => 'Usaquen',
                'zone' => 'Usaquen',
                'issue_reason' => 'Destinatario ausente',
            ],
        ];

        foreach ($shipments as $data) {
            $company = $data['company'];
            unset($data['company']);

            $shipment = Shipment::query()->updateOrCreate(
                ['guide_number' => $data['guide_number']],
                array_merge($data, [
                    'tenant_id' => $tenant->id,
                    'affiliated_company_id' => $company->id,
                    'created_by' => $admin->id,
                    'sender_name' => $company->name,
                    'sender_phone' => $company->phone,
                    'sender_address' => 'Bodega RCI Bogota',
                    'sender_locality' => 'Bogota',
                    'package_type' => 'package',
                    'pieces' => 1,
                    'shipping_value' => 8000,
                    'payment_method' => 'credit',
                ])
            );

            ShipmentEvent::query()->updateOrCreate(
                ['shipment_id' => $shipment->id, 'status' => 'created'],
                ['user_id' => $admin->id, 'location' => 'Sistema', 'recorded_at' => now()->subHours(3)]
            );

            ShipmentEvent::query()->updateOrCreate(
                ['shipment_id' => $shipment->id, 'status' => $shipment->status],
                ['user_id' => $admin->id, 'location' => $shipment->zone, 'recorded_at' => now()->subHours(1)]
            );
        }
    }
}
