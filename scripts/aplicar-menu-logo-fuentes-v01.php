<?php

declare(strict_types=1);

header('Content-Type: text/plain; charset=UTF-8');

$token = 'MENU-LOGO-FUENTES-V01-20260526';
$providedToken = $_GET['token'] ?? ($argv[1] ?? '');

if ($providedToken !== $token) {
    http_response_code(403);
    exit("Token invalido.\n");
}

$current = __DIR__;
$candidates = [
    $current,
    dirname($current),
    dirname($current, 2),
    dirname($current, 3),
];

$basePath = null;

foreach (array_unique($candidates) as $candidate) {
    if (is_file($candidate . '/artisan') && is_dir($candidate . '/resources/views')) {
        $basePath = $candidate;
        break;
    }
}

if (! $basePath) {
    exit("ERROR: no encontre la raiz Laravel. Sube este archivo dentro de public/scripts, scripts o la raiz del proyecto.\n");
}

echo "Aplicando menu logo compacto + fuentes v01\n";
echo "Proyecto: {$basePath}\n\n";

function replaceOrAppendStyle(string $content, string $marker, string $style): string
{
    $pattern = '/\s*<!-- ' . preg_quote($marker, '/') . '_START -->.*?<!-- ' . preg_quote($marker, '/') . '_END -->/s';
    $content = preg_replace($pattern, '', $content) ?? $content;

    if (str_contains($content, '</nav>')) {
        return str_replace('</nav>', $style . "\n</nav>", $content);
    }

    if (str_contains($content, '</x-app-layout>')) {
        return str_replace('</x-app-layout>', $style . "\n</x-app-layout>", $content);
    }

    return $content . "\n" . $style;
}

function saveFile(string $path, string $content): void
{
    if (! is_file($path)) {
        echo "AVISO: no existe {$path}\n";
        return;
    }

    if (file_put_contents($path, $content) === false) {
        echo "ERROR: no pude guardar {$path}\n";
        return;
    }

    echo "OK: actualizado {$path}\n";
}

function clearCompiledViews(string $path): int
{
    if (! is_dir($path)) {
        return 0;
    }

    $deleted = 0;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getFilename() !== '.gitignore' && @unlink($file->getPathname())) {
            $deleted++;
        }
    }

    return $deleted;
}

$navigationPath = $basePath . '/resources/views/layouts/navigation.blade.php';
$dashboardPath = $basePath . '/resources/views/dashboard.blade.php';
$shipmentsPath = $basePath . '/resources/views/shipments/index.blade.php';

$menuStyle = <<<'HTML'

<!-- TE_MENU_LOGO_FUENTES_V01_START -->
<style>
    @media (min-width: 1024px) {
        aside .te-panel-logo-area,
        aside > div:first-child {
            min-height: 88px !important;
            height: 88px !important;
            padding: 8px 16px !important;
            overflow: hidden !important;
        }

        aside .te-panel-logo-box,
        aside > div:first-child a {
            width: 142px !important;
            height: 50px !important;
            max-width: 142px !important;
            max-height: 50px !important;
        }

        aside .te-panel-logo-img,
        aside > div:first-child img {
            width: auto !important;
            height: auto !important;
            max-width: 142px !important;
            max-height: 50px !important;
            object-fit: contain !important;
            background: transparent !important;
            box-shadow: none !important;
        }
    }

    aside a,
    aside button,
    aside p,
    aside span {
        font-weight: 600;
    }
</style>
<!-- TE_MENU_LOGO_FUENTES_V01_END -->
HTML;

$panelFontStyle = <<<'HTML'

<!-- TE_PANEL_FONT_UNIFORME_V01_START -->
<style>
    main h2,
    main h3 {
        font-weight: 600 !important;
    }

    main [class*="font-black"],
    main [class*="font-bold"] {
        font-weight: 600 !important;
    }

    main .recent-shipments-row p,
    main .shipment-simple-row p {
        font-weight: 400 !important;
    }

    main .recent-shipments-head,
    main .shipment-simple-head {
        font-weight: 600 !important;
    }
</style>
<!-- TE_PANEL_FONT_UNIFORME_V01_END -->
HTML;

if (is_file($navigationPath)) {
    $content = file_get_contents($navigationPath) ?: '';
    $content = replaceOrAppendStyle($content, 'TE_MENU_LOGO_FUENTES_V01', $menuStyle);
    saveFile($navigationPath, $content);
}

foreach ([$dashboardPath, $shipmentsPath] as $path) {
    if (! is_file($path)) {
        echo "AVISO: no existe {$path}\n";
        continue;
    }

    $content = file_get_contents($path) ?: '';
    $content = str_replace(['font-black', 'font-bold'], 'font-semibold', $content);
    $content = preg_replace('/text-3xl\s+font-semibold/', 'text-3xl font-medium', $content) ?? $content;
    $content = replaceOrAppendStyle($content, 'TE_PANEL_FONT_UNIFORME_V01', $panelFontStyle);
    saveFile($path, $content);
}

$deleted = clearCompiledViews($basePath . '/storage/framework/views');

echo "\nOK: vistas compiladas eliminadas: {$deleted}\n";
echo "Listo. Recarga con Ctrl+F5.\n";
echo "Elimina este archivo despues de ejecutarlo.\n";
