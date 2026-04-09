<?php
// Carga de configuración desde .env o variables de entorno del sistema
if (!function_exists('env')) {
    function env(string $key, string $default = ''): string {
        static $vars = null;
        if ($vars === null) {
            $vars = [];
            $envFiles = [
                __DIR__ . '/../.env',
                __DIR__ . '/../../.env',
            ];
            foreach ($envFiles as $envFile) {
                if (!file_exists($envFile)) {
                    continue;
                }
                foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                    if (str_starts_with(trim($line), '#')) continue;
                    if (!str_contains($line, '=')) continue;
                    [$k, $v] = explode('=', $line, 2);
                    $vars[trim($k)] = trim($v);
                }
                break;
            }
        }
        return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?: ($vars[$key] ?? $default);
    }
}

return [
    'db_host'     => env('DB_HOST', 'localhost'),
    'db_port'     => env('DB_PORT', '3306'),
    'db_name'     => env('DB_NAME', 'liga_jde'),
    'db_user'     => env('DB_USER', 'root'),
    'db_password' => env('DB_PASSWORD', ''),
    'db_ssl'      => env('DB_SSL', 'false') === 'true',
    'jwt_secret'  => env('JWT_SECRET', ''),
    'admin_email' => env('ADMIN_EMAIL', ''),
    'admin_password' => env('ADMIN_PASSWORD', ''),
    'api_url'     => env('API_URL', ''),
];
