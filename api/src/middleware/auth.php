<?php
namespace App\Middleware;

use App\Jwt;
use App\Request;
use App\Response;

function extractAuthToken(Request $req): ?string {
    $candidates = [
        $req->getHeader('authorization'),
        $req->getHeader('x-auth-token'),
        $_SERVER['HTTP_X_AUTH_TOKEN'] ?? null,
    ];

    foreach ($candidates as $candidate) {
        $value = trim((string) $candidate);
        if ($value === '') {
            continue;
        }

        if (stripos($value, 'Bearer ') === 0) {
            $value = trim(substr($value, 7));
        }

        if ($value !== '') {
            return $value;
        }
    }

    return null;
}

function authMiddleware(Request $req, Response $res): bool {
    $config = require __DIR__ . '/../config.php';
    $token = extractAuthToken($req);

    if (!$token) {
        // Debug: log all headers to find the issue
        $logFile = dirname(__DIR__, 2) . '/debug-auth.log';
        $logData = date('Y-m-d H:i:s') . ' | '
            . 'URI=' . ($_SERVER['REQUEST_URI'] ?? '?') . ' | '
            . 'HEADERS=' . json_encode($req->headers) . ' | '
            . 'SERVER_AUTH=' . ($_SERVER['HTTP_AUTHORIZATION'] ?? 'null') . ' | '
            . 'REDIRECT_AUTH=' . ($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? 'null') . ' | '
            . 'X_AUTH=' . ($_SERVER['HTTP_X_AUTH_TOKEN'] ?? 'null') . "\n";
        file_put_contents($logFile, $logData, FILE_APPEND);

        $res->status(401)->json(['error' => 'Token requerido']);
        return false;
    }

    try {
        $decoded = Jwt::decode($token, $config['jwt_secret']);
        $req->user = $decoded;
        return true;
    } catch (\Exception $e) {
        $res->status(401)->json(['error' => 'Token inválido']);
        return false;
    }
}
