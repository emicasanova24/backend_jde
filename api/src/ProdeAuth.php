<?php
/**
 * ProdeAuth.php — Helper de autenticación compartido entre prode y prode-mundial.
 * Namespace App para que sea accesible desde ambos módulos de rutas.
 */
namespace App;

use App\Database;
use App\Request;
use App\Response;

use function App\ensureProdeTables;

require_once __DIR__ . '/seed.php';

/**
 * Middleware de autenticación del Prode.
 * Lee header X-Prode-Token y setea $req->prodeUser si es válido.
 * Devuelve true si la auth es válida, false si ya envió respuesta de error.
 */
function prodeAuthMiddleware(Request $req, Response $res): bool
{
    ensureProdeTables();
    $token = $req->getHeader('x-prode-token');
    if (!$token) {
        $res->status(401)->json(['error' => 'Token de prode requerido']);
        return false;
    }

    $sesion = Database::fetchOne(
        "SELECT s.id, s.participante_id, s.expira_at, p.alias
         FROM prode_sesiones s
         JOIN prode_participantes p ON p.id = s.participante_id
         WHERE s.token = ? AND s.expira_at > NOW()",
        [$token]
    );

    if (!$sesion) {
        $res->status(401)->json(['error' => 'Sesión inválida o expirada']);
        return false;
    }

    $req->prodeUser = (object) [
        'id'    => (int) $sesion['participante_id'],
        'alias' => $sesion['alias'],
        'token' => $token,
    ];

    return true;
}
