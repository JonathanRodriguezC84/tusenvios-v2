param(
    [string] $Destination = "C:\laragon\www\tus-envios",
    [string] $Database = "tus_envios",
    [int] $Port = 8001
)

$ErrorActionPreference = "Stop"

$Source = (Resolve-Path (Join-Path $PSScriptRoot "..")).Path
$Php = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"

Write-Host "Separando Tus Envios..."
Write-Host "Origen:  $Source"
Write-Host "Destino: $Destination"

if (Test-Path -LiteralPath $Destination) {
    throw "La carpeta destino ya existe. Cambia el nombre o elimina primero: $Destination"
}

New-Item -ItemType Directory -Path $Destination | Out-Null

robocopy $Source $Destination /E /XD `
    "$Source\storage\logs" `
    /XF "$Source\.env.backup" /NFL /NDL /NJH /NJS /NP

if ($LASTEXITCODE -gt 7) {
    throw "La copia fallo con codigo $LASTEXITCODE"
}

$envPath = Join-Path $Destination ".env"
$envText = Get-Content -LiteralPath $envPath -Raw
$envText = $envText -replace 'APP_NAME=.*', 'APP_NAME="Tus Envios"'
$envText = $envText -replace 'APP_URL=.*', "APP_URL=http://127.0.0.1:$Port"
$envText = $envText -replace 'DB_DATABASE=.*', "DB_DATABASE=$Database"
Set-Content -LiteralPath $envPath -Value $envText -Encoding UTF8

Push-Location $Destination

& $Php artisan key:generate --force

$createDb = @'
<?php
require 'vendor/autoload.php';

$env = [];
foreach (file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if (str_starts_with(trim($line), '#') || ! str_contains($line, '=')) {
        continue;
    }

    [$key, $value] = explode('=', $line, 2);
    $env[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
}

$host = $env['DB_HOST'] ?? '127.0.0.1';
$port = $env['DB_PORT'] ?? '3306';
$database = $env['DB_DATABASE'] ?? 'tus_envios';
$username = $env['DB_USERNAME'] ?? 'root';
$password = $env['DB_PASSWORD'] ?? '';

$pdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$safeDatabase = str_replace('`', '``', $database);
$pdo->exec("CREATE DATABASE IF NOT EXISTS `{$safeDatabase}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

echo "Base de datos lista: {$database}\n";
'@

$createDbPath = Join-Path $Destination "storage\app\create-tus-envios-db.php"
Set-Content -LiteralPath $createDbPath -Value $createDb -Encoding UTF8
& $Php $createDbPath
Remove-Item -LiteralPath $createDbPath -Force

& $Php artisan migrate --force
& $Php artisan storage:link

$seed = @'
<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\QuickProduct;
use App\Models\Shipment;
use App\Models\ShipmentEvent;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

$email = 'demo@tusenvios.com.co';

User::where('email', $email)->delete();
$oldTenant = Tenant::where('subdomain', 'demo-tus-envios')->first();
if ($oldTenant) {
    User::where('tenant_id', $oldTenant->id)->delete();
    $oldTenant->delete();
}

$tenant = Tenant::create([
    'name' => 'Dulce Aroma Store',
    'legal_name' => 'Dulce Aroma Store S.A.S.',
    'document_number' => '901234567',
    'email' => 'hola@dulcearoma.test',
    'phone' => '3001234567',
    'subdomain' => 'demo-tus-envios',
    'guide_prefix' => 'DA',
    'status' => 'active',
    'brand_color' => '#dc2626',
    'brand_whatsapp' => '3001234567',
    'brand_instagram' => '@dulcearomastore',
    'brand_website' => 'tusenvios.com.co',
    'brand_message' => 'Gracias por apoyar nuestro emprendimiento.',
    'label_template' => 'bold',
]);

$user = User::create([
    'tenant_id' => $tenant->id,
    'name' => 'Demo Tus Envios',
    'email' => $email,
    'password' => Hash::make('demo12345'),
    'role' => 'tenant_admin',
    'status' => 'active',
]);

$plan = SubscriptionPlan::where('code', 'emprende')->first();
if ($plan) {
    TenantSubscription::create([
        'tenant_id' => $tenant->id,
        'subscription_plan_id' => $plan->id,
        'status' => 'active',
        'starts_at' => now()->toDateString(),
        'next_payment_at' => now()->addMonth()->toDateString(),
        'notes' => 'Cuenta demo local.',
    ]);
}

foreach (['Vela aromatica', 'Kit regalo', 'Body splash', 'Caja sorpresa'] as $name) {
    QuickProduct::create([
        'tenant_id' => $tenant->id,
        'name' => $name,
        'package_type' => 'package',
        'status' => 'active',
    ]);
}

$rows = [
    ['001', 'created', 'Laura Medina', 'Calle 72 #10-34', 'Chapinero', 'Bogota', '3004567890', 'Norte', 1, 80000, 12000, 'cod', 'Vela aromatica vainilla'],
    ['002', 'printed', 'Camilo Torres', 'Carrera 15 #88-20', 'El Lago', 'Bogota', '3012223344', 'Norte', 1, 95000, 12000, 'cod', 'Kit regalo pequeno'],
    ['003', 'on_route', 'Natalia Ruiz', 'Av. Siempre Viva #45-12', 'La Castellana', 'Bogota', '3029876543', 'Noroccidente', 2, 130000, 14000, 'cod', 'Caja sorpresa'],
    ['004', 'delivered', 'Andres Gomez', 'Calle 19 #4-55', 'Centro', 'Bogota', '3101112233', 'Centro', 1, 62000, 10000, 'cash', 'Body splash'],
    ['005', 'failed_delivery', 'Paula Rios', 'Carrera 50 #26-18', 'Galerias', 'Bogota', '3154445566', 'Occidente', 1, 110000, 14000, 'cod', 'Kit regalo grande'],
    ['006', 'return_pending', 'Sofia Cardenas', 'Calle 140 #12-44', 'Cedritos', 'Bogota', '3203332211', 'Norte', 1, 74000, 12000, 'cod', 'Vela aromatica lavanda'],
];

foreach ($rows as $index => $row) {
    [$suffix, $status, $recipient, $address, $neighborhood, $locality, $phone, $zone, $pieces, $collection, $shipping, $payment, $content] = $row;

    $shipment = Shipment::create([
        'tenant_id' => $tenant->id,
        'created_by' => $user->id,
        'guide_number' => 'DA-2026-000'.$suffix,
        'status' => $status,
        'service_type' => 'standard',
        'estimated_delivery_date' => now()->addDay()->toDateString(),
        'sender_name' => $tenant->name,
        'sender_document' => $tenant->document_number,
        'sender_phone' => $tenant->phone,
        'sender_address' => 'Bodega principal - Calle 100 #15-20',
        'sender_neighborhood' => 'Chico',
        'sender_locality' => 'Bogota',
        'recipient_name' => $recipient,
        'recipient_phone' => $phone,
        'recipient_address' => $address,
        'recipient_neighborhood' => $neighborhood,
        'recipient_locality' => $locality,
        'recipient_notes' => $status === 'failed_delivery' ? 'Cliente no contesta, reprogramar.' : null,
        'package_type' => 'package',
        'pieces' => $pieces,
        'weight_kg' => 1.2,
        'content_description' => $content,
        'declared_value' => $collection,
        'shipping_value' => $shipping,
        'payment_method' => $payment,
        'collection_value' => $payment === 'cod' ? $collection : 0,
        'zone' => $zone,
        'delivery_attempts' => $status === 'failed_delivery' ? 1 : 0,
        'issue_reason' => $status === 'failed_delivery' ? 'No contesta' : null,
        'created_at' => now()->subHours(6 - $index),
        'updated_at' => $status === 'delivered' ? now()->subMinutes(25) : now()->subHours(5 - $index),
    ]);

    ShipmentEvent::create([
        'shipment_id' => $shipment->id,
        'user_id' => $user->id,
        'status' => 'created',
        'location' => 'Panel web',
        'notes' => 'Guia creada desde cuenta demo.',
        'recorded_at' => $shipment->created_at,
    ]);

    if ($status !== 'created') {
        ShipmentEvent::create([
            'shipment_id' => $shipment->id,
            'user_id' => $user->id,
            'status' => $status,
            'location' => 'Bogota',
            'notes' => 'Movimiento demo.',
            'recorded_at' => $shipment->updated_at,
        ]);
    }
}

echo "Cuenta demo: {$email} / demo12345\n";
'@

$seedPath = Join-Path $Destination "storage\app\seed-tus-envios-demo.php"
Set-Content -LiteralPath $seedPath -Value $seed -Encoding UTF8
& $Php $seedPath
Remove-Item -LiteralPath $seedPath -Force

Write-Host ""
Write-Host "Separacion completa."
Write-Host "Carpeta nueva: $Destination"
Write-Host "Base de datos: $Database"
Write-Host "Para abrirlo:"
Write-Host "cd $Destination"
Write-Host "$Php artisan serve --port=$Port"
Write-Host "URL: http://127.0.0.1:$Port"
Write-Host "Demo: demo@tusenvios.com.co / demo12345"

Pop-Location
