<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$secret = '8a7966e26e7da7053e5fbfb004ab25ef';

if (! isset($_GET['key']) || $_GET['key'] !== $secret) {
    http_response_code(404);
    echo 'No encontrado';
    exit;
}

define('LARAVEL_START', microtime(true));

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $admin = \App\Models\User::where('email', 'admin@tusenvios.com.co')->first();

    if (! $admin) {
        $admin = new \App\Models\User();
        $admin->email = 'admin@tusenvios.com.co';
        $admin->name = 'Administrador';
        $admin->password = 'password';
        $admin->role = 'superadmin';
        $admin->status = 'active';
        $admin->email_verified_at = now();
        $admin->save();
        echo "Usuario admin CREADO.<br>";
    } else {
        $admin->password = 'password';
        $admin->role = 'superadmin';
        $admin->status = 'active';
        $admin->save();
        echo "Usuario admin ACTUALIZADO.<br>";
    }

    // Quitar tenant para que no lo bloquee el plan
    if ($admin->tenant_id) {
        $admin->tenant_id = null;
        $admin->save();
        echo "Tenant removido del admin para evitar bloqueo.<br>";
    }

    if (\Illuminate\Support\Facades\Hash::check('password', $admin->password)) {
        echo "Contrasena CORRECTA.<br>";
    } else {
        echo "ERROR: contrasena no coincide.<br>";
    }

    echo "<br><b>Usuarios:</b><br>";
    $users = \App\Models\User::all(['id', 'name', 'email', 'role', 'tenant_id']);
    foreach ($users as $u) {
        echo "#{$u->id} {$u->name} &lt;{$u->email}&gt; rol={$u->role} tenant=" . ($u->tenant_id ?? 'ninguno') . "<br>";
    }

    echo "<br><a href='/login'>Ir al login</a>";

} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "<br>";
    echo nl2br($e->getTraceAsString());
}
