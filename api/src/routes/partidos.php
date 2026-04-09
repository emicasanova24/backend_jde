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

const EVENT_TYPES = ['gol', 'amarilla', 'roja'];

function registerPartidosRoutes(Router $router): void {
    $router->group('/partidos', function (Router $r) {

        $r->get('/live', function (Request $req, Response $res) {
            try {
                $seasonId = resolveSeasonId($req->query['temporada'] ?? null);
                $rows = Database::fetchAll(
                    "SELECT p.id, p.fecha, p.hora, p.estadio, p.estado, p.broadcast,
                        p.goleslocal AS golesLocal, p.golesvisitante AS golesVisitante,
                        p.temporada_id AS temporadaId,
                        el.nombre AS local, ev.nombre AS visitante,
                        el.escudo AS localEscudo, ev.escudo AS visitanteEscudo
                    FROM partidos p
                    LEFT JOIN equipos el ON p.local = el.id
                    LEFT JOIN equipos ev ON p.visitante = ev.id
                    WHERE p.temporada_id = ? AND p.fecha = CURRENT_DATE AND p.broadcast IS NOT NULL AND p.broadcast != ''
                    ORDER BY p.hora",
                    [$seasonId]
                );
                $res->json($rows);
            } catch (\Exception $e) {
                error_log('Error GET /partidos/live: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al obtener partidos en vivo']);
            }
        });

        $r->get('/jornadas', function (Request $req, Response $res) {
            try {
                $seasonId = resolveSeasonId($req->query['temporada'] ?? null);
                $params = [$seasonId];
                $where = ['temporada_id = ?'];
                if (!empty($req->query['categoria'])) {
                    $params[] = $req->query['categoria'];
                    $where[] = 'LOWER(categoria) = LOWER(?)';
                }
                if (!empty($req->query['torneo'])) {
                    $params[] = $req->query['torneo'];
                    $where[] = 'LOWER(torneo) = LOWER(?)';
                }
                $where[] = 'jornada IS NOT NULL';

                $query = "SELECT jornada, COUNT(*) AS total, SUM(CASE WHEN estado = 'jugado' THEN 1 ELSE 0 END) AS jugados FROM partidos";
                if ($where) {
                    $query .= ' WHERE ' . implode(' AND ', $where);
                }
                $query .= ' GROUP BY jornada ORDER BY jornada';

                $res->json(Database::fetchAll($query, $params));
            } catch (\Exception $e) {
                error_log('Error GET /partidos/jornadas: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al listar jornadas']);
            }
        });

        $r->get('/h2h', function (Request $req, Response $res) {
            try {
                $team1 = (int) ($req->query['team1'] ?? 0);
                $team2 = (int) ($req->query['team2'] ?? 0);
                if (!$team1 || !$team2 || $team1 === $team2) {
                    return $res->status(400)->json(['error' => 'Se requieren dos equipos distintos (team1, team2)']);
                }

                $seasonId = resolveSeasonId($req->query['temporada'] ?? null);
                $params = [$seasonId, $team1, $team2, $team2, $team1];
                $where = ['p.temporada_id = ?', '((p.local = ? AND p.visitante = ?) OR (p.local = ? AND p.visitante = ?))', "p.estado = 'jugado'"];
                if (!empty($req->query['categoria'])) {
                    $params[] = $req->query['categoria'];
                    $where[] = 'LOWER(p.categoria) = LOWER(?)';
                }

                $partidos = Database::fetchAll(
                    "SELECT p.id, p.fecha, p.hora, p.estadio, p.jornada, p.torneo, p.categoria,
                        p.local AS localId, p.visitante AS visitanteId,
                        p.goleslocal AS golesLocal, p.golesvisitante AS golesVisitante,
                        el.nombre AS local, ev.nombre AS visitante,
                        el.escudo AS localEscudo, ev.escudo AS visitanteEscudo
                    FROM partidos p
                    LEFT JOIN equipos el ON p.local = el.id
                    LEFT JOIN equipos ev ON p.visitante = ev.id
                    WHERE " . implode(' AND ', $where) . "
                    ORDER BY p.fecha DESC, p.hora DESC",
                    $params
                );

                $goleadoresByPartido = [];
                if ($partidos) {
                    $ids = array_column($partidos, 'id');
                    $placeholders = implode(',', array_fill(0, count($ids), '?'));
                    $eventos = Database::fetchAll(
                        "SELECT e.partido_id, e.minuto, e.equipo_id, j.nombre AS jugador
                        FROM eventos e
                        LEFT JOIN jugadores j ON e.jugador_id = j.id
                        WHERE e.partido_id IN ($placeholders) AND e.tipo = 'gol'
                        ORDER BY e.minuto IS NULL, e.minuto",
                        $ids
                    );

                    foreach ($eventos as $evento) {
                        $goleadoresByPartido[$evento['partido_id']][] = $evento;
                    }
                }

                $team1Wins = 0;
                $team2Wins = 0;
                $draws = 0;
                $team1Goals = 0;
                $team2Goals = 0;
                $enriched = [];

                foreach ($partidos as $partido) {
                    $golesTeam1 = (int) ($partido['localId'] == $team1 ? $partido['golesLocal'] : $partido['golesVisitante']);
                    $golesTeam2 = (int) ($partido['localId'] == $team1 ? $partido['golesVisitante'] : $partido['golesLocal']);
                    $team1Goals += $golesTeam1;
                    $team2Goals += $golesTeam2;
                    if ($golesTeam1 > $golesTeam2) {
                        $team1Wins++;
                    } elseif ($golesTeam2 > $golesTeam1) {
                        $team2Wins++;
                    } else {
                        $draws++;
                    }
                    $partido['goleadores'] = $goleadoresByPartido[$partido['id']] ?? [];
                    $enriched[] = $partido;
                }

                $res->json([
                    'resumen' => [
                        'team1Wins' => $team1Wins,
                        'team2Wins' => $team2Wins,
                        'draws' => $draws,
                        'team1Goals' => $team1Goals,
                        'team2Goals' => $team2Goals,
                        'totalMatches' => count($partidos),
                    ],
                    'partidos' => $enriched,
                ]);
            } catch (\Exception $e) {
                error_log('Error GET /partidos/h2h: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al obtener historial H2H']);
            }
        });

        $r->get('', function (Request $req, Response $res) {
            try {
                $seasonId = resolveSeasonId($req->query['temporada'] ?? null);
                $params = [$seasonId];
                $where = ['p.temporada_id = ?'];
                $query = buildPartidosSelect();

                if (!empty($req->query['estado'])) {
                    $params[] = $req->query['estado'];
                    $where[] = 'LOWER(p.estado) = LOWER(?)';
                }
                if (!empty($req->query['torneo'])) {
                    $params[] = $req->query['torneo'];
                    $where[] = 'LOWER(p.torneo) = LOWER(?)';
                }
                if (!empty($req->query['categoria'])) {
                    $params[] = $req->query['categoria'];
                    $where[] = 'LOWER(p.categoria) = LOWER(?)';
                }
                if (isset($req->query['jornada'])) {
                    $params[] = (int) $req->query['jornada'];
                    $where[] = 'p.jornada = ?';
                }

                if ($where) {
                    $query .= ' WHERE ' . implode(' AND ', $where);
                }
                $query .= ' ORDER BY p.jornada, p.fecha, p.hora';

                $res->json(Database::fetchAll($query, $params));
            } catch (\Exception $e) {
                error_log('Error GET /partidos: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al listar partidos']);
            }
        });

        $r->post('', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $body = $req->body;
            $fecha = $body['fecha'] ?? '';
            $hora = $body['hora'] ?? '';
            $estadio = $body['estadio'] ?? '';
            $local = (int) ($body['local'] ?? 0);
            $visitante = (int) ($body['visitante'] ?? 0);
            $estado = $body['estado'] ?? 'pendiente';
            $torneo = $body['torneo'] ?? '';
            $categoria = $body['categoria'] ?? '';
            $temporadaId = resolveSeasonId($body['temporadaId'] ?? null);
            $jornada = $body['jornada'] ?? null;
            $broadcast = $body['broadcast'] ?? null;

            if (!$fecha || !$hora || !$estadio || !$local || !$visitante || !$torneo || !$categoria) {
                return $res->status(400)->json(['error' => 'Faltan campos requeridos']);
            }
            if ($local === $visitante) {
                return $res->status(400)->json(['error' => 'Los equipos del partido son inválidos']);
            }

            try {
                if (!Database::fetchOne('SELECT 1 FROM equipos WHERE id = ?', [$local]) || !Database::fetchOne('SELECT 1 FROM equipos WHERE id = ?', [$visitante])) {
                    return $res->status(400)->json(['error' => 'Equipo local o visitante inválido']);
                }

                Database::execute(
                    'INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, temporada_id, jornada, broadcast)
                    VALUES (?, ?, ?, ?, ?, ?, 0, 0, 0, 0, ?, ?, ?, ?, ?)',
                    [$fecha, $hora, $estadio, $local, $visitante, $estado, $torneo, $categoria, $temporadaId, $jornada, $broadcast]
                );

                $res->status(201)->json(fetchPartidoById(Database::lastInsertId()));
            } catch (\Exception $e) {
                error_log('Error POST /partidos: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al crear partido']);
            }
        });

        $r->post('/bulk', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $input = $req->body['partidos'] ?? [];
            if (!is_array($input) || !count($input)) {
                return $res->status(400)->json(['error' => 'Se requiere un array de partidos']);
            }

            $partidos = array_map(static fn(array $partido) => [
                'fecha' => $partido['fecha'] ?? '',
                'hora' => $partido['hora'] ?? '',
                'estadio' => trim($partido['estadio'] ?? ''),
                'local' => (int) ($partido['local'] ?? 0),
                'visitante' => (int) ($partido['visitante'] ?? 0),
                'estado' => $partido['estado'] ?? 'pendiente',
                'torneo' => $partido['torneo'] ?? '',
                'categoria' => $partido['categoria'] ?? '',
                'temporadaId' => resolveSeasonId($partido['temporadaId'] ?? null),
                'jornada' => $partido['jornada'] ?? null,
                'broadcast' => $partido['broadcast'] ?? null,
            ], $input);

            foreach ($partidos as $partido) {
                if (!$partido['fecha'] || !$partido['hora'] || !$partido['estadio'] || !$partido['torneo'] || !$partido['categoria']) {
                    return $res->status(400)->json(['error' => 'Todos los partidos requieren fecha, hora, estadio, torneo y categoría']);
                }
                if (!$partido['local'] || !$partido['visitante'] || $partido['local'] === $partido['visitante']) {
                    return $res->status(400)->json(['error' => 'Cada partido debe tener equipos válidos y distintos']);
                }
            }

            $allTeams = [];
            foreach ($partidos as $partido) {
                $allTeams[] = $partido['local'];
                $allTeams[] = $partido['visitante'];
            }

            if (count(array_unique($allTeams)) !== count($allTeams)) {
                return $res->status(400)->json(['error' => 'Hay equipos repetidos en el fixture']);
            }

            $db = Database::getConnection();
            try {
                $db->beginTransaction();

                $placeholders = implode(',', array_fill(0, count($allTeams), '?'));
                $equipos = Database::fetchAll("SELECT id FROM equipos WHERE id IN ($placeholders)", $allTeams);
                if (count($equipos) !== count(array_unique($allTeams))) {
                    $db->rollBack();
                    return $res->status(400)->json(['error' => 'Uno o más equipos no existen']);
                }

                $created = [];
                foreach ($partidos as $partido) {
                    Database::execute(
                        'INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, temporada_id, jornada, broadcast)
                        VALUES (?, ?, ?, ?, ?, ?, 0, 0, 0, 0, ?, ?, ?, ?, ?)',
                        [
                            $partido['fecha'],
                            $partido['hora'],
                            $partido['estadio'],
                            $partido['local'],
                            $partido['visitante'],
                            $partido['estado'],
                            $partido['torneo'],
                            $partido['categoria'],
                            $partido['temporadaId'],
                            $partido['jornada'],
                            $partido['broadcast'],
                        ]
                    );
                    $created[] = fetchPartidoById(Database::lastInsertId());
                }

                $db->commit();
                $res->status(201)->json(['partidos' => $created, 'total' => count($created)]);
            } catch (\Exception $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                error_log('Error POST /partidos/bulk: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al crear fixture']);
            }
        });

        $r->put('/:id', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $id = (int) $req->params['id'];
            $body = $req->body;

            try {
                if (!fetchPartidoById($id)) {
                    return $res->status(404)->json(['error' => 'Partido no encontrado']);
                }

                $localId = array_key_exists('local', $body) ? (int) $body['local'] : null;
                $visitanteId = array_key_exists('visitante', $body) ? (int) $body['visitante'] : null;

                if ($localId !== null && !Database::fetchOne('SELECT 1 FROM equipos WHERE id = ?', [$localId])) {
                    return $res->status(400)->json(['error' => 'Equipo local inválido']);
                }
                if ($visitanteId !== null && !Database::fetchOne('SELECT 1 FROM equipos WHERE id = ?', [$visitanteId])) {
                    return $res->status(400)->json(['error' => 'Equipo visitante inválido']);
                }
                if ($localId !== null && $visitanteId !== null && $localId === $visitanteId) {
                    return $res->status(400)->json(['error' => 'Los equipos del partido deben ser distintos']);
                }

                Database::execute(
                    'UPDATE partidos
                    SET fecha = COALESCE(?, fecha),
                        hora = COALESCE(?, hora),
                        estadio = COALESCE(?, estadio),
                        local = COALESCE(?, local),
                        visitante = COALESCE(?, visitante),
                        estado = COALESCE(?, estado),
                        goleslocal = COALESCE(?, goleslocal),
                        golesvisitante = COALESCE(?, golesvisitante),
                        amarillas = COALESCE(?, amarillas),
                        rojas = COALESCE(?, rojas),
                        torneo = COALESCE(?, torneo),
                        categoria = COALESCE(?, categoria),
                        temporada_id = COALESCE(?, temporada_id),
                        jornada = COALESCE(?, jornada),
                        broadcast = COALESCE(?, broadcast)
                    WHERE id = ?',
                    [
                        $body['fecha'] ?? null,
                        $body['hora'] ?? null,
                        $body['estadio'] ?? null,
                        $localId,
                        $visitanteId,
                        $body['estado'] ?? null,
                        $body['golesLocal'] ?? null,
                        $body['golesVisitante'] ?? null,
                        $body['amarillas'] ?? null,
                        $body['rojas'] ?? null,
                        $body['torneo'] ?? null,
                        $body['categoria'] ?? null,
                        isset($body['temporadaId']) ? resolveSeasonId($body['temporadaId']) : null,
                        $body['jornada'] ?? null,
                        $body['broadcast'] ?? null,
                        $id,
                    ]
                );

                $res->json(fetchPartidoById($id));
            } catch (\Exception $e) {
                error_log('Error PUT /partidos/:id: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al actualizar partido']);
            }
        });

        $r->put('/:id/resultado', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $id = (int) $req->params['id'];
            $golesLocal = (int) ($req->body['golesLocal'] ?? -1);
            $golesVisitante = (int) ($req->body['golesVisitante'] ?? -1);
            $eventos = $req->body['eventos'] ?? [];

            if ($golesLocal < 0 || $golesVisitante < 0) {
                return $res->status(400)->json(['error' => 'El resultado del partido es inválido']);
            }
            foreach ($eventos as $evento) {
                if (!in_array($evento['tipo'] ?? '', EVENT_TYPES, true)) {
                    return $res->status(400)->json(['error' => 'Hay eventos con tipo inválido']);
                }
            }

            $amarillas = count(array_filter($eventos, static fn(array $evento) => ($evento['tipo'] ?? null) === 'amarilla'));
            $rojas = count(array_filter($eventos, static fn(array $evento) => ($evento['tipo'] ?? null) === 'roja'));

            $db = Database::getConnection();
            try {
                $db->beginTransaction();

                if (!Database::fetchOne('SELECT 1 FROM partidos WHERE id = ?', [$id])) {
                    $db->rollBack();
                    return $res->status(404)->json(['error' => 'Partido no encontrado']);
                }

                Database::execute(
                    "UPDATE partidos SET goleslocal = ?, golesvisitante = ?, amarillas = ?, rojas = ?, estado = 'jugado' WHERE id = ?",
                    [$golesLocal, $golesVisitante, $amarillas, $rojas, $id]
                );
                Database::execute('DELETE FROM eventos WHERE partido_id = ?', [$id]);

                $createdEventos = [];
                foreach ($eventos as $evento) {
                    Database::execute(
                        'INSERT INTO eventos (partido_id, jugador_id, equipo_id, temporada_id, tipo, minuto) VALUES (?, ?, ?, ?, ?, ?)',
                        [$id, $evento['jugador_id'] ?? null, $evento['equipo_id'] ?? null, (int) (fetchPartidoById($id)['temporadaId'] ?? resolveSeasonId(null)), $evento['tipo'], $evento['minuto'] ?? null]
                    );
                    $createdEventos[] = fetchPartidoEventoById(Database::lastInsertId());
                }

                $db->commit();
                $res->json([
                    'partido' => fetchPartidoById($id),
                    'eventos' => $createdEventos,
                ]);
            } catch (\Exception $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                error_log('Error PUT /partidos/:id/resultado: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al guardar el resultado']);
            }
        });

        $r->delete('/:id', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $id = (int) $req->params['id'];
            try {
                $count = Database::execute('DELETE FROM partidos WHERE id = ?', [$id]);
                if (!$count) {
                    return $res->status(404)->json(['error' => 'Partido no encontrado']);
                }
                $res->json(['ok' => true]);
            } catch (\Exception $e) {
                error_log('Error DELETE /partidos/:id: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al eliminar partido']);
            }
        });
    });
}

function buildPartidosSelect(): string {
    return "SELECT p.id, p.fecha, p.hora, p.estadio, p.local AS localId, p.visitante AS visitanteId, p.estado,
        p.goleslocal AS golesLocal, p.golesvisitante AS golesVisitante, p.amarillas, p.rojas,
        p.torneo, p.categoria, p.temporada_id AS temporadaId, p.jornada, p.broadcast,
        el.nombre AS local, ev.nombre AS visitante,
        el.escudo AS localEscudo, ev.escudo AS visitanteEscudo
    FROM partidos p
    LEFT JOIN equipos el ON p.local = el.id
    LEFT JOIN equipos ev ON p.visitante = ev.id";
}

function fetchPartidoById(int $id): array|false {
    return Database::fetchOne(buildPartidosSelect() . ' WHERE p.id = ?', [$id]);
}

function fetchPartidoEventoById(int $id): array|false {
    return Database::fetchOne('SELECT id, partido_id, jugador_id, equipo_id, tipo, minuto FROM eventos WHERE id = ?', [$id]);
}