<?php
namespace App\Routes;

use App\Router;
use App\Database;
use App\Request;
use App\Response;

use function App\Middleware\authMiddleware;

require_once __DIR__ . '/../middleware/auth.php';

const ALLOWED_PLAYER_POSITIONS = ['PEND', 'PT', 'LIB', 'CT', 'LAT', 'MCD', 'MC', 'CRR', 'INT', 'MP', 'EXT', 'SD', 'DC'];

function normalizeJugadorPosicion(mixed $posicion): string {
    return strtoupper(trim((string) $posicion));
}

function isAllowedJugadorPosicion(string $posicion): bool {
    return in_array($posicion, ALLOWED_PLAYER_POSITIONS, true);
}

function registerJugadoresRoutes(Router $router): void {
    $router->group('/jugadores', function (Router $r) {

        $r->get('', function (Request $req, Response $res) {
            try {
                $params = [];
                $where = '';
                if (!empty($req->query['equipo'])) {
                    $params[] = (int)$req->query['equipo'];
                    $where = ' WHERE j.equipo = ?';
                }
                $rows = Database::fetchAll(
                    "SELECT j.id, j.nombre, j.posicion, j.equipo, e.nombre AS \"equipoNombre\", e.categoria AS \"equipoCategoria\", j.goles, j.amarillas, j.rojas
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

        $r->get('/:id/disciplina', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $id = (int)$req->params['id'];
            try {
                $jugador = Database::fetchOne(
                    "SELECT j.id, j.nombre, j.posicion, j.equipo, e.nombre AS equipoNombre, e.categoria AS equipoCategoria
                     FROM jugadores j
                     LEFT JOIN equipos e ON j.equipo = e.id
                     WHERE j.id = ?",
                    [$id]
                );
                if (!$jugador) {
                    return $res->status(404)->json(['error' => 'Jugador no encontrado']);
                }

                $historial = Database::fetchAll(
                    "SELECT ev.id, ev.tipo, ev.minuto,
                            p.id AS partidoId, p.fecha, p.hora, p.jornada, p.torneo, p.categoria,
                            el.id AS localId, el.nombre AS localNombre,
                            vi.id AS visitanteId, vi.nombre AS visitanteNombre,
                            eq.nombre AS equipoEventoNombre
                     FROM eventos ev
                     INNER JOIN partidos p ON ev.partido_id = p.id
                     LEFT JOIN equipos el ON p.local = el.id
                     LEFT JOIN equipos vi ON p.visitante = vi.id
                     LEFT JOIN equipos eq ON ev.equipo_id = eq.id
                     WHERE ev.jugador_id = ? AND ev.tipo IN ('amarilla', 'roja')
                     ORDER BY p.fecha DESC, ev.minuto DESC, ev.id DESC",
                    [$id]
                );

                $summary = [
                    'amarillas' => 0,
                    'rojas' => 0,
                    'total' => count($historial),
                ];

                foreach ($historial as $evento) {
                    if ($evento['tipo'] === 'amarilla') {
                        $summary['amarillas']++;
                    }
                    if ($evento['tipo'] === 'roja') {
                        $summary['rojas']++;
                    }
                }

                $res->json([
                    'jugador' => $jugador,
                    'summary' => $summary,
                    'historial' => $historial,
                ]);
            } catch (\Exception $e) {
                error_log('Error GET /jugadores/:id/disciplina: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al obtener historial disciplinario']);
            }
        });

        $r->post('', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $nombre = trim((string) ($req->body['nombre'] ?? ''));
            $posicion = normalizeJugadorPosicion($req->body['posicion'] ?? '');
            $equipo = $req->body['equipo'] ?? '';
            if (!$nombre || !$posicion || !$equipo) {
                return $res->status(400)->json(['error' => 'Falta campo requerido']);
            }
            if (!isAllowedJugadorPosicion($posicion)) {
                return $res->status(400)->json(['error' => 'La posición seleccionada no es válida']);
            }
            $equipoId = (int)$equipo;
            try {
                $exists = Database::fetchOne("SELECT 1 FROM equipos WHERE id = ?", [$equipoId]);
                if (!$exists) return $res->status(400)->json(['error' => 'Equipo no válido']);

                Database::execute(
                    "INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
                     VALUES (?, ?, ?, 0, 0, 0)",
                    [$nombre, $posicion, $equipoId]
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
            $nombre = array_key_exists('nombre', $req->body) ? trim((string) $req->body['nombre']) : null;
            $posicion = array_key_exists('posicion', $req->body) ? normalizeJugadorPosicion($req->body['posicion']) : null;
            $equipo = isset($req->body['equipo']) ? (int)$req->body['equipo'] : null;
            try {
                $row = fetchJugadorById($id);
                if (!$row) return $res->status(404)->json(['error' => 'Jugador no encontrado']);
                $currentPosicion = normalizeJugadorPosicion($row['posicion'] ?? '');
                if ($posicion !== null && $posicion !== '' && !isAllowedJugadorPosicion($posicion) && $posicion !== $currentPosicion) {
                    return $res->status(400)->json(['error' => 'La posición seleccionada no es válida']);
                }
                if ($equipo !== null) {
                    $exists = Database::fetchOne("SELECT 1 FROM equipos WHERE id = ?", [$equipo]);
                    if (!$exists) return $res->status(400)->json(['error' => 'Equipo no válido']);
                }
                Database::execute(
                    "UPDATE jugadores SET nombre = COALESCE(?, nombre), posicion = COALESCE(?, posicion), equipo = COALESCE(?, equipo)
                     WHERE id = ?",
                    [$nombre, $posicion, $equipo, $id]
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
        "SELECT id, nombre, posicion, equipo, goles, amarillas, rojas FROM jugadores WHERE id = ?",
        [$id]
    );
}
