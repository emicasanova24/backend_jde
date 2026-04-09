<?php
namespace App\Middleware;

use App\Jwt;
use App\Request;
use App\Response;

function authMiddleware(Request $req, Response $res): bool {
    $config = require __DIR__ . '/../config.php';
    $authHeader = $req->getHeader('authorization');

    if (!$authHeader) {
        $res->status(401)->json(['error' => 'Token requerido']);
        return false;
    }

    $parts = explode(' ', $authHeader);
    $token = $parts[1] ?? '';

    try {
        $decoded = Jwt::decode($token, $config['jwt_secret']);
        $req->user = $decoded;
        return true;
    } catch (\Exception $e) {
        $res->status(401)->json(['error' => 'Token inválido']);
        return false;
    }
}
