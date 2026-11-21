<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class DeployController extends Controller
{
    public function __invoke(Request $request, string $key): \Illuminate\Http\Response
    {
        $this->requireKey($key);

        $lines = [];
        $success = true;

        $lines[] = 'Migraciones:';
        foreach ($this->runMigrationsIndividually() as $line) {
            $lines[] = '  ' . $line;

            if (str_contains($line, 'ERROR ')) {
                $success = false;
            }
        }

        try {
            Artisan::call('optimize:clear');
            $lines[] = 'Caché limpiada: ' . $this->toText(Artisan::output());
        } catch (\Throwable $e) {
            $lines[] = 'Error limpiando caché: ' . $e->getMessage();
        }

        $status = $success ? 'Completado' : 'Con errores';
        $title = $success ? 'Actualización completada' : 'Actualización con errores';
        $color = $success ? 'emerald' : 'red';

        $preContent = implode("\n", array_map([$this, 'toText'], $lines));
        $preContent = trim($preContent) !== '' ? htmlspecialchars($preContent, ENT_QUOTES, 'UTF-8') : '(sin salida)';

        return $this->htmlPage($title, $status, $color, $preContent);
    }

    public function log(Request $request, string $key): \Illuminate\Http\Response
    {
        $this->requireKey($key);

        $candidates = [
            storage_path('logs/laravel.log'),
            base_path('privado/storage/logs/laravel.log'),
            base_path('../storage/logs/laravel.log'),
        ];

        $logFile = null;
        foreach ($candidates as $path) {
            if (file_exists($path)) {
                $logFile = $path;
                break;
            }
        }

        $lines = ['Rutas probadas:'];

        foreach ($candidates as $path) {
            $lines[] = '  - ' . $path . ($path === $logFile ? '  << AQUÍ' : (file_exists($path) ? '  (existe)' : ''));
        }

        if ($logFile) {
            $size = filesize($logFile);
            $handle = fopen($logFile, 'r');
            fseek($handle, max(0, $size - 30000));
            $tail = fread($handle, 30000);
            fclose($handle);

            $lines[] = '';
            $lines[] = 'Últimas líneas del log (' . $size . ' bytes):';
            $lines[] = '';
            $lines[] = $tail;

            $title = 'Log de errores';
            $preContent = htmlspecialchars(implode("\n", $lines), ENT_QUOTES, 'UTF-8');

            return $this->htmlPage($title, 'Log', 'blue', $preContent, $logFile);
        }

        $title = 'Log no encontrado';
        $preContent = htmlspecialchars(implode("\n", $lines), ENT_QUOTES, 'UTF-8');

        return $this->htmlPage($title, 'Sin log', 'red', $preContent);
    }

    public function checkView(Request $request, string $key): \Illuminate\Http\Response
    {
        $this->requireKey($key);

        $requested = (string) $request->query('path', 'recipients/index');
        $requested = ltrim($requested, '/');

        if (! preg_match('#^[a-z0-9\-_\./]+$#i', $requested)) {
            return $this->htmlPage('Ruta invalida', 'Error', 'red', 'La ruta solo puede contener letras, numeros, guiones, puntos y slash.');
        }

        if (! preg_match('#\.(blade\.php|php|css|js|json|md|txt|xml|yml|yaml|sql)$#i', $requested)) {
            $requested .= '.blade.php';
        }

        $candidates = [];

        $root = explode('/', $requested)[0];

        if (in_array($root, ['app', 'routes', 'database', 'resources', 'bootstrap', 'public', 'tests'], true)) {
            if (str_contains($requested, '..')) {
                return $this->htmlPage('Ruta invalida', 'Error', 'red', 'La ruta no puede contener "..".');
            }

            $candidates = [
                base_path($requested),
            ];
        } else {
            $candidates = [
                resource_path('views/' . $requested),
                base_path('resources/views/' . $requested),
            ];
        }

        $lines = ['Buscando: ' . $requested, ''];

        foreach ($candidates as $path) {
            if (! file_exists($path)) {
                $lines[] = 'NO EXISTE: ' . $path;
                continue;
            }
            $lines[] = 'EXISTE: ' . $path . ' (' . filesize($path) . ' bytes)';
            $lines[] = 'mtime: ' . date('Y-m-d H:i:s', filemtime($path));
            $lines[] = '';

            $src = file_get_contents($path);
            $srcLines = explode("\n", str_replace(["\r\n", "\r"], "\n", $src));
            foreach ($srcLines as $i => $line) {
                $lines[] = sprintf('%4d| %s', $i + 1, $line);
            }
        }

        $preContent = htmlspecialchars(implode("\n", $lines), ENT_QUOTES, 'UTF-8');

        return $this->htmlPage('Archivo en servidor: ' . $requested, 'Archivo', 'blue', $preContent);
    }

    public function listFiles(Request $request, string $key): \Illuminate\Http\Response
    {
        $this->requireKey($key);

        $requested = trim((string) $request->query('path', ''), '/');
        $requested = str_replace('\\', '/', $requested);

        if ($requested !== '' && ! preg_match('#^[a-z0-9\-_\./]+$#i', $requested)) {
            return $this->htmlPage('Ruta invalida', 'Error', 'red', 'La ruta solo puede contener letras, numeros, guiones, puntos y slash.');
        }

        $absolute = $this->resolveCheckedPath($requested);

        if ($absolute === null) {
            return $this->htmlPage(
                'Ruta no permitida',
                'Error',
                'red',
                'Solo se pueden explorar estas carpetas: ' . implode(', ', $this->allowedRoots()) . "\n" .
                'Archivos sueltos permitidos: ' . implode(', ', $this->allowedRootFiles())
            );
        }

        if (is_file($absolute)) {
            if (! is_readable($absolute)) {
                return $this->htmlPage('Sin permiso', 'Error', 'red', 'No se puede leer: ' . $absolute);
            }

            $src = file_get_contents($absolute);
            $srcLines = explode("\n", str_replace(["\r\n", "\r"], "\n", (string) $src));

            $lines = [
                'Archivo: ' . $requested,
                'Ruta real: ' . $absolute,
                'Tamaño: ' . filesize($absolute) . ' bytes',
                'mtime: ' . date('Y-m-d H:i:s', filemtime($absolute)),
                '',
            ];

            foreach ($srcLines as $i => $line) {
                $lines[] = sprintf('%4d| %s', $i + 1, $line);
            }

            return $this->htmlPage('Archivo: ' . $requested, 'Archivo', 'blue', htmlspecialchars(implode("\n", $lines), ENT_QUOTES, 'UTF-8'));
        }

        if (! is_dir($absolute)) {
            return $this->htmlPage('No existe', 'Error', 'red', 'No existe la ruta: ' . $requested . "\nRuta real: " . $absolute);
        }

        $entries = @scandir($absolute) ?: [];
        $dirs = [];
        $files = [];

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $full = rtrim($absolute, '/\\') . DIRECTORY_SEPARATOR . $entry;
            $rel = ltrim($requested . '/' . $entry, '/');

            if (is_dir($full)) {
                $dirs[] = ['name' => $entry, 'rel' => $rel];
            } else {
                $files[] = [
                    'name' => $entry,
                    'rel' => $rel,
                    'size' => @filesize($full) ?: 0,
                    'mtime' => @filemtime($full) ?: 0,
                ];
            }
        }

        usort($dirs, fn ($a, $b) => strcasecmp($a['name'], $b['name']));
        usort($files, fn ($a, $b) => strcasecmp($a['name'], $b['name']));

        $base = url('/deploy/' . $key . '/list');
        $link = fn (string $path, string $label) => '<a href="' . htmlspecialchars($base . '?path=' . rawurlencode($path), ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</a>';

        $crumbs = [$link('', 'raíz')];
        $accum = '';
        foreach (array_filter(explode('/', $requested)) as $segment) {
            $accum = ltrim($accum . '/' . $segment, '/');
            $crumbs[] = $link($accum, $segment);
        }

        $html = '<p style="margin-bottom:1rem;color:#374151;font-size:.9rem;">'
            . 'Ruta real: <code>' . htmlspecialchars($absolute, ENT_QUOTES, 'UTF-8') . '</code><br>'
            . 'Ubicación: ' . implode(' / ', $crumbs)
            . '</p>';

        $html .= '<table style="width:100%;border-collapse:collapse;font-size:.85rem;">';
        $html .= '<thead><tr style="text-align:left;border-bottom:2px solid #e5e7eb;">'
            . '<th style="padding:.4rem;">Nombre</th><th style="padding:.4rem;">Tamaño</th><th style="padding:.4rem;">Modificado</th>'
            . '</tr></thead><tbody>';

        if ($requested !== '') {
            $parent = dirname($requested);
            $parent = $parent === '.' ? '' : $parent;
            $html .= '<tr><td style="padding:.35rem;" colspan="3">' . $link($parent, '⬑ .. (subir)') . '</td></tr>';
        }

        foreach ($dirs as $dir) {
            $html .= '<tr style="border-bottom:1px solid #f3f4f6;">'
                . '<td style="padding:.35rem;font-weight:700;">📁 ' . $link($dir['rel'], $dir['name'] . '/') . '</td>'
                . '<td style="padding:.35rem;color:#6b7280;">—</td>'
                . '<td style="padding:.35rem;color:#6b7280;">—</td>'
                . '</tr>';
        }

        foreach ($files as $file) {
            $html .= '<tr style="border-bottom:1px solid #f3f4f6;">'
                . '<td style="padding:.35rem;">📄 ' . $link($file['rel'], $file['name']) . '</td>'
                . '<td style="padding:.35rem;color:#6b7280;">' . number_format($file['size']) . ' B</td>'
                . '<td style="padding:.35rem;color:#6b7280;">' . ($file['mtime'] ? date('Y-m-d H:i', $file['mtime']) : '—') . '</td>'
                . '</tr>';
        }

        if (! $dirs && ! $files) {
            $html .= '<tr><td colspan="3" style="padding:.6rem;color:#6b7280;">(carpeta vacía)</td></tr>';
        }

        $html .= '</tbody></table>';

        return $this->htmlPage('Explorador: /' . $requested, count($dirs) . ' carpetas, ' . count($files) . ' archivos', 'blue', '', '', $html);
    }

    public function checkData(Request $request, string $key): \Illuminate\Http\Response
    {
        $this->requireKey($key);

        $lines = [];

        try {
            $shipments = \App\Models\Shipment::query()
                ->latest()
                ->take(5)
                ->get();
            $lines[] = 'ÚLTIMAS 5 GUÍAS:';
            foreach ($shipments as $s) {
                $lines[] = sprintf(
                    '  id=%s | guia="%s" | status="%s" | snapshot=%s | created=%s',
                    $s->id,
                    $s->guide_number,
                    $s->status,
                    json_encode($s->inventory_snapshot),
                    $s->created_at
                );
            }
            $lines[] = '';
        } catch (\Throwable $e) {
            $lines[] = 'Error consultando guias: ' . $e->getMessage();
            $lines[] = '';
        }

        try {
            $qps = \App\Models\QuickProduct::query()
                ->latest()
                ->take(5)
                ->get();
            $lines[] = 'ÚLTIMOS 5 PRODUCTOS RÁPIDOS:';
            foreach ($qps as $qp) {
                $lines[] = sprintf(
                    '  id=%s | name="%s" | stock=%s | status="%s" | updated=%s',
                    $qp->id,
                    $qp->name,
                    $qp->stock,
                    $qp->status,
                    $qp->updated_at
                );
            }
            $lines[] = '';
        } catch (\Throwable $e) {
            $lines[] = 'Error consultando productos rapidos: ' . $e->getMessage();
            $lines[] = '';
        }

        try {
            $migrationFile = database_path('migrations/2026_06_16_000000_add_lastname_to_frequent_recipients.php');
            $migrationNueva = database_path('migrations/2026_08_17_000003_backfill_lastname_to_frequent_recipients.php');
            $lines[] = 'MIGRACIÓN lastname:';
            $lines[] = '  Archivo 2026_06_16 (vieja) en servidor: ' . (file_exists($migrationFile) ? 'SÍ' : 'NO EXISTE');
            $lines[] = '  Archivo 2026_08_17 (nueva) en servidor: ' . (file_exists($migrationNueva) ? 'SÍ' : 'NO EXISTE');
            $lines[] = '';

            $total = \Illuminate\Support\Facades\DB::table('frequent_recipients')->count();
            $lines[] = "Total registros clientes frecuentes: {$total}";
        } catch (\Throwable $e) {
            $lines[] = 'Error: ' . $e->getMessage();
        }

        $preContent = htmlspecialchars(implode("\n", $lines), ENT_QUOTES, 'UTF-8');
        return $this->htmlPage('Datos de Diagnóstico', 'Base de datos', 'blue', $preContent);
    }

    protected function allowedRoots(): array
    {
        return ['app', 'routes', 'database', 'resources', 'bootstrap', 'public', 'tests', 'storage/logs'];
    }

    protected function allowedRootFiles(): array
    {
        return ['composer.json', 'package.json', 'artisan', 'tailwind.config.js', 'vite.config.js', 'postcss.config.js'];
    }

    protected function resolveCheckedPath(string $requested): ?string
    {
        $requested = trim($requested, '/');

        if ($requested === '') {
            return base_path();
        }

        if (str_contains($requested, '..')) {
            return null;
        }

        foreach ($this->allowedRoots() as $allowedRoot) {
            if ($requested === $allowedRoot || str_starts_with($requested, $allowedRoot . '/')) {
                return base_path($requested);
            }
        }

        if (in_array($requested, $this->allowedRootFiles(), true)) {
            return base_path($requested);
        }

        return null;
    }

    protected function requireKey(string $key): void
    {
        $expectedKey = config('app.deploy_key');

        if (! $expectedKey || $key !== $expectedKey) {
            abort(404);
        }
    }

    protected function htmlPage(string $title, string $status, string $color, string $preContent, string $note = '', string $extraHtml = ''): \Illuminate\Http\Response
    {
        $preContent = trim($preContent) !== '' ? $preContent : '';
        $breadcrumb = $note !== '' ? '<p style="margin-bottom:1rem;color:#6b7280;font-size:.9rem;">Archivo: <strong>' . htmlspecialchars($note, ENT_QUOTES, 'UTF-8') . '</strong></p>' : '';
        $preBlock = $preContent !== '' ? '<pre>' . $preContent . '</pre>' : '';
        $wideClass = $extraHtml !== '' ? ' wide' : '';

        return response(
            <<<HTML
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$title} - Tus Envios</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif; background: #f3f4f6; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 1rem; }
        .card { background: white; border-radius: 12px; padding: 2rem; max-width: 800px; width: 100%; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
        .card.wide { max-width: 1100px; }
        h1 { font-size: 1.5rem; margin-bottom: 1.5rem; }
        h1 span { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 8px; }
        .ok { background: #10b981; }
        .err { background: #ef4444; }
        .blue { background: #2563eb; }
        pre { background: #1f2937; color: #e5e7eb; padding: 1rem; border-radius: 8px; font-size: .8rem; overflow-x: auto; white-space: pre-wrap; word-break: break-word; max-height: 420px; overflow-y: auto; }
        .btn { display: inline-block; margin-top: 1.5rem; background: #2563eb; color: white; padding: .6rem 1.5rem; border-radius: 8px; text-decoration: none; font-size: .9rem; }
        .btn:hover { background: #1d4ed8; }
    </style>
</head>
<body>
    <div class="card{$wideClass}">
        <h1><span class="{$color}"></span> {$title}</h1>
        <p style="margin-bottom:1rem;color:#6b7280;font-size:.9rem;">Estado: <strong>{$status}</strong></p>
        {$breadcrumb}
        {$extraHtml}
        {$preBlock}
        <a href="/" class="btn">Volver al inicio</a>
    </div>
</body>
</html>
HTML
        );
    }

    protected function toText(mixed $value): string
    {
        if (is_array($value) || is_object($value)) {
            $json = json_encode($value);

            return $json !== false ? $json : print_r($value, true);
        }

        return (string) $value;
    }

    protected function runMigrationsIndividually(): array
    {
        $migrator = app('migrator');
        $repository = $migrator->getRepository();
        $results = [];

        if (! $repository->repositoryExists()) {
            $repository->createRepository();
        }

        $ran = $repository->getRan();

        $migrationPaths = array_merge($migrator->paths(), [database_path('migrations')]);

        foreach ($migrator->getMigrationFiles($migrationPaths) as $name => $file) {
            if (in_array($name, $ran, true)) {
                continue;
            }

            $filePath = is_array($file) ? ($file['path'] ?? $file['path0']) : $file;
            $relative = ltrim(str_replace('\\', '/', substr($filePath, strlen(base_path()))), '/');

            try {
                Artisan::call('migrate', ['--force' => true, '--path' => $relative]);
                $results[] = 'OK ' . $name;
            } catch (\Throwable $e) {
                $message = $e->getMessage();

                if (
                    str_contains($message, 'already exists')
                    || str_contains($message, 'already has a column')
                    || str_contains($message, 'Duplicate column name')
                    || str_contains($message, '42S01')
                    || str_contains($message, '42S21')
                    || str_contains($message, '(1050)')
                    || str_contains($message, '(1060)')
                ) {
                    $repository->log($name, $repository->getNextBatchNumber());
                    $results[] = 'YA EXISTÍA (registrada como aplicada) ' . $name;
                } else {
                    $results[] = 'ERROR ' . $name . ': ' . $message;
                }
            }
        }

        if (count(array_filter($results, fn ($r) => str_contains($r, 'ERROR '))) === 0) {
            $results[] = 'Todas las migraciones pendientes quedaron aplicadas o registradas.';
        }

        return $results;
    }
}