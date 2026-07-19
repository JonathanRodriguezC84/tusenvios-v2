<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class DeployController extends Controller
{
    public function __invoke(Request $request, string $key): \Illuminate\Http\Response
    {
        $expectedKey = config('app.deploy_key');

        if (! $expectedKey || $key !== $expectedKey) {
            abort(404);
        }

        $output = [];
        $success = true;

        try {
            Artisan::call('migrate', ['--force' => true]);
            $output[] = 'Migraciones: ' . Artisan::output();
        } catch (\Throwable $e) {
            $output[] = 'Error en migraciones: ' . $e->getMessage();
            $success = false;
        }

        try {
            Artisan::call('optimize:clear');
            $output[] = 'Caché limpiada: ' . Artisan::output();
        } catch (\Throwable $e) {
            $output[] = 'Error limpiando caché: ' . $e->getMessage();
        }

        $status = $success ? 'Completado' : 'Con errores';

        $title = $success ? 'Actualización completada' : 'Actualización con errores';
        $color = $success ? 'emerald' : 'red';

        return <<<HTML
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$title} - Tus Envios</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif; background: #f3f4f6; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 1rem; }
        .card { background: white; border-radius: 12px; padding: 2rem; max-width: 600px; width: 100%; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
        h1 { font-size: 1.5rem; margin-bottom: 1.5rem; }
        h1 span { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 8px; }
        .ok { background: #10b981; }
        .err { background: #ef4444; }
        pre { background: #1f2937; color: #e5e7eb; padding: 1rem; border-radius: 8px; font-size: .8rem; overflow-x: auto; white-space: pre-wrap; word-break: break-word; }
        .btn { display: inline-block; margin-top: 1.5rem; background: #2563eb; color: white; padding: .6rem 1.5rem; border-radius: 8px; text-decoration: none; font-size: .9rem; }
        .btn:hover { background: #1d4ed8; }
    </style>
</head>
<body>
    <div class="card">
        <h1><span class="{$color}"></span> {$title}</h1>
        <p style="margin-bottom:1rem;color:#6b7280;font-size:.9rem;">Estado: <strong>{$status}</strong></p>
        <pre>' . implode("\n", array_map('htmlspecialchars', $output)) . '</pre>
        <a href="/" class="btn">Volver al inicio</a>
    </div>
</body>
</html>
HTML;
    }
}
