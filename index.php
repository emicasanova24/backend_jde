<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$root = __DIR__;

require_once $root . '/api/src/PublicNews.php';

// // --- Debug log ---
// $logFile = $root . '/debug-router.log';
// $logData = date('Y-m-d H:i:s') . ' | '
//     . 'METHOD=' . ($_SERVER['REQUEST_METHOD'] ?? '?') . ' | '
//     . 'REQUEST_URI=' . ($_SERVER['REQUEST_URI'] ?? '?') . ' | '
//     . 'PARSED_URI=' . $uri . ' | '
//     . 'SCRIPT_NAME=' . ($_SERVER['SCRIPT_NAME'] ?? '?') . ' | '
//     . 'DOCUMENT_ROOT=' . ($_SERVER['DOCUMENT_ROOT'] ?? '?') . ' | '
//     . '__DIR__=' . __DIR__ . ' | '
//     . 'VIEW_EXISTS=' . (isset(['/' => 'views/home.php'][$uri]) && is_file($root . '/views/home.php') ? 'yes' : 'no') . ' | '
//     . 'PHP_SAPI=' . PHP_SAPI . "\n";
// file_put_contents($logFile, $logData, FILE_APPEND);

$resolveMimeType = function (string $path): string {
    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $map = [
        'html' => 'text/html; charset=utf-8',
        'htm' => 'text/html; charset=utf-8',
        'js' => 'application/javascript; charset=utf-8',
        'css' => 'text/css; charset=utf-8',
        'json' => 'application/json; charset=utf-8',
        'webmanifest' => 'application/manifest+json; charset=utf-8',
        'txt' => 'text/plain; charset=utf-8',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'svg' => 'image/svg+xml',
        'webp' => 'image/webp',
        'gif' => 'image/gif',
        'ico' => 'image/x-icon',
    ];

    if (isset($map[$extension])) {
        return $map[$extension];
    }

    if (function_exists('mime_content_type')) {
        $detected = mime_content_type($path);
        if (is_string($detected) && $detected !== '') {
            return $detected;
        }
    }

    return 'application/octet-stream';
};

$serveStatic = function (string $path) use ($resolveMimeType): bool {
    if (!is_file($path)) {
        return false;
    }

    $mime = $resolveMimeType($path);
    header('Content-Type: ' . $mime);
    readfile($path);
    return true;
};

$rootStaticAliases = [
    '/favicon.ico' => '/assets/favicon.ico',
    '/apple-touch-icon.png' => '/assets/apple-touch-icon.png',
    '/site.webmanifest' => '/assets/site.webmanifest',
];

// --- API ---
if (str_starts_with($uri, '/api/')) {
    require $root . '/api/index.php';
    exit;
}

if (isset($rootStaticAliases[$uri])) {
    $serveStatic($root . $rootStaticAliases[$uri]);
    exit;
}

// --- Assets estáticos ---
if (str_starts_with($uri, '/assets/')) {
    $serveStatic($root . $uri);
    exit;
}

if (str_starts_with($uri, '/assets-equipos/')) {
    $serveStatic($root . $uri);
    exit;
}

if (str_starts_with($uri, '/logos/')) {
    $serveStatic($root . '/assets-equipos/logos' . substr($uri, strlen('/logos')));
    exit;
}

if (str_starts_with($uri, '/sponsors/')) {
    $serveStatic($root . $uri);
    exit;
}

if (str_starts_with($uri, '/uploads/')) {
    $serveStatic($root . $uri);
    exit;
}

// JS del frontend (servidos como estáticos)
if (preg_match('#^/(app|home|carga|tailwind-config|prode|prode-mundial)\.js$#', $uri)) {
    $serveStatic($root . '/frontend' . $uri);
    exit;
}

// Archivos estáticos del directorio /frontend/ (js, css, etc.)
if (str_starts_with($uri, '/frontend/')) {
    $serveStatic($root . $uri);
    exit;
}

// --- Redirect trailing slash ---
if ($uri !== '/' && str_ends_with($uri, '/')) {
    header('Location: ' . rtrim($uri, '/'), true, 301);
    exit;
}

// --- Redirect .html antiguo → URL limpia ---
$htmlRedirects = [
    '/index.html'        => '/',
    '/login.html'        => '/login',
    '/dashboard.html'    => '/dashboard',
    '/equipos.html'      => '/equipos',
    '/jugadores.html'    => '/jugadores',
    '/partidos.html'     => '/partidos',
    '/carga.html'        => '/carga',
    '/account.html'      => '/account',
    '/estadisticas.html' => '/estadisticas',
    '/fixture.html'      => '/fixture',
    '/noticias.html'     => '/noticias',
];

if (isset($htmlRedirects[$uri])) {
    header('Location: ' . $htmlRedirects[$uri], true, 301);
    exit;
}

// --- Rutas dinámicas ---
if (preg_match('#^/og/noticias/([a-z0-9][a-z0-9-]*)$#', $uri, $m)) {
    App\streamNoticiaOgImage($m[1], $root);
    exit;
}

if (preg_match('#^/noticias/([a-z0-9][a-z0-9-]*)$#', $uri, $m)) {
    $notaSlug = $m[1];
    $nota = App\fetchPublicNoticiaBySlug($notaSlug);
    header('Content-Type: text/html; charset=utf-8');
    if (!$nota) {
        http_response_code(404);
    }
    require $root . '/views/nota.php';
    exit;
}

// --- Rutas web → vistas PHP ---
$webRoutes = [
    '/'                 => 'views/home.php',
    '/login'            => 'views/login.php',
    '/dashboard'        => 'views/dashboard.php',
    '/equipos'          => 'views/equipos.php',
    '/jugadores'        => 'views/jugadores.php',
    '/partidos'         => 'views/partidos.php',
    '/carga'            => 'views/carga.php',
    '/account'          => 'views/account.php',
    '/estadisticas'     => 'views/estadisticas.php',
    '/fixture'          => 'views/fixture.php',
    '/noticias'         => 'views/noticias-admin.php',
    '/notas'            => 'views/notas.php',
    '/sidebar'          => 'views/sidebar.php',
    '/embed/tabla'      => 'views/embed/tabla.php',
    '/embed/generador'  => 'views/embed/generador.php',
    '/publicidad'       => 'views/publicidad.php',
    '/prode'            => 'views/prode.php',
    '/prode-mundial'    => 'views/prode-mundial.php',
    '/prode-admin'      => 'views/prode-admin.php',
    '/banners'          => 'views/banners.php',
    '/politica-de-privacidad' => 'views/politica-privacidad.php',
    '/eliminacion-de-datos'   => 'views/eliminacion-datos.php',
];

if (isset($webRoutes[$uri])) {
    $viewFile = $root . '/' . $webRoutes[$uri];
    if (is_file($viewFile)) {
        header('Content-Type: text/html; charset=utf-8');
        require $viewFile;
        exit;
    }
}

// --- 404 ---
http_response_code(404);
echo 'Not Found';
