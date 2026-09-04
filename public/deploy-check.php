<?php

// Diagnóstico del despliegue - Tus Envios
// Uso: https://tusenvios.com.co/deploy-check.php?k=CLAVE_DEPLOY
// (CLAVE_DEPLOY = el valor de DEPLOY_KEY que está en la línea .env del servidor)

function load_env_key($path)
{
    if (! is_file($path)) {
        return null;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return null;
    }
    foreach ($lines as $line) {
        if (strncmp(trim($line), 'DEPLOY_KEY=', 11) === 0) {
            return trim(substr(trim($line), 11), " \t\"'");
        }
    }

    return null;
}

$base = dirname(__DIR__);
$envFile = $base . '/.env';
$key = load_env_key($envFile);

if (! $key || ! isset($_GET['k']) || $_GET['k'] !== $key) {
    http_response_code(404);
    exit('No encontrado');
}

$logFile = $base . '/storage/logs/laravel.log';

header('Content-Type: text/html; charset=utf-8');
echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Diagnóstico Tus Envios</title>'
    . '<style>body{font-family:system-ui,sans-serif;background:#111827;color:#e5e7eb;margin:0;padding:24px}'
    . 'h1{font-size:1.25rem;margin:0 0 8px}pre{background:#000;padding:14px;border-radius:8px;overflow-x:auto;font-size:.78rem;line-height:1.6;white-space:pre-wrap;word-break:break-word}'
    . '.muted{color:#6b7280;font-size:.85rem}.ok{color:#34d399}.err{color:#f87171}.box{background:#1f2937;border-radius:8px;padding:14px;margin-bottom:16px}</style></head><body>';

echo '<h1>Diagnóstico del servidor</h1>';
echo '<div class="box"><p class="muted">Hora del servidor: ' . date('Y-m-d H:i:s') . ' · PHP: ' . PHP_VERSION . '</p></div>';

$ctl = $base . '/app/Http/Controllers/DeployController.php';
echo '<div class="box">';
echo '<h1>Estado del controlador de despliegue</h1>';
if (is_file($ctl)) {
    echo '<p class="ok">DeployController.php existe</p>';
    $src = @file_get_contents($ctl);
    if ($src !== false) {
        if (strpos($src, '$preContent = implode') !== false) {
            echo '<p class="ok">Contiene el arreglo FINAL (VERSIÓN CORRECTA)</p>';
        } elseif (strpos($src, 'json_encode($line)') !== false) {
            echo '<p class="err">Tiene el arreglo PARCIAL (sigue fallando por el heredoc)</p>';
        } elseif (strpos($src, '$safeOutput = array_map') !== false) {
            echo '<p class="err">Tiene el arreglo ANTERIOR (puede fallar)</p>';
        } else {
            echo '<p class="err">Es la versión VIEJA (la que da ERROR 500)</p>';
        }
        echo '<p class="muted">Tamaño: ' . number_format(filesize($ctl)) . ' bytes · Modificado: ' . date('Y-m-d H:i:s', filemtime($ctl)) . '</p>';
    }
} else {
    echo '<p class="err">¡No existe!</p>';
}
echo '</div>';

echo '<div class="box">';
echo '<h1>Últimas líneas del log</h1>';
if (! is_file($logFile)) {
    echo '<p class="err">No existe el archivo de log: ' . htmlspecialchars($logFile) . '</p>';
} else {
    echo '<p class="muted">Tamaño: ' . number_format(filesize($logFile)) . ' bytes · Última modificación: ' . date('Y-m-d H:i:s', filemtime($logFile)) . '</p>';
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        echo '<p class="err">No se pudo leer el archivo de log.</p>';
    } else {
        $recent = array_slice(array_reverse($lines), 0, 120);
        echo '<pre>';
        foreach ($recent as $line) {
            echo htmlspecialchars($line) . "\n";
        }
        echo '</pre>';
    }
}
echo '</div>';

echo '</body></html>';