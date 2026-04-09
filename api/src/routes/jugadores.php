<?php
namespace App\Routes;

use App\Router;
use App\Database;
use App\Request;
use App\Response;

use function App\Middleware\authMiddleware;
use function App\resolveSeasonId;

require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../Season.php';

function registerJugadoresRoutes(Router $router): void {
    $router->group('/jugadores', function (Router $r) {

        $r->get('', function (Request $req, Response $res) {
            try {
                $seasonId = resolveSeasonId($req->query['temporada'] ?? null);
                $params = [$seasonId];
                $where = ' WHERE j.temporada_id = ?';
                if (!empty($req->query['equipo'])) {
                    $params[] = (int)$req->query['equipo'];
                    $where .= ' AND j.equipo = ?';
                }
                if (!empty($req->query['estado'])) {
                    $params[] = $req->query['estado'];
                    $where .= ' AND LOWER(j.estado) = LOWER(?)';
                }
                $rows = Database::fetchAll(
                    "SELECT j.id, j.nombre, j.posicion, j.equipo, j.temporada_id AS temporadaId, j.estado, e.nombre AS \"equipoNombre\", e.categoria AS \"equipoCategoria\", j.goles, j.amarillas, j.rojas
                     FROM jugadores j
                     LEFT JOIN equipos e ON j.equipo = e.id{$where}
                     ORDER BY j.nombre",
                    $params
                );
                $res->json($rows);
            } catch (\Exception $e) {
                error_log('Error GET /jugadores: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al listar jugadores']);
            }
        });

        $r->post('', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $nombre = $req->body['nombre'] ?? '';
            $posicion = $req->body['posicion'] ?? '';
            $equipo = $req->body['equipo'] ?? '';
            $seasonId = resolveSeasonId($req->body['temporadaId'] ?? null);
            $estado = trim((string) ($req->body['estado'] ?? 'activo'));
            if (!$nombre || !$posicion || !$equipo) {
                return $res->status(400)->json(['error' => 'Falta campo requerido']);
            }
            $equipoId = (int)$equipo;
            try {
                $exists = Database::fetchOne("SELECT 1 FROM equipos WHERE id = ?", [$equipoId]);
                if (!$exists) return $res->status(400)->json(['error' => 'Equipo no válido']);

                Database::execute(
                    "INSERT INTO jugadores (nombre, posicion, equipo, temporada_id, estado, goles, amarillas, rojas)
                     VALUES (?, ?, ?, ?, ?, 0, 0, 0)",
                    [$nombre, $posicion, $equipoId, $seasonId, $estado]
                );
                $row = fetchJugadorById(Database::lastInsertId());
                $res->status(201)->json($row);
            } catch (\Exception $e) {
                error_log('Error POST /jugadores: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al crear jugador']);
            }
        });

        $r->put('/:id', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $id = (int)$req->params['id'];
            $nombre = $req->body['nombre'] ?? null;
            $posicion = $req->body['posicion'] ?? null;
            $equipo = isset($req->body['equipo']) ? (int)$req->body['equipo'] : null;
            $estado = $req->body['estado'] ?? null;
            $temporadaId = isset($req->body['temporadaId']) ? resolveSeasonId($req->body['temporadaId']) : null;
            try {
                $row = fetchJugadorById($id);
                if (!$row) return $res->status(404)->json(['error' => 'Jugador no encontrado']);
                if ($equipo !== null) {
                    $exists = Database::fetchOne("SELECT 1 FROM equipos WHERE id = ?", [$equipo]);
                    if (!$exists) return $res->status(400)->json(['error' => 'Equipo no válido']);
                }
                Database::execute(
                    "UPDATE jugadores SET nombre = COALESCE(?, nombre), posicion = COALESCE(?, posicion), equipo = COALESCE(?, equipo), estado = COALESCE(?, estado), temporada_id = COALESCE(?, temporada_id)
                     WHERE id = ?",
                    [$nombre, $posicion, $equipo, $estado, $temporadaId, $id]
                );
                $row = fetchJugadorById($id);
                $res->json($row);
            } catch (\Exception $e) {
                error_log('Error PUT /jugadores/:id: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al actualizar jugador']);
            }
        });

        $r->delete('/:id', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $id = (int)$req->params['id'];
            try {
                $count = Database::execute("DELETE FROM jugadores WHERE id = ?", [$id]);
                if (!$count) return $res->status(404)->json(['error' => 'Jugador no encontrado']);
                $res->json(['ok' => true]);
            } catch (\Exception $e) {
                error_log('Error DELETE /jugadores/:id: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al eliminar jugador']);
            }
        });

        $r->delete('', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            try {
                Database::execute("DELETE FROM jugadores");
                $res->json(['ok' => true, 'message' => 'Todos los jugadores eliminados']);
            } catch (\Exception $e) {
                error_log('Error DELETE /jugadores: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al eliminar todos los jugadores']);
            }
        });
    });
}

function fetchJugadorById(int $id): array|false {
    return Database::fetchOne(
        "SELECT id, nombre, posicion, equipo, temporada_id AS temporadaId, estado, goles, amarillas, rojas FROM jugadores WHERE id = ?",
        [$id]
    );
}
