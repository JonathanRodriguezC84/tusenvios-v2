<?php

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

// Buscar usuario admin
$admin = \App\Models\User::where('email', 'admin@tusenvios.com.co')->first();

if (! $admin) {
    $admin = \App\Models\User::where('email', 'emprendedor@tusenvios.com.co')->first();
    if ($admin) {
        $admin->update(['role' => 'superadmin', 'name' => 'Administrador']);
        echo "Usuario emprendedor actualizado a superadmin.<br>";
    } else {
        echo "No se encontró ningún usuario.<br>";
    }
} else {
    $admin->update(['role' => 'superadmin']);
    echo "Usuario admin actualizado a superadmin.<br>";
}

// Listar usuarios
$users = \App\Models\User::all(['id', 'name', 'email', 'role']);
echo "<br><b>Usuarios actuales:</b><br>";
foreach ($users as $u) {
    echo "#{$u->id} {$u->name} &lt;{$u->email}&gt; rol={$u->role}<br>";
}

echo "<br><a href='/admin'>Ir al panel admin</a>";
