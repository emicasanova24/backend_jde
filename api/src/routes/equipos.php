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

function registerEquiposRoutes(Router $router): void {
    $router->group('/equipos', function (Router $r) {

        $r->get('', function (Request $req, Response $res) {
            try {
                $seasonId = resolveSeasonId($req->query['temporada'] ?? null);
                $params = [$seasonId];
                $query = "SELECT id, nombre, estadio, escudo, categoria, temporada_id AS temporadaId FROM equipos WHERE temporada_id = ?";
                if (!empty($req->query['categoria'])) {
                    $params[] = $req->query['categoria'];
                    $query .= " AND LOWER(categoria) = LOWER(?)";
                }
                $query .= " ORDER BY categoria, nombre";
                $rows = Database::fetchAll($query, $params);
                $res->json($rows);
            } catch (\Exception $e) {
                error_log('Error GET /equipos: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al listar equipos']);
            }
        });

        $r->post('', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $nombre = $req->body['nombre'] ?? '';
            $estadio = $req->body['estadio'] ?? '';
            $escudo = $req->body['escudo'] ?? '';
            $categoria = $req->body['categoria'] ?? '';
            $seasonId = resolveSeasonId($req->body['temporadaId'] ?? null);
            if (!$nombre || !$estadio || !$categoria) {
                return $res->status(400)->json(['error' => 'Falta nombre, estadio o categoría']);
            }
            try {
                Database::execute("INSERT INTO equipos (nombre, estadio, escudo, categoria, temporada_id) VALUES (?, ?, ?, ?, ?)", [$nombre, $estadio, $escudo, $categoria, $seasonId]);
                $row = fetchEquipoById(Database::lastInsertId());
                $res->status(201)->json($row);
            } catch (\Exception $e) {
                error_log('Error POST /equipos: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al crear equipo']);
            }
        });

        $r->put('/:id', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $id = (int)$req->params['id'];
            $nombre = $req->body['nombre'] ?? null;
            $estadio = $req->body['estadio'] ?? null;
            $escudo = $req->body['escudo'] ?? null;
            $categoria = $req->body['categoria'] ?? null;
            $temporadaId = isset($req->body['temporadaId']) ? resolveSeasonId($req->body['temporadaId']) : null;
            try {
                $row = fetchEquipoById($id);
                if (!$row) return $res->status(404)->json(['error' => 'Equipo no encontrado']);
                Database::execute(
                    "UPDATE equipos SET nombre = COALESCE(?, nombre), estadio = COALESCE(?, estadio), escudo = COALESCE(?, escudo), categoria = COALESCE(?, categoria), temporada_id = COALESCE(?, temporada_id) WHERE id = ?",
                    [$nombre, $estadio, $escudo, $categoria, $temporadaId, $id]
                );
                $row = fetchEquipoById($id);
                $res->json($row);
            } catch (\Exception $e) {
                error_log('Error PUT /equipos/:id: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al actualizar equipo']);
            }
        });

        $r->delete('/:id', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $id = (int)$req->params['id'];
            try {
                $count = Database::execute("DELETE FROM equipos WHERE id = ?", [$id]);
                if (!$count) return $res->status(404)->json(['error' => 'Equipo no encontrado']);
                $res->json(['ok' => true]);
            } catch (\Exception $e) {
                error_log('Error DELETE /equipos/:id: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al eliminar equipo']);
            }
        });

        $r->post('/sync', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $input = $req->body['equipos'] ?? null;
            if (!is_array($input)) {
                return $res->status(400)->json(['error' => 'Debe enviar { equipos: [...] }']);
            }
            $seasonId = resolveSeasonId($req->body['temporadaId'] ?? null);
            $names = array_values(array_unique(array_filter(array_map(fn($e) => trim($e['nombre'] ?? ''), $input))));

            try {
                if (count($names)) {
                    $placeholders = implode(', ', array_fill(0, count($names), '?'));
                    Database::execute("DELETE FROM equipos WHERE temporada_id = ? AND nombre NOT IN ($placeholders)", array_merge([$seasonId], $names));
                } else {
                    Database::execute("DELETE FROM equipos WHERE temporada_id = ?", [$seasonId]);
                }

                foreach ($input as $equipo) {
                    $nombre = trim($equipo['nombre'] ?? '');
                    if (!$nombre) continue;
                    Database::execute(
                        "INSERT INTO equipos (nombre, estadio, escudo, categoria, temporada_id) VALUES (?, ?, ?, ?, ?)
                         ON DUPLICATE KEY UPDATE estadio = VALUES(estadio), escudo = VALUES(escudo)",
                        [$nombre, $equipo['estadio'] ?? '', $equipo['escudo'] ?? '', $equipo['categoria'] ?? 'Primera', $seasonId]
                    );
                }

                $rows = Database::fetchAll("SELECT id, nombre, estadio, escudo, categoria, temporada_id AS temporadaId FROM equipos WHERE temporada_id = ? ORDER BY categoria, nombre", [$seasonId]);
                $res->json(['ok' => true, 'equipos' => $rows]);
            } catch (\Exception $e) {
                error_log('Error POST /equipos/sync: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al sincronizar equipos']);
            }
        });
    });
}

function fetchEquipoById(int $id): array|false {
    return Database::fetchOne("SELECT id, nombre, estadio, escudo, categoria, temporada_id AS temporadaId FROM equipos WHERE id = ?", [$id]);
}
