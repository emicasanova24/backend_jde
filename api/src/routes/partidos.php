<?php
namespace App\Routes;

use App\Router;
use App\Database;
use App\Request;
use App\Response;

use function App\Middleware\authMiddleware;

require_once __DIR__ . '/../middleware/auth.php';

const EVENT_TYPES = ['gol', 'autogol', 'amarilla', 'roja'];

function normalizePartidoEscudo(?string $escudo): string {
    $path = trim((string) $escudo);
    if ($path === '') {
        return '';
    }

    if (str_starts_with($path, '/logos/')) {
        return '/assets-equipos/logos/' . basename($path);
    }

    return $path;
}

function normalizePartidoRecord(array $row): array {
    if (array_key_exists('localEscudo', $row)) {
        $row['localEscudo'] = normalizePartidoEscudo($row['localEscudo']);
    }
    if (array_key_exists('visitanteEscudo', $row)) {
        $row['visitanteEscudo'] = normalizePartidoEscudo($row['visitanteEscudo']);
    }
    return $row;
}

function fetchEquiposPartidoMeta(array $ids): array {
    $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static fn(int $id) => $id > 0)));
    if (!$ids) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $rows = Database::fetchAll("SELECT id, estadio FROM equipos WHERE id IN ($placeholders)", $ids);
    $byId = [];
    foreach ($rows as $row) {
        $byId[(int) $row['id']] = $row;
    }

    return $byId;
}

function resolvePartidoEstadio(?string $estadio, int $localId, array $equiposById = []): string {
    $normalized = trim((string) $estadio);
    if ($normalized !== '') {
        return $normalized;
    }

    $equipo = $equiposById[$localId] ?? null;
    if (!$equipo && $localId > 0) {
        $equipo = Database::fetchOne('SELECT id, estadio FROM equipos WHERE id = ?', [$localId]);
    }

    return trim((string) ($equipo['estadio'] ?? ''));
}

function ensureHomeConfigTable(): void {
    Database::execute(
        "CREATE TABLE IF NOT EXISTS home_config (
            clave VARCHAR(120) NOT NULL PRIMARY KEY,
            valor_string VARCHAR(512) NULL,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
}

function buildNextPartidoConfigKey(?string $categoria, ?string $torneo): string {
    $categoriaKey = strtolower(trim((string) ($categoria ?: 'all')));
    $torneoKey = strtolower(trim((string) ($torneo ?: 'all')));
    return "partido_proximo_override:{$categoriaKey}:{$torneoKey}";
}

function getHomeConfigValue(string $key): ?string {
    ensureHomeConfigTable();
    $row = Database::fetchOne('SELECT valor_string FROM home_config WHERE clave = ?', [$key]);
    return $row ? (string) ($row['valor_string'] ?? '') : null;
}

function setHomeConfigValue(string $key, ?string $value): void {
    ensureHomeConfigTable();
    $normalized = trim((string) $value);
    if ($normalized === '') {
        Database::execute('DELETE FROM home_config WHERE clave = ?', [$key]);
        return;
    }

    Database::execute(
        'INSERT INTO home_config (clave, valor_string) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE valor_string = VALUES(valor_string)',
        [$key, $normalized]
    );
}

function matchesPartidoFilters(array $partido, ?string $categoria, ?string $torneo): bool {
    if ($categoria && strcasecmp((string) ($partido['categoria'] ?? ''), $categoria) !== 0) {
        return false;
    }
    if ($torneo && strcasecmp((string) ($partido['torneo'] ?? ''), $torneo) !== 0) {
        return false;
    }
    return true;
}

function fetchNextPartido(?string $categoria, ?string $torneo, bool $includePostergados = false): array|false {
    $params = [];
    $where = [];

    if ($includePostergados) {
        $where[] = "LOWER(p.estado) <> 'jugado'";
    } else {
        $params[] = 'pendiente';
        $where[] = 'LOWER(p.estado) = LOWER(?)';
    }

    if ($categoria) {
        $params[] = $categoria;
        $where[] = 'LOWER(p.categoria) = LOWER(?)';
    }
    if ($torneo) {
        $params[] = $torneo;
        $where[] = 'LOWER(p.torneo) = LOWER(?)';
    }

    $query = buildPartidosSelect() . ' WHERE ' . implode(' AND ', $where) . ' ORDER BY p.fecha, p.hora, p.id LIMIT 1';
    $row = Database::fetchOne($query, $params);
    return $row ? normalizePartidoRecord($row) : false;
}

function validatePartidoEventos(int $partidoId, array $eventos): array {
    $partido = fetchPartidoById($partidoId);
    if (!$partido) {
        return ['error' => 'Partido no encontrado'];
    }

    $allowedTeams = [(int) $partido['localId'], (int) $partido['visitanteId']];
    $playerIds = [];
    $normalizedEventos = [];

    foreach ($eventos as $index => $evento) {
        $tipo = $evento['tipo'] ?? '';
        $jugadorId = (int) ($evento['jugador_id'] ?? 0);
        $equipoId = (int) ($evento['equipo_id'] ?? 0);
        $minutoRaw = $evento['minuto'] ?? null;
        $minuto = ($minutoRaw === null || $minutoRaw === '') ? null : (int) $minutoRaw;

        if (!in_array($tipo, EVENT_TYPES, true)) {
            return ['error' => 'Hay eventos con tipo inválido'];
        }
        if ($jugadorId <= 0 || $equipoId <= 0) {
            return ['error' => 'Cada evento debe indicar jugador y equipo'];
        }
        if (!in_array($equipoId, $allowedTeams, true)) {
            return ['error' => 'Hay eventos asignados a un equipo que no participa del partido'];
        }
        if ($minuto !== null && ($minuto < 0 || $minuto > 130)) {
            return ['error' => 'El minuto de la incidencia es inválido'];
        }

        $playerIds[] = $jugadorId;
        $normalizedEventos[$index] = [
            'tipo' => $tipo,
            'jugador_id' => $jugadorId,
            'equipo_id' => $equipoId,
            'minuto' => $minuto,
        ];
    }

    if (!$playerIds) {
        return ['partido' => $partido, 'eventos' => []];
    }

    $placeholders = implode(',', array_fill(0, count($playerIds), '?'));
    $players = Database::fetchAll(
        "SELECT id, equipo, nombre FROM jugadores WHERE id IN ($placeholders)",
        $playerIds
    );
    $playersById = [];
    foreach ($players as $player) {
        $playersById[(int) $player['id']] = $player;
    }

    foreach ($normalizedEventos as $evento) {
        $player = $playersById[$evento['jugador_id']] ?? null;
        if (!$player) {
            return ['error' => 'Hay eventos con jugadores inexistentes'];
        }
        if ((int) $player['equipo'] !== (int) $evento['equipo_id']) {
            return ['error' => 'El jugador seleccionado no pertenece al equipo de la incidencia'];
        }
        if (!in_array((int) $player['equipo'], $allowedTeams, true)) {
            return ['error' => 'El jugador seleccionado no pertenece a este partido'];
        }
    }

    return ['partido' => $partido, 'eventos' => array_values($normalizedEventos)];
}

function registerPartidosRoutes(Router $router): void {
    $router->group('/partidos', function (Router $r) {

        $r->get('/live', function (Request $req, Response $res) {
            try {
                $rows = Database::fetchAll(
                    "SELECT p.id, p.fecha, p.hora, p.estadio, p.estado, p.broadcast,
                        p.goleslocal AS golesLocal, p.golesvisitante AS golesVisitante,
                        el.nombre AS local, ev.nombre AS visitante,
                        el.escudo AS localEscudo, ev.escudo AS visitanteEscudo
                    FROM partidos p
                    LEFT JOIN equipos el ON p.local = el.id
                    LEFT JOIN equipos ev ON p.visitante = ev.id
                    WHERE p.fecha = CURDATE()
                      AND LOWER(p.estado) <> 'jugado'
                      AND p.broadcast IS NOT NULL
                      AND p.broadcast != ''
                      AND p.hora != '00:00:00'
                      AND NOW() >= DATE_SUB(CAST(CONCAT(p.fecha, ' ', p.hora) AS DATETIME), INTERVAL 15 MINUTE)
                      AND NOW() <= DATE_ADD(CAST(CONCAT(p.fecha, ' ', p.hora) AS DATETIME), INTERVAL 3 HOUR)
                    ORDER BY p.hora"
                );
                $res->json(array_map('App\\Routes\\normalizePartidoRecord', $rows));
            } catch (\Exception $e) {
                error_log('Error GET /partidos/live: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al obtener partidos en vivo']);
            }
        });

        // Partidos en curso con marcador + eventos — para polling de tiempo real
        $r->get('/live-scores', function (Request $req, Response $res) {
            try {
                if (getHomeConfigValue('live_scores_widget_enabled') === '0') {
                    return $res->json([]);
                }

                // Manual override: mostrar partidos seleccionados manualmente
                $overrideRaw = getHomeConfigValue('live_scores_override_ids');
                $overrideIds = ($overrideRaw !== null && $overrideRaw !== '')
                    ? array_values(array_filter(array_map('intval', explode(',', $overrideRaw)), static fn(int $id) => $id > 0))
                    : [];

                if ($overrideIds) {
                    $ph = implode(',', array_fill(0, count($overrideIds), '?'));
                    $partidos = Database::fetchAll(
                        "SELECT p.id, p.fecha, p.hora, p.estadio, p.estado, p.broadcast,
                            p.goleslocal AS golesLocal, p.golesvisitante AS golesVisitante,
                            p.torneo, p.categoria, p.jornada,
                            el.nombre AS local, ev.nombre AS visitante,
                            el.id AS localId, ev.id AS visitanteId,
                            el.escudo AS localEscudo, ev.escudo AS visitanteEscudo
                        FROM partidos p
                        LEFT JOIN equipos el ON p.local = el.id
                        LEFT JOIN equipos ev ON p.visitante = ev.id
                        WHERE p.id IN ($ph)
                        ORDER BY p.fecha, p.hora",
                        $overrideIds
                    );
                } else {
                    $partidos = Database::fetchAll(
                        "SELECT p.id, p.fecha, p.hora, p.estadio, p.estado, p.broadcast,
                            p.goleslocal AS golesLocal, p.golesvisitante AS golesVisitante,
                            p.torneo, p.categoria, p.jornada,
                            el.nombre AS local, ev.nombre AS visitante,
                            el.id AS localId, ev.id AS visitanteId,
                            el.escudo AS localEscudo, ev.escudo AS visitanteEscudo
                        FROM partidos p
                        LEFT JOIN equipos el ON p.local = el.id
                        LEFT JOIN equipos ev ON p.visitante = ev.id
                        WHERE p.estado = 'en_curso'
                           OR (
                                p.fecha = CURDATE()
                                AND LOWER(p.estado) NOT IN ('jugado', 'postergado')
                                AND p.hora != '00:00:00'
                                AND NOW() >= DATE_SUB(CAST(CONCAT(p.fecha, ' ', p.hora) AS DATETIME), INTERVAL 15 MINUTE)
                                AND NOW() <= DATE_ADD(CAST(CONCAT(p.fecha, ' ', p.hora) AS DATETIME), INTERVAL 3 HOUR)
                           )
                        GROUP BY p.id
                        ORDER BY p.hora"
                    );
                }

                if (empty($partidos)) {
                    return $res->json([]);
                }

                $ids = array_map(static fn($p) => (int) $p['id'], $partidos);
                $placeholders = implode(',', array_fill(0, count($ids), '?'));

                $eventos = Database::fetchAll(
                    "SELECT e.partido_id, e.tipo, e.minuto, e.equipo_id,
                        COALESCE(j.nombre, '') AS jugador_nombre
                    FROM eventos e
                    LEFT JOIN jugadores j ON e.jugador_id = j.id
                    WHERE e.partido_id IN ($placeholders)
                    ORDER BY e.partido_id, e.minuto ASC, e.id ASC",
                    $ids
                );

                // Indexar eventos por partido_id
                $eventosByPartido = [];
                foreach ($eventos as $ev) {
                    $pid = (int) $ev['partido_id'];
                    if (!isset($eventosByPartido[$pid])) {
                        $eventosByPartido[$pid] = [];
                    }
                    $eventosByPartido[$pid][] = [
                        'tipo'           => $ev['tipo'],
                        'minuto'         => $ev['minuto'] !== null ? (int) $ev['minuto'] : null,
                        'jugador_nombre' => $ev['jugador_nombre'],
                        'equipo_id'      => (int) $ev['equipo_id'],
                    ];
                }

                // En modo manual (overrideIds): mostrar exactamente los partidos elegidos.
                // En modo automático: deduplicar por (local, visitante, fecha).
                if ($overrideIds) {
                    $result = array_map(static function (array $p) use ($eventosByPartido): array {
                        $normalized = normalizePartidoRecord($p);
                        $pid = (int) $p['id'];
                        $normalized['eventos'] = $eventosByPartido[$pid] ?? [];
                        return $normalized;
                    }, $partidos);
                } else {
                    $deduped = [];
                    foreach ($partidos as $p) {
                        $key = min((int)$p['localId'], (int)$p['visitanteId'])
                             . '_' . max((int)$p['localId'], (int)$p['visitanteId'])
                             . '_' . $p['fecha'];

                        if (!isset($deduped[$key])) {
                            $deduped[$key] = $p;
                            continue;
                        }

                        $existing = $deduped[$key];
                        $existingIsLive = $existing['estado'] === 'en_curso';
                        $newIsLive      = $p['estado'] === 'en_curso';

                        // Prioridad: en_curso > otro estado; empate → mayor id
                        if (
                            (!$existingIsLive && $newIsLive) ||
                            ($existingIsLive === $newIsLive && (int)$p['id'] > (int)$existing['id'])
                        ) {
                            $deduped[$key] = $p;
                        }
                    }

                    $result = array_map(static function (array $p) use ($eventosByPartido): array {
                        $normalized = normalizePartidoRecord($p);
                        $pid = (int) $p['id'];
                        $normalized['eventos'] = $eventosByPartido[$pid] ?? [];
                        return $normalized;
                    }, array_values($deduped));
                }

                $res->json(array_values($result));
            } catch (\Exception $e) {
                error_log('Error GET /partidos/live-scores: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al obtener marcadores en vivo']);
            }
        });

        $r->get('/proximo', function (Request $req, Response $res) {
            try {
                $categoria = trim((string) ($req->query['categoria'] ?? ''));
                $torneo = trim((string) ($req->query['torneo'] ?? ''));
                $configKey = buildNextPartidoConfigKey($categoria, $torneo);
                $overrideId = (int) (getHomeConfigValue($configKey) ?? 0);

                if ($overrideId <= 0 && $categoria !== '') {
                    $fallbackKey = buildNextPartidoConfigKey(null, $torneo);
                    $overrideId = (int) (getHomeConfigValue($fallbackKey) ?? 0);
                }

                if ($overrideId > 0) {
                    $override = fetchPartidoById($overrideId);
                    if ($override && strcasecmp((string) ($override['estado'] ?? ''), 'jugado') !== 0 && matchesPartidoFilters($override, $categoria, $torneo)) {
                        return $res->json(['partido' => $override, 'modo' => 'manual']);
                    }

                    setHomeConfigValue($configKey, null);
                }

                $partido = fetchNextPartido($categoria, $torneo, false);
                if (!$partido) {
                    $partido = fetchNextPartido($categoria, $torneo, true);
                }

                $res->json(['partido' => $partido ?: null, 'modo' => $partido ? 'automatico' : null]);
            } catch (\Exception $e) {
                error_log('Error GET /partidos/proximo: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al obtener el próximo partido']);
            }
        });

        $r->get('/jornadas', function (Request $req, Response $res) {
            try {
                $params = [];
                $where = [];
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

                $params = [$team1, $team2, $team2, $team1];
                $where = ['((p.local = ? AND p.visitante = ?) OR (p.local = ? AND p.visitante = ?))', "p.estado = 'jugado'"];
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
                    $enriched[] = normalizePartidoRecord($partido);
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
                $params = [];
                $where = [];
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

                $rows = Database::fetchAll($query, $params);
                $res->json(array_map('App\\Routes\\normalizePartidoRecord', $rows));
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
            $estadio = trim((string) ($body['estadio'] ?? ''));
            $local = (int) ($body['local'] ?? 0);
            $visitante = (int) ($body['visitante'] ?? 0);
            $estado = $body['estado'] ?? 'pendiente';
            $torneo = $body['torneo'] ?? '';
            $categoria = $body['categoria'] ?? '';
            $jornada = $body['jornada'] ?? null;
            $broadcast = $body['broadcast'] ?? null;

            if (!$fecha || !$hora || !$local || !$visitante || !$torneo || !$categoria) {
                return $res->status(400)->json(['error' => 'Faltan campos requeridos']);
            }
            if ($local === $visitante) {
                return $res->status(400)->json(['error' => 'Los equipos del partido son inválidos']);
            }

            try {
                $equiposById = fetchEquiposPartidoMeta([$local, $visitante]);
                if (!isset($equiposById[$local]) || !isset($equiposById[$visitante])) {
                    return $res->status(400)->json(['error' => 'Equipo local o visitante inválido']);
                }

                $estadio = resolvePartidoEstadio($estadio, $local, $equiposById);
                if ($estadio === '') {
                    return $res->status(400)->json(['error' => 'Completá el estadio del equipo local o cargá uno manualmente']);
                }

                Database::execute(
                    'INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
                    VALUES (?, ?, ?, ?, ?, ?, 0, 0, 0, 0, ?, ?, ?, ?)',
                    [$fecha, $hora, $estadio, $local, $visitante, $estado, $torneo, $categoria, $jornada, $broadcast]
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
                'jornada' => $partido['jornada'] ?? null,
                'broadcast' => $partido['broadcast'] ?? null,
            ], $input);

            foreach ($partidos as $partido) {
                if (!$partido['fecha'] || !$partido['hora'] || !$partido['torneo'] || !$partido['categoria']) {
                    return $res->status(400)->json(['error' => 'Todos los partidos requieren fecha, hora, torneo y categoría']);
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

                $equiposById = fetchEquiposPartidoMeta($allTeams);
                if (count($equiposById) !== count(array_unique($allTeams))) {
                    $db->rollBack();
                    return $res->status(400)->json(['error' => 'Uno o más equipos no existen']);
                }

                $created = [];
                foreach ($partidos as $partido) {
                    $estadio = resolvePartidoEstadio($partido['estadio'], $partido['local'], $equiposById);
                    if ($estadio === '') {
                        $db->rollBack();
                        return $res->status(400)->json(['error' => 'Cada equipo local debe tener estadio o el partido debe informarlo manualmente']);
                    }

                    Database::execute(
                        'INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
                        VALUES (?, ?, ?, ?, ?, ?, 0, 0, 0, 0, ?, ?, ?, ?)',
                        [
                            $partido['fecha'],
                            $partido['hora'],
                            $estadio,
                            $partido['local'],
                            $partido['visitante'],
                            $partido['estado'],
                            $partido['torneo'],
                            $partido['categoria'],
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

        $r->put('/proximo', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $partidoId = (int) ($req->body['partidoId'] ?? 0);
            if ($partidoId <= 0) {
                return $res->status(400)->json(['error' => 'Seleccioná un partido válido']);
            }

            try {
                $partido = fetchPartidoById($partidoId);
                if (!$partido) {
                    return $res->status(404)->json(['error' => 'Partido no encontrado']);
                }
                if (strcasecmp((string) ($partido['estado'] ?? ''), 'jugado') === 0) {
                    return $res->status(400)->json(['error' => 'No se puede destacar un partido ya jugado']);
                }

                $categoria = trim((string) ($req->body['categoria'] ?? ''));
                if ($categoria === '') {
                    $categoria = trim((string) ($partido['categoria'] ?? ''));
                }

                $torneo = trim((string) ($req->body['torneo'] ?? ''));
                if ($torneo === '') {
                    $torneo = trim((string) ($partido['torneo'] ?? ''));
                }

                $configKey = buildNextPartidoConfigKey($categoria, $torneo);
                setHomeConfigValue($configKey, (string) $partidoId);

                $res->json(['ok' => true, 'partido' => $partido, 'modo' => 'manual']);
            } catch (\Exception $e) {
                error_log('Error PUT /partidos/proximo: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al guardar el próximo partido']);
            }
        });

        $r->delete('/proximo', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            try {
                $categoria = trim((string) ($req->query['categoria'] ?? ''));
                $torneo = trim((string) ($req->query['torneo'] ?? ''));
                $configKey = buildNextPartidoConfigKey($categoria, $torneo);
                setHomeConfigValue($configKey, null);
                $res->json(['ok' => true]);
            } catch (\Exception $e) {
                error_log('Error DELETE /partidos/proximo: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al limpiar el próximo partido manual']);
            }
        });

        $r->get('/live-scores-config', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            try {
                $value = getHomeConfigValue('live_scores_widget_enabled');
                $enabled = $value === null || $value !== '0';
                $overrideRaw = getHomeConfigValue('live_scores_override_ids');
                $overrideIds = ($overrideRaw !== null && $overrideRaw !== '')
                    ? array_values(array_filter(array_map('intval', explode(',', $overrideRaw)), static fn(int $id) => $id > 0))
                    : [];
                $res->json(['enabled' => $enabled, 'overrideIds' => $overrideIds]);
            } catch (\Exception $e) {
                error_log('Error GET /partidos/live-scores-config: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al leer configuración del widget']);
            }
        });

        $r->put('/live-scores-config', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            try {
                $body = $req->body;
                if (array_key_exists('enabled', $body)) {
                    $en = filter_var($body['enabled'], FILTER_VALIDATE_BOOLEAN);
                    setHomeConfigValue('live_scores_widget_enabled', $en ? '1' : '0');
                }
                if (array_key_exists('overrideIds', $body)) {
                    $rawIds = $body['overrideIds'];
                    if (empty($rawIds)) {
                        setHomeConfigValue('live_scores_override_ids', null);
                    } else {
                        $ids = array_values(array_filter(array_map('intval', (array) $rawIds), static fn(int $id) => $id > 0));
                        setHomeConfigValue('live_scores_override_ids', $ids ? implode(',', $ids) : null);
                    }
                }
                $enabledVal = getHomeConfigValue('live_scores_widget_enabled');
                $currentEnabled = $enabledVal === null || $enabledVal !== '0';
                $overrideRaw = getHomeConfigValue('live_scores_override_ids');
                $currentOverrideIds = ($overrideRaw !== null && $overrideRaw !== '')
                    ? array_values(array_filter(array_map('intval', explode(',', $overrideRaw)), static fn(int $id) => $id > 0))
                    : [];
                $res->json(['enabled' => $currentEnabled, 'overrideIds' => $currentOverrideIds]);
            } catch (\Exception $e) {
                error_log('Error PUT /partidos/live-scores-config: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al guardar configuración del widget']);
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
            if (!is_array($eventos)) {
                return $res->status(400)->json(['error' => 'Los eventos del partido son inválidos']);
            }

            $validation = validatePartidoEventos($id, $eventos);
            if (!empty($validation['error'])) {
                return $res->status(400)->json(['error' => $validation['error']]);
            }

            $eventos = $validation['eventos'];

            $amarillas = count(array_filter($eventos, static fn(array $evento) => ($evento['tipo'] ?? null) === 'amarilla'));
            $rojas = count(array_filter($eventos, static fn(array $evento) => ($evento['tipo'] ?? null) === 'roja'));

            // Determinar estado según parámetro 'finalizar'
            $finalizar = $req->body['finalizar'] ?? true; // default true para compatibilidad
            $nuevoEstado = $finalizar ? 'jugado' : 'en_curso';

            $db = Database::getConnection();
            try {
                $db->beginTransaction();

                Database::execute(
                    "UPDATE partidos SET goleslocal = ?, golesvisitante = ?, amarillas = ?, rojas = ?, estado = ? WHERE id = ?",
                    [$golesLocal, $golesVisitante, $amarillas, $rojas, $nuevoEstado, $id]
                );
                Database::execute('DELETE FROM eventos WHERE partido_id = ?', [$id]);

                $createdEventos = [];
                foreach ($eventos as $evento) {
                    Database::execute(
                        'INSERT INTO eventos (partido_id, jugador_id, equipo_id, tipo, minuto) VALUES (?, ?, ?, ?, ?)',
                        [$id, $evento['jugador_id'] ?? null, $evento['equipo_id'] ?? null, $evento['tipo'], $evento['minuto'] ?? null]
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
        p.torneo, p.categoria, p.jornada, p.broadcast,
        el.nombre AS local, ev.nombre AS visitante,
        el.escudo AS localEscudo, ev.escudo AS visitanteEscudo
    FROM partidos p
    LEFT JOIN equipos el ON p.local = el.id
    LEFT JOIN equipos ev ON p.visitante = ev.id";
}

function fetchPartidoById(int $id): array|false {
    $row = Database::fetchOne(buildPartidosSelect() . ' WHERE p.id = ?', [$id]);
    return $row ? normalizePartidoRecord($row) : false;
}

function fetchPartidoEventoById(int $id): array|false {
    return Database::fetchOne('SELECT id, partido_id, jugador_id, equipo_id, tipo, minuto FROM eventos WHERE id = ?', [$id]);
}