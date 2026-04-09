<?php
/**
 * Liga JDE - API PHP
 * Entry point: todas las peticiones /api/* pasan por aquí
 */

require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/Season.php';
require_once __DIR__ . '/src/Jwt.php';
require_once __DIR__ . '/src/Router.php';
require_once __DIR__ . '/src/routes/auth.php';
require_once __DIR__ . '/src/routes/temporadas.php';
require_once __DIR__ . '/src/routes/noticias.php';
require_once __DIR__ . '/src/routes/equipos.php';
require_once __DIR__ . '/src/routes/jugadores.php';
require_once __DIR__ . '/src/routes/partidos.php';
require_once __DIR__ . '/src/routes/eventos.php';
require_once __DIR__ . '/src/routes/tablas.php';
require_once __DIR__ . '/src/routes/semanario.php';
require_once __DIR__ . '/src/seed.php';
require_once __DIR__ . '/src/middleware/auth.php';

use App\Router;
use App\Database;
use App\Request;
use App\Response;

use function App\Middleware\authMiddleware;

// CORS
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$router = new Router();

// Registrar rutas de cada módulo
App\Routes\registerAuthRoutes($router);
App\Routes\registerTemporadasRoutes($router);
App\Routes\registerNoticiasRoutes($router);
App\Routes\registerEquiposRoutes($router);
App\Routes\registerJugadoresRoutes($router);
App\Routes\registerPartidosRoutes($router);
App\Routes\registerEventosRoutes($router);
App\Routes\registerTablasRoutes($router);
App\Routes\registerSemanarioRoutes($router);

// Seed DB
$router->post('/setup/seed', function (Request $req, Response $res) {
    return authMiddleware($req, $res);
}, function (Request $req, Response $res) {
    try {
        App\seedDatabase();
        $res->json(['ok' => true, 'message' => 'Seed realizado']);
    } catch (\Exception $e) {
        error_log('Error en seed: ' . $e->getMessage());
        $res->status(500)->json(['ok' => false, 'message' => 'Error en seed']);
    }
});

// Clear DB
$router->post('/setup/clear', function (Request $req, Response $res) {
    return authMiddleware($req, $res);
}, function (Request $req, Response $res) {
    try {
        Database::execute("DELETE FROM noticias_relacionadas");
        Database::execute("DELETE FROM noticias");
        Database::execute("DELETE FROM eventos");
        Database::execute("DELETE FROM partidos");
        Database::execute("DELETE FROM jugadores");
        Database::execute("DELETE FROM equipos");
        Database::resetAutoIncrement('noticias');
        Database::resetAutoIncrement('eventos');
        Database::resetAutoIncrement('partidos');
        Database::resetAutoIncrement('jugadores');
        Database::resetAutoIncrement('equipos');
        $res->json(['ok' => true, 'message' => 'Base de datos limpiada']);
    } catch (\Exception $e) {
        error_log('Error en /setup/clear: ' . $e->getMessage());
        $res->status(500)->json(['ok' => false, 'message' => 'Error al limpiar DB']);
    }
});

// Health check
$router->get('/health', function (Request $req, Response $res) {
    $res->json(['ok' => true]);
});

// Test protegido
$router->get('/test-protegido', function (Request $req, Response $res) {
    return authMiddleware($req, $res);
}, function (Request $req, Response $res) {
    $res->json(['mensaje' => 'Acceso autorizado', 'usuario' => $req->user]);
});

// Dispatch
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
