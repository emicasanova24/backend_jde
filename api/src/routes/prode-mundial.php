<?php
/**
 * prode-mundial.php — Rutas del Prode del Mundial 2026.
 * Montado bajo el prefijo /prode-mundial.
 */
namespace App\Routes;

use App\Router;
use App\Database;
use App\Request;
use App\Response;

use function App\Middleware\authMiddleware;
use function App\ensureProdeTables;
use function App\ensureProdeMundialTables;
use function App\prodeAuthMiddleware;

require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../seed.php';
require_once __DIR__ . '/../ProdeAuth.php';

// ---------------------------------------------------------------------------
// Helpers internos
// ---------------------------------------------------------------------------

/**
 * Obtiene la ronda activa del mundial:
 * la primera ronda con estado='abierta' que tenga al menos un partido pendiente.
 * Devuelve array con datos de la ronda, o null si no hay.
 */
function getRondaActivaMundial(): ?array
{
    return Database::fetchOne(
        "SELECT r.id, r.nombre, r.fase, r.estado, r.puntos_calculados, r.closed_at
         FROM mundial_rondas r
         WHERE r.estado = 'abierta'
           AND EXISTS (
               SELECT 1 FROM mundial_partidos p
               WHERE p.ronda_id = r.id AND p.estado = 'pendiente'
           )
         ORDER BY r.id ASC
         LIMIT 1"
    );
}

/**
 * Calcula el deadline de la ronda activa:
 * 1 hora antes del primer partido pendiente en la ronda.
 * Devuelve ISO string UTC o null.
 */
function calcularDeadlineMundial(?int $rondaId): ?string
{
    if (!$rondaId) return null;

    $primer = Database::fetchOne(
        "SELECT fecha, hora FROM mundial_partidos
         WHERE ronda_id = ? AND estado = 'pendiente'
         ORDER BY fecha ASC, hora ASC
         LIMIT 1",
        [$rondaId]
    );

    if (!$primer) return null;

    $dt = new \DateTime($primer['fecha'] . ' ' . $primer['hora'], new \DateTimeZone('America/Argentina/Buenos_Aires'));
    $dt->modify('-1 hour');
    $dt->setTimezone(new \DateTimeZone('UTC'));
    return $dt->format('Y-m-d\TH:i:s\Z');
}

function deadlinePasadoMundial(?string $deadline): bool
{
    if (!$deadline) return false;
    $now = new \DateTime('now', new \DateTimeZone('UTC'));
    $dl  = new \DateTime($deadline, new \DateTimeZone('UTC'));
    return $now >= $dl;
}

// ---------------------------------------------------------------------------
// Registro de rutas
// ---------------------------------------------------------------------------

function registerProdeMundialRoutes(Router $router): void
{
    $router->group('/prode-mundial', function (Router $r) {

        // -------------------------------------------------------------------
        // GET /prode-mundial/config
        // -------------------------------------------------------------------
        $r->get('/config', function (Request $req, Response $res) {
            ensureProdeMundialTables();
            $ronda = getRondaActivaMundial();
            $deadline = $ronda ? calcularDeadlineMundial((int) $ronda['id']) : null;
            $enabledVal = getHomeConfigValue('mundial_enabled');
            $enabled    = ($enabledVal === null || $enabledVal === '1');
            return $res->json([
                'enabled'       => $enabled,
                'mundial_activo' => !empty($ronda),
                'ronda'    => $ronda,
                'deadline' => $deadline,
                'bloqueado'=> deadlinePasadoMundial($deadline),
            ]);
        });

        // -------------------------------------------------------------------
        // GET /prode-mundial/ronda-activa
        // -------------------------------------------------------------------
        $r->get('/ronda-activa', function (Request $req, Response $res) {
            ensureProdeMundialTables();

            $ronda = getRondaActivaMundial();
            if (!$ronda) {
                return $res->json(['ronda' => null, 'partidos' => [], 'bloqueado' => false, 'deadline' => null]);
            }

            $deadline  = calcularDeadlineMundial((int) $ronda['id']);
            $bloqueado = deadlinePasadoMundial($deadline);

            $partidos = Database::fetchAll(
                "SELECT p.id, p.fecha, p.hora, p.estadio, p.estado, p.fase, p.grupo,
                        p.goles_local AS golesLocal, p.goles_visitante AS golesVisitante,
                        el.id AS local_id, el.nombre AS local, el.codigo_pais AS localBandera,
                        ev.id AS visitante_id, ev.nombre AS visitante, ev.codigo_pais AS visitanteBandera
                 FROM mundial_partidos p
                 JOIN mundial_equipos el ON el.id = p.local_id
                 JOIN mundial_equipos ev ON ev.id = p.visitante_id
                 WHERE p.ronda_id = ?
                 ORDER BY p.fecha ASC, p.hora ASC",
                [(int) $ronda['id']]
            );

            return $res->json([
                'ronda'    => $ronda,
                'deadline' => $deadline,
                'bloqueado'=> $bloqueado,
                'partidos' => array_values($partidos),
            ]);
        });

        // -------------------------------------------------------------------
        // GET /prode-mundial/ranking/publico  (top 50, sin auth)
        // -------------------------------------------------------------------
        $r->get('/ranking/publico', function (Request $req, Response $res) {
            ensureProdeMundialTables();
            ensureProdeTables();

            $limit = max(1, min(100, (int) ($req->query['limit'] ?? 50)));

            $rows = Database::fetchAll(
                "SELECT p.alias,
                        COALESCE(SUM(mp.puntos_obtenidos), 0) AS puntos_total,
                        COUNT(mp.id) AS pronosticos_count,
                        SUM(CASE WHEN mp.puntos_obtenidos >= 4 THEN 1 ELSE 0 END) AS aciertos_exactos,
                        SUM(CASE WHEN mp.puntos_obtenidos > 0 THEN 1 ELSE 0 END) AS aciertos_resultado
                 FROM prode_participantes p
                 LEFT JOIN mundial_pronosticos mp ON mp.participante_id = p.id
                    AND mp.puntos_obtenidos IS NOT NULL
                 WHERE EXISTS (SELECT 1 FROM mundial_pronosticos WHERE participante_id = p.id)
                 GROUP BY p.id, p.alias
                 ORDER BY puntos_total DESC, aciertos_exactos DESC, pronosticos_count DESC
                 LIMIT ?",
                [$limit]
            );

            $ranking = [];
            foreach ($rows as $i => $row) {
                $ranking[] = [
                    'posicion'           => $i + 1,
                    'alias'              => $row['alias'],
                    'puntos_total'       => (int) $row['puntos_total'],
                    'pronosticos_count'  => (int) $row['pronosticos_count'],
                    'aciertos_exactos'   => (int) $row['aciertos_exactos'],
                    'aciertos_resultado' => (int) $row['aciertos_resultado'],
                ];
            }

            return $res->json(['ranking' => $ranking]);
        });

        // -------------------------------------------------------------------
        // GET /prode-mundial/pronosticos  [requiere X-Prode-Token]
        // -------------------------------------------------------------------
        $r->get('/pronosticos', function (Request $req, Response $res) {
            if (!prodeAuthMiddleware($req, $res)) return;
            ensureProdeMundialTables();

            $rondaId = isset($req->query['ronda_id']) ? (int) $req->query['ronda_id'] : null;

            if (!$rondaId) {
                $ronda = getRondaActivaMundial();
                $rondaId = $ronda ? (int) $ronda['id'] : null;
            }

            if (!$rondaId) {
                return $res->json(['ronda_id' => null, 'pronosticos' => [], 'total_puntos' => 0]);
            }

            $participanteId = $req->prodeUser->id;

            $rows = Database::fetchAll(
                "SELECT mp.partido_id, mp.goles_local, mp.goles_visitante, mp.puntos_obtenidos,
                        p.estado, p.goles_local AS golesLocalReal, p.goles_visitante AS golesVisitanteReal,
                        el.nombre AS local, ev.nombre AS visitante,
                        el.codigo_pais AS localBandera, ev.codigo_pais AS visitanteBandera,
                        p.fecha, p.hora, p.grupo, p.fase
                 FROM mundial_pronosticos mp
                 JOIN mundial_partidos p ON p.id = mp.partido_id
                 JOIN mundial_equipos el ON el.id = p.local_id
                 JOIN mundial_equipos ev ON ev.id = p.visitante_id
                 WHERE mp.participante_id = ? AND p.ronda_id = ?
                 ORDER BY p.fecha ASC, p.hora ASC",
                [$participanteId, $rondaId]
            );

            $totalPuntos = 0;
            foreach ($rows as $row) {
                if ($row['puntos_obtenidos'] !== null) {
                    $totalPuntos += (int) $row['puntos_obtenidos'];
                }
            }

            return $res->json([
                'ronda_id'     => $rondaId,
                'pronosticos'  => array_values($rows),
                'total_puntos' => $totalPuntos,
            ]);
        });

        // -------------------------------------------------------------------
        // POST /prode-mundial/pronosticos  [requiere X-Prode-Token]
        // -------------------------------------------------------------------
        $r->post('/pronosticos', function (Request $req, Response $res) {
            if (!prodeAuthMiddleware($req, $res)) return;
            ensureProdeMundialTables();

            $pronosticos = $req->body['pronosticos'] ?? [];
            if (!is_array($pronosticos) || count($pronosticos) === 0) {
                return $res->status(400)->json(['error' => 'pronosticos debe ser un array no vacío']);
            }

            foreach ($pronosticos as $idx => $pron) {
                $gl  = $pron['goles_local']     ?? null;
                $gv  = $pron['goles_visitante'] ?? null;
                $pid = $pron['partido_id']       ?? null;
                if ($pid === null || $gl === null || $gv === null) {
                    return $res->status(400)->json(['error' => "Pronóstico #{$idx}: partido_id, goles_local y goles_visitante son requeridos"]);
                }
                if (!is_numeric($gl) || !is_numeric($gv) || (int) $gl < 0 || (int) $gv < 0) {
                    return $res->status(400)->json(['error' => "Pronóstico #{$idx}: los goles deben ser números no negativos"]);
                }
            }

            $partidoIds   = array_unique(array_map(fn($p) => (int) $p['partido_id'], $pronosticos));
            $placeholders = implode(',', array_fill(0, count($partidoIds), '?'));

            $partidos = Database::fetchAll(
                "SELECT id, ronda_id, estado FROM mundial_partidos WHERE id IN ($placeholders)",
                $partidoIds
            );

            if (count($partidos) !== count($partidoIds)) {
                return $res->status(400)->json(['error' => 'Uno o más partidos no encontrados']);
            }

            // Todos deben ser de la misma ronda
            $rondaIds = array_unique(array_column($partidos, 'ronda_id'));
            if (count($rondaIds) !== 1) {
                return $res->status(400)->json(['error' => 'Todos los pronósticos deben ser de la misma ronda']);
            }

            $rondaId = (int) $rondaIds[0];
            $deadline = calcularDeadlineMundial($rondaId);
            if ($deadline && deadlinePasadoMundial($deadline)) {
                return $res->status(403)->json(['error' => 'El plazo para cargar pronósticos ya cerró']);
            }

            // Verificar que la ronda esté abierta
            $ronda = Database::fetchOne("SELECT estado FROM mundial_rondas WHERE id = ?", [$rondaId]);
            if ($ronda && $ronda['estado'] === 'cerrada') {
                return $res->status(403)->json(['error' => 'Esta ronda ya fue cerrada']);
            }

            $participanteId = $req->prodeUser->id;
            $guardados = 0;

            foreach ($pronosticos as $pron) {
                $partidoId = (int) $pron['partido_id'];
                $gl        = (int) $pron['goles_local'];
                $gv        = (int) $pron['goles_visitante'];

                Database::execute(
                    "INSERT INTO mundial_pronosticos
                        (participante_id, partido_id, goles_local, goles_visitante)
                     VALUES (?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE
                        goles_local = VALUES(goles_local),
                        goles_visitante = VALUES(goles_visitante),
                        updated_at = NOW()",
                    [$participanteId, $partidoId, $gl, $gv]
                );
                $guardados++;
            }

            return $res->json(['guardados' => $guardados, 'mensaje' => 'Pronósticos guardados correctamente']);
        });

        // -------------------------------------------------------------------
        // GET /prode-mundial/ranking  [requiere X-Prode-Token — ranking personal]
        // -------------------------------------------------------------------
        $r->get('/ranking', function (Request $req, Response $res) {
            if (!prodeAuthMiddleware($req, $res)) return;
            ensureProdeMundialTables();
            ensureProdeTables();

            $limit = max(1, min(100, (int) ($req->query['limit'] ?? 50)));

            $rows = Database::fetchAll(
                "SELECT p.alias,
                        COALESCE(SUM(mp.puntos_obtenidos), 0) AS puntos_total,
                        COUNT(mp.id) AS pronosticos_count,
                        SUM(CASE WHEN mp.puntos_obtenidos >= 4 THEN 1 ELSE 0 END) AS aciertos_exactos,
                        SUM(CASE WHEN mp.puntos_obtenidos > 0 THEN 1 ELSE 0 END) AS aciertos_resultado
                 FROM prode_participantes p
                 LEFT JOIN mundial_pronosticos mp ON mp.participante_id = p.id
                    AND mp.puntos_obtenidos IS NOT NULL
                 WHERE EXISTS (SELECT 1 FROM mundial_pronosticos WHERE participante_id = p.id)
                 GROUP BY p.id, p.alias
                 ORDER BY puntos_total DESC, aciertos_exactos DESC, pronosticos_count DESC
                 LIMIT ?",
                [$limit]
            );

            $alias   = $req->prodeUser->alias;
            $ranking = [];
            $miPosicion = null;
            foreach ($rows as $i => $row) {
                $pos = $i + 1;
                if ($row['alias'] === $alias) {
                    $miPosicion = $pos;
                }
                $ranking[] = [
                    'posicion'           => $pos,
                    'alias'              => $row['alias'],
                    'puntos_total'       => (int) $row['puntos_total'],
                    'pronosticos_count'  => (int) $row['pronosticos_count'],
                    'aciertos_exactos'   => (int) $row['aciertos_exactos'],
                    'aciertos_resultado' => (int) $row['aciertos_resultado'],
                    'es_yo'              => ($row['alias'] === $alias),
                ];
            }

            return $res->json(['ranking' => $ranking, 'mi_posicion' => $miPosicion]);
        });

        // ===================================================================
        // RUTAS ADMIN (requieren JWT)
        // ===================================================================

        // -------------------------------------------------------------------
        // PUT /prode-mundial/admin/config — activar/desactivar botón en el header
        // -------------------------------------------------------------------
        $r->put('/admin/config', function (Request $req, Response $res) {
            authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $enabled = isset($req->body['enabled']) ? (bool) $req->body['enabled'] : true;
            setHomeConfigValue('mundial_enabled', $enabled ? '1' : '0');
            return $res->json(['enabled' => $enabled]);
        });

        // -------------------------------------------------------------------
        // GET /prode-mundial/admin/equipos
        // -------------------------------------------------------------------
        $r->get('/admin/equipos', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            ensureProdeMundialTables();
            $rows = Database::fetchAll(
                "SELECT id, nombre, codigo_pais, grupo FROM mundial_equipos ORDER BY grupo ASC, nombre ASC"
            );
            return $res->json(['equipos' => $rows, 'total' => count($rows)]);
        });

        // -------------------------------------------------------------------
        // POST /prode-mundial/admin/equipos/seed  — importar 48 selecciones
        // -------------------------------------------------------------------
        $r->post('/admin/equipos/seed', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            ensureProdeMundialTables();

            $jsonPath = __DIR__ . '/../data/mundial2026.json';
            if (!file_exists($jsonPath)) {
                return $res->status(500)->json(['error' => 'Archivo mundial2026.json no encontrado']);
            }

            $equipos = json_decode(file_get_contents($jsonPath), true);
            if (!is_array($equipos)) {
                return $res->status(500)->json(['error' => 'Formato inválido en mundial2026.json']);
            }

            $insertados = 0;
            $actualizados = 0;

            foreach ($equipos as $e) {
                $nombre     = trim($e['nombre']      ?? '');
                $codigo     = trim($e['codigo_pais'] ?? '');
                $grupo      = strtoupper(trim($e['grupo'] ?? '')) ?: null;

                if (!$nombre || !$codigo) continue;

                $exists = Database::fetchOne(
                    "SELECT id FROM mundial_equipos WHERE nombre = ?",
                    [$nombre]
                );

                if ($exists) {
                    Database::execute(
                        "UPDATE mundial_equipos SET codigo_pais = ?, grupo = ? WHERE nombre = ?",
                        [$codigo, $grupo, $nombre]
                    );
                    $actualizados++;
                } else {
                    Database::execute(
                        "INSERT INTO mundial_equipos (nombre, codigo_pais, grupo) VALUES (?, ?, ?)",
                        [$nombre, $codigo, $grupo]
                    );
                    $insertados++;
                }
            }

            return $res->json([
                'insertados'   => $insertados,
                'actualizados' => $actualizados,
                'total'        => $insertados + $actualizados,
                'mensaje'      => 'Selecciones importadas correctamente',
            ]);
        });

        // -------------------------------------------------------------------
        // GET /prode-mundial/admin/rondas
        // -------------------------------------------------------------------
        $r->get('/admin/rondas', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            ensureProdeMundialTables();

            $rondas = Database::fetchAll(
                "SELECT r.id, r.nombre, r.fase, r.estado, r.puntos_calculados, r.closed_at,
                        COUNT(p.id) AS partidos_total,
                        SUM(CASE WHEN p.estado = 'pendiente' THEN 1 ELSE 0 END) AS partidos_pendientes,
                        SUM(CASE WHEN p.estado = 'jugado'    THEN 1 ELSE 0 END) AS partidos_jugados
                 FROM mundial_rondas r
                 LEFT JOIN mundial_partidos p ON p.ronda_id = r.id
                 GROUP BY r.id, r.nombre, r.fase, r.estado, r.puntos_calculados, r.closed_at
                 ORDER BY r.id ASC"
            );

            return $res->json(['rondas' => $rondas]);
        });

        // -------------------------------------------------------------------
        // POST /prode-mundial/admin/rondas — crear ronda
        // -------------------------------------------------------------------
        $r->post('/admin/rondas', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            ensureProdeMundialTables();

            $nombre = trim($req->body['nombre'] ?? '');
            $fase   = $req->body['fase']   ?? 'grupos';

            if (!$nombre) {
                return $res->status(400)->json(['error' => 'El nombre es requerido']);
            }

            $fasesValidas = ['grupos','octavos','cuartos','semifinal','tercer_puesto','final'];
            if (!in_array($fase, $fasesValidas, true)) {
                return $res->status(400)->json(['error' => 'Fase inválida']);
            }

            Database::execute(
                "INSERT INTO mundial_rondas (nombre, fase) VALUES (?, ?)",
                [$nombre, $fase]
            );

            $id = Database::lastInsertId();
            return $res->status(201)->json(['id' => $id, 'nombre' => $nombre, 'fase' => $fase, 'estado' => 'abierta']);
        });

        // -------------------------------------------------------------------
        // PUT /prode-mundial/admin/rondas/:id — editar nombre/fase
        // -------------------------------------------------------------------
        $r->put('/admin/rondas/:id', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            ensureProdeMundialTables();

            $id     = (int) $req->params['id'];
            $nombre = trim($req->body['nombre'] ?? '');
            $fase   = $req->body['fase'] ?? null;

            $ronda = Database::fetchOne("SELECT id FROM mundial_rondas WHERE id = ?", [$id]);
            if (!$ronda) {
                return $res->status(404)->json(['error' => 'Ronda no encontrada']);
            }

            if ($nombre) {
                Database::execute("UPDATE mundial_rondas SET nombre = ? WHERE id = ?", [$nombre, $id]);
            }
            if ($fase) {
                $fasesValidas = ['grupos','octavos','cuartos','semifinal','tercer_puesto','final'];
                if (!in_array($fase, $fasesValidas, true)) {
                    return $res->status(400)->json(['error' => 'Fase inválida']);
                }
                Database::execute("UPDATE mundial_rondas SET fase = ? WHERE id = ?", [$fase, $id]);
            }

            return $res->json(['mensaje' => 'Ronda actualizada']);
        });

        // -------------------------------------------------------------------
        // DELETE /prode-mundial/admin/rondas/:id — eliminar ronda
        // -------------------------------------------------------------------
        $r->delete('/admin/rondas/:id', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            ensureProdeMundialTables();

            $id = (int) $req->params['id'];

            $ronda = Database::fetchOne("SELECT id, nombre FROM mundial_rondas WHERE id = ?", [$id]);
            if (!$ronda) {
                return $res->status(404)->json(['error' => 'Ronda no encontrada']);
            }

            // Eliminar pronósticos y partidos asociados primero
            $partidos = Database::fetchAll("SELECT id FROM mundial_partidos WHERE ronda_id = ?", [$id]);
            foreach ($partidos as $p) {
                Database::execute("DELETE FROM mundial_pronosticos WHERE partido_id = ?", [(int) $p['id']]);
            }
            Database::execute("DELETE FROM mundial_partidos WHERE ronda_id = ?", [$id]);
            Database::execute("DELETE FROM mundial_rondas WHERE id = ?", [$id]);

            return $res->json(['mensaje' => 'Ronda eliminada']);
        });

        // -------------------------------------------------------------------
        // POST /prode-mundial/admin/rondas/:id/cerrar — calcular puntos
        // -------------------------------------------------------------------
        $r->post('/admin/rondas/:id/cerrar', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            ensureProdeMundialTables();

            $id = (int) $req->params['id'];

            $ronda = Database::fetchOne("SELECT * FROM mundial_rondas WHERE id = ?", [$id]);
            if (!$ronda) {
                return $res->status(404)->json(['error' => 'Ronda no encontrada']);
            }

            // Obtener todos los partidos jugados de la ronda
            $partidos = Database::fetchAll(
                "SELECT id, goles_local, goles_visitante, estado
                 FROM mundial_partidos
                 WHERE ronda_id = ? AND estado = 'jugado'",
                [$id]
            );

            $actualizados = 0;

            foreach ($partidos as $partido) {
                $pid = (int) $partido['id'];
                $glReal = (int) $partido['goles_local'];
                $gvReal = (int) $partido['goles_visitante'];
                $resReal = ($glReal > $gvReal) ? 'local' : ($glReal < $gvReal ? 'visitante' : 'empate');

                // Todos los pronósticos para este partido
                $pronosticos = Database::fetchAll(
                    "SELECT id, goles_local, goles_visitante
                     FROM mundial_pronosticos
                     WHERE partido_id = ?",
                    [$pid]
                );

                foreach ($pronosticos as $pron) {
                    $gl = (int) $pron['goles_local'];
                    $gv = (int) $pron['goles_visitante'];
                    $resPron = ($gl > $gv) ? 'local' : ($gl < $gv ? 'visitante' : 'empate');

                    $puntos = 0;
                    if ($resPron === $resReal) {
                        $puntos += 1; // acertó resultado
                        if ($gl === $glReal && $gv === $gvReal) {
                            $puntos += 3; // acertó marcador exacto
                        }
                    }

                    Database::execute(
                        "UPDATE mundial_pronosticos SET puntos_obtenidos = ? WHERE id = ?",
                        [$puntos, (int) $pron['id']]
                    );
                    $actualizados++;
                }
            }

            // Marcar ronda cerrada
            Database::execute(
                "UPDATE mundial_rondas SET estado = 'cerrada', puntos_calculados = 1, closed_at = NOW() WHERE id = ?",
                [$id]
            );

            return $res->json([
                'mensaje'         => 'Ronda cerrada y puntos calculados',
                'pronosticos_actualizados' => $actualizados,
            ]);
        });

        // -------------------------------------------------------------------
        // GET /prode-mundial/admin/partidos — listar partidos (con filtro ronda)
        // -------------------------------------------------------------------
        $r->get('/admin/partidos', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            ensureProdeMundialTables();

            $rondaId = isset($req->query['ronda_id']) ? (int) $req->query['ronda_id'] : null;

            $where  = $rondaId ? "WHERE p.ronda_id = ?" : "";
            $params = $rondaId ? [$rondaId] : [];

            $rows = Database::fetchAll(
                "SELECT p.id, p.ronda_id, p.fase, p.grupo, p.fecha, p.hora, p.estadio, p.estado,
                        p.goles_local AS golesLocal, p.goles_visitante AS golesVisitante,
                        el.id AS local_id, el.nombre AS local, el.codigo_pais AS localBandera,
                        ev.id AS visitante_id, ev.nombre AS visitante, ev.codigo_pais AS visitanteBandera
                 FROM mundial_partidos p
                 JOIN mundial_equipos el ON el.id = p.local_id
                 JOIN mundial_equipos ev ON ev.id = p.visitante_id
                 $where
                 ORDER BY p.fecha ASC, p.hora ASC",
                $params
            );

            return $res->json(['partidos' => $rows, 'total' => count($rows)]);
        });

        // -------------------------------------------------------------------
        // POST /prode-mundial/admin/partidos — crear partido
        // -------------------------------------------------------------------
        $r->post('/admin/partidos', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            ensureProdeMundialTables();

            $rondaId    = isset($req->body['ronda_id'])    ? (int) $req->body['ronda_id']    : null;
            $fase       = $req->body['fase']               ?? 'grupos';
            $grupo      = strtoupper(trim($req->body['grupo'] ?? '')) ?: null;
            $localId    = isset($req->body['local_id'])    ? (int) $req->body['local_id']    : null;
            $visitanteId= isset($req->body['visitante_id'])? (int) $req->body['visitante_id']: null;
            $fecha      = trim($req->body['fecha']         ?? '');
            $hora       = trim($req->body['hora']          ?? '00:00');
            $estadio    = trim($req->body['estadio']       ?? '');

            if (!$localId || !$visitanteId || !$fecha) {
                return $res->status(400)->json(['error' => 'local_id, visitante_id y fecha son requeridos']);
            }

            $fasesValidas = ['grupos','octavos','cuartos','semifinal','tercer_puesto','final'];
            if (!in_array($fase, $fasesValidas, true)) {
                return $res->status(400)->json(['error' => 'Fase inválida']);
            }

            Database::execute(
                "INSERT INTO mundial_partidos
                    (ronda_id, fase, grupo, local_id, visitante_id, fecha, hora, estadio)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [$rondaId, $fase, $grupo, $localId, $visitanteId, $fecha, $hora, $estadio]
            );

            $id = Database::lastInsertId();
            return $res->status(201)->json(['id' => $id, 'mensaje' => 'Partido creado']);
        });

        // -------------------------------------------------------------------
        // PUT /prode-mundial/admin/partidos/:id — editar datos del partido
        // -------------------------------------------------------------------
        $r->put('/admin/partidos/:id', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            ensureProdeMundialTables();

            $id = (int) $req->params['id'];

            $partido = Database::fetchOne("SELECT id FROM mundial_partidos WHERE id = ?", [$id]);
            if (!$partido) {
                return $res->status(404)->json(['error' => 'Partido no encontrado']);
            }

            $fields = [];
            $params = [];

            if (isset($req->body['local_id'])) {
                $fields[] = 'local_id = ?';
                $params[] = (int) $req->body['local_id'];
            }
            if (isset($req->body['visitante_id'])) {
                $fields[] = 'visitante_id = ?';
                $params[] = (int) $req->body['visitante_id'];
            }
            if (isset($req->body['fecha'])) {
                $fields[] = 'fecha = ?';
                $params[] = $req->body['fecha'];
            }
            if (isset($req->body['hora'])) {
                $fields[] = 'hora = ?';
                $params[] = $req->body['hora'];
            }
            if (array_key_exists('grupo', $req->body)) {
                $fields[] = 'grupo = ?';
                $params[] = strtoupper(trim($req->body['grupo'])) ?: null;
            }
            if (isset($req->body['estadio'])) {
                $fields[] = 'estadio = ?';
                $params[] = trim($req->body['estadio']);
            }
            if (isset($req->body['fase'])) {
                $fasesValidas = ['grupos','octavos','cuartos','semifinal','tercer_puesto','final'];
                if (!in_array($req->body['fase'], $fasesValidas, true)) {
                    return $res->status(400)->json(['error' => 'Fase inválida']);
                }
                $fields[] = 'fase = ?';
                $params[] = $req->body['fase'];
            }

            if (empty($fields)) {
                return $res->status(400)->json(['error' => 'Nada que actualizar']);
            }

            $params[] = $id;
            Database::execute("UPDATE mundial_partidos SET " . implode(', ', $fields) . " WHERE id = ?", $params);

            return $res->json(['mensaje' => 'Partido actualizado']);
        });

        // -------------------------------------------------------------------
        // DELETE /prode-mundial/admin/partidos/:id — eliminar partido
        // -------------------------------------------------------------------
        $r->delete('/admin/partidos/:id', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            ensureProdeMundialTables();

            $id = (int) $req->params['id'];

            $partido = Database::fetchOne("SELECT id FROM mundial_partidos WHERE id = ?", [$id]);
            if (!$partido) {
                return $res->status(404)->json(['error' => 'Partido no encontrado']);
            }

            Database::execute("DELETE FROM mundial_pronosticos WHERE partido_id = ?", [$id]);
            Database::execute("DELETE FROM mundial_partidos WHERE id = ?", [$id]);

            return $res->json(['mensaje' => 'Partido eliminado']);
        });

        // -------------------------------------------------------------------
        // PUT /prode-mundial/admin/partidos/:id/resultado — cargar resultado
        // -------------------------------------------------------------------
        $r->put('/admin/partidos/:id/resultado', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            ensureProdeMundialTables();

            $id = (int) $req->params['id'];
            $gl = isset($req->body['goles_local'])     ? (int) $req->body['goles_local']     : null;
            $gv = isset($req->body['goles_visitante']) ? (int) $req->body['goles_visitante'] : null;

            if ($gl === null || $gv === null || $gl < 0 || $gv < 0) {
                return $res->status(400)->json(['error' => 'goles_local y goles_visitante son requeridos y deben ser ≥ 0']);
            }

            $partido = Database::fetchOne("SELECT id FROM mundial_partidos WHERE id = ?", [$id]);
            if (!$partido) {
                return $res->status(404)->json(['error' => 'Partido no encontrado']);
            }

            Database::execute(
                "UPDATE mundial_partidos SET goles_local = ?, goles_visitante = ?, estado = 'jugado' WHERE id = ?",
                [$gl, $gv, $id]
            );

            return $res->json(['mensaje' => 'Resultado guardado', 'goles_local' => $gl, 'goles_visitante' => $gv]);
        });
    });
}
