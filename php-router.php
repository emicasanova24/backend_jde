<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$root = __DIR__;

$resolveMimeType = function (string $path): string {
    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $map = [
        'html' => 'text/html; charset=utf-8',
        'htm' => 'text/html; charset=utf-8',
        'js' => 'application/javascript; charset=utf-8',
        'css' => 'text/css; charset=utf-8',
        'json' => 'application/json; charset=utf-8',
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

if (str_starts_with($uri, '/api/')) {
    require $root . '/api/index.php';
    return true;
}

if (str_starts_with($uri, '/assets/')) {
    return $serveStatic($root . $uri);
}

if (str_starts_with($uri, '/logos/')) {
    return $serveStatic($root . '/equipos/logos' . substr($uri, strlen('/logos')));
}

if (str_starts_with($uri, '/sponsors/')) {
    return $serveStatic($root . $uri);
}

if ($uri !== '/' && $serveStatic($root . '/frontend' . $uri)) {
    return true;
}

if ($uri === '/' || $uri === '') {
    return $serveStatic($root . '/frontend/index.html');
}

http_response_code(404);
echo 'Not Found';
return true;