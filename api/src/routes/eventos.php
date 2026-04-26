<?php
namespace App\Routes;

use App\Router;
use App\Database;
use App\Request;
use App\Response;

use function App\Middleware\authMiddleware;

require_once __DIR__ . '/../middleware/auth.php';

function registerEventosRoutes(Router $router): void {
    $router->group('/eventos', function (Router $r) {

        // GET /eventos?partido_id=X
        $r->get('', function (Request $req, Response $res) {
            try {
                $params = [];
                $where = '';
                if (!empty($req->query['partido_id'])) {
                    $params[] = (int)$req->query['partido_id'];
                    $where = ' WHERE ev.partido_id = ?';
                }
                $rows = Database::fetchAll(
                    "SELECT ev.id, ev.partido_id, ev.jugador_id, ev.equipo_id, ev.tipo, ev.minuto,
                            j.nombre AS jugador_nombre, e.nombre AS equipo_nombre
                     FROM eventos ev
                     LEFT JOIN jugadores j ON ev.jugador_id = j.id
                     LEFT JOIN equipos e ON ev.equipo_id = e.id{$where}
                     ORDER BY ev.minuto IS NULL, ev.minuto, ev.id",
                    $params
                );
                $res->json($rows);
            } catch (\Exception $e) {
                error_log('Error GET /eventos: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al listar eventos']);
            }
        });

        // POST /eventos/bulk
        $r->post('/bulk', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $eventos = $req->body['eventos'] ?? [];
            if (!is_array($eventos) || !count($eventos)) {
                return $res->status(400)->json(['error' => 'Se requiere un array de eventos']);
            }

            $db = Database::getConnection();
            try {
                $db->beginTransaction();
                $created = [];
                foreach ($eventos as $ev) {
                    $partidoId = $ev['partido_id'] ?? null;
                    $tipo = $ev['tipo'] ?? '';
                    if (!$partidoId || !$tipo) {
                        $db->rollBack();
                        return $res->status(400)->json(['error' => 'Cada evento requiere partido_id y tipo']);
                    }
                    Database::execute(
                        "INSERT INTO eventos (partido_id, jugador_id, equipo_id, tipo, minuto)
                         VALUES (?, ?, ?, ?, ?)",
                        [$partidoId, $ev['jugador_id'] ?? null, $ev['equipo_id'] ?? null, $tipo, $ev['minuto'] ?? null]
                    );
                    $created[] = fetchEventoById(Database::lastInsertId());
                }
                $db->commit();
                $res->status(201)->json($created);
            } catch (\Exception $e) {
                $db->rollBack();
                error_log('Error POST /eventos/bulk: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al crear eventos']);
            }
        });

        // POST /eventos
        $r->post('', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $partidoId = $req->body['partido_id'] ?? null;
            $tipo = $req->body['tipo'] ?? '';
            if (!$partidoId || !$tipo) {
                return $res->status(400)->json(['error' => 'partido_id y tipo son requeridos']);
            }
            try {
                Database::execute(
                    "INSERT INTO eventos (partido_id, jugador_id, equipo_id, tipo, minuto)
                     VALUES (?, ?, ?, ?, ?)",
                    [$partidoId, $req->body['jugador_id'] ?? null, $req->body['equipo_id'] ?? null, $tipo, $req->body['minuto'] ?? null]
                );
                $row = fetchEventoById(Database::lastInsertId());
                $res->status(201)->json($row);
            } catch (\Exception $e) {
                error_log('Error POST /eventos: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al crear evento']);
            }
        });

        // DELETE /eventos/partido/:partidoId
        $r->delete('/partido/:partidoId', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $partidoId = (int)$req->params['partidoId'];
            try {
                $count = Database::execute("DELETE FROM eventos WHERE partido_id = ?", [$partidoId]);
                $res->json(['ok' => true, 'deleted' => $count]);
            } catch (\Exception $e) {
                error_log('Error DELETE /eventos/partido/:partidoId: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al eliminar eventos del partido']);
            }
        });

        // DELETE /eventos/:id
        $r->delete('/:id', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $id = (int)$req->params['id'];
            try {
                $count = Database::execute("DELETE FROM eventos WHERE id = ?", [$id]);
                if (!$count) return $res->status(404)->json(['error' => 'Evento no encontrado']);
                $res->json(['ok' => true]);
            } catch (\Exception $e) {
                error_log('Error DELETE /eventos/:id: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al eliminar evento']);
            }
        });
    });
}

function fetchEventoById(int $id): array|false {
    return Database::fetchOne(
        "SELECT id, partido_id, jugador_id, equipo_id, tipo, minuto FROM eventos WHERE id = ?",
        [$id]
    );
}
