<?php

$secret = '8a7966e26e7da7053e5fbfb004ab25ef';

if (! isset($_GET['key']) || $_GET['key'] !== $secret) {
    http_response_code(404);
    echo 'No encontrado';
    exit;
}

define('LARAVEL_START', microtime(true));

require __DIR__.'/../vendor/autoload.php';

/** @var \Illuminate\Foundation\Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$output = [];
$success = true;

try {
    $exitCode = \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    $output[] = 'Migraciones: ' . \Illuminate\Support\Facades\Artisan::output();
    if ($exitCode !== 0) {
        $success = false;
    }
} catch (\Throwable $e) {
    $output[] = 'Error en migraciones: ' . $e->getMessage();
    $success = false;
}

try {
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    $output[] = 'Cache: ' . \Illuminate\Support\Facades\Artisan::output();
} catch (\Throwable $e) {
    $output[] = 'Error limpiando cache: ' . $e->getMessage();
}

$title = $success ? 'Actualizacion completada' : 'Actualizacion con errores';
$color = $success ? '#10b981' : '#ef4444';

echo '<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>'.$title.' - Tus Envios</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",system-ui,sans-serif;background:#f3f4f6;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:1rem}
        .card{background:white;border-radius:12px;padding:2rem;max-width:600px;width:100%;box-shadow:0 1px 3px rgba(0,0,0,.1)}
        h1{font-size:1.5rem;margin-bottom:1.5rem}
        .dot{display:inline-block;width:10px;height:10px;border-radius:50%;margin-right:8px}
        pre{background:#1f2937;color:#e5e7eb;padding:1rem;border-radius:8px;font-size:.8rem;overflow-x:auto;white-space:pre-wrap;word-break:break-word}
        .btn{display:inline-block;margin-top:1.5rem;background:#2563eb;color:white;padding:.6rem 1.5rem;border-radius:8px;text-decoration:none;font-size:.9rem}
        .btn:hover{background:#1d4ed8}
    </style>
</head>
<body>
    <div class="card">
        <h1><span class="dot" style="background:'.$color.'"></span> '.$title.'</h1>
        <pre>'.implode("\n", array_map('htmlspecialchars', $output)).'</pre>
        <a href="/" class="btn">Volver al inicio</a>
    </div>
</body>
</html>';
