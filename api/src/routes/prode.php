<?php
namespace App\Routes;

use App\Router;
use App\Database;
use App\Request;
use App\Response;

use function App\Middleware\authMiddleware;
use function App\ensureProdeTables;
use function App\prodeAuthMiddleware;

require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../seed.php';
require_once __DIR__ . '/../ProdeAuth.php';

// ---------------------------------------------------------------------------
// Helpers internos
// ---------------------------------------------------------------------------

/**
 * Valida formato de email.
 */
function validarEmail(string $email): bool
{
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL) && mb_strlen($email) <= 255;
}

/**
 * Obtiene la jornada activa de Primera División:
 * la próxima con partidos pendientes o en_curso.
 * Fallback: jornadas suspendidas con deadline_override futuro en prode_fechas.
 */
function getJornadaActivaPrimera(?string $torneo = null, ?int $temporadaId = null): ?array
{
    $params = [];
    $where  = ["p.categoria = 'Primera'", "p.estado IN ('pendiente', 'en_curso')"];

    if ($torneo) {
        $where[]  = 'p.torneo = ?';
        $params[] = $torneo;
    }
    if ($temporadaId) {
        $where[]  = 'p.temporada_id = ?';
        $params[] = $temporadaId;
    }

    $row = Database::fetchOne(
        "SELECT p.jornada, p.torneo, p.temporada_id,
                MIN(CONCAT(p.fecha, ' ', p.hora)) AS primer_partido,
                COUNT(*) AS total_partidos
         FROM partidos p
         WHERE " . implode(' AND ', $where) . "
           AND p.jornada IS NOT NULL
         GROUP BY p.jornada, p.torneo, p.temporada_id
         ORDER BY p.jornada ASC
         LIMIT 1",
        $params
    );

    if ($row) {
        return $row;
    }

    // Fallback: jornada suspendida con deadline_override aún vigente
    $overrideParams = ["pf.categoria = 'Primera'", "pf.estado = 'abierta'", "pf.deadline_override > NOW()"];
    $overrideExtra  = [];
    if ($torneo) {
        $overrideParams[] = 'pf.torneo = ?';
        $overrideExtra[]  = $torneo;
    }
    if ($temporadaId) {
        $overrideParams[] = 'pf.temporada_id = ?';
        $overrideExtra[]  = $temporadaId;
    }

    $suspended = Database::fetchOne(
        "SELECT pf.jornada, pf.torneo, pf.temporada_id, pf.deadline_override
         FROM prode_fechas pf
         WHERE " . implode(' AND ', $overrideParams) . "
         ORDER BY pf.jornada ASC
         LIMIT 1",
        $overrideExtra
    );

    if (!$suspended) {
        return null;
    }

    // Contar todos los partidos de esa jornada (cualquier estado) para el total
    $totalRow = Database::fetchOne(
        "SELECT COUNT(*) AS total_partidos,
                MIN(CONCAT(fecha, ' ', hora)) AS primer_partido
         FROM partidos
         WHERE jornada = ? AND categoria = 'Primera' AND torneo = ? AND temporada_id = ?",
        [(int) $suspended['jornada'], $suspended['torneo'], (int) $suspended['temporada_id']]
    );

    return [
        'jornada'        => $suspended['jornada'],
        'torneo'         => $suspended['torneo'],
        'temporada_id'   => $suspended['temporada_id'],
        'primer_partido' => $totalRow['primer_partido'] ?? null,
        'total_partidos' => $totalRow['total_partidos'] ?? 0,
        'suspendida'     => true,
    ];
}

/**
 * Calcula el deadline de una jornada: 1 hora antes del primer partido pendiente/en_curso.
 * Si la jornada tiene un deadline_override en prode_fechas, lo prioriza.
 */
function calcularDeadline(int $jornada, string $torneo, int $temporadaId): ?string
{
    // Prioridad 1: deadline_override configurado por el admin
    $override = Database::fetchOne(
        "SELECT deadline_override FROM prode_fechas
         WHERE jornada = ? AND categoria = 'Primera' AND torneo = ? AND temporada_id = ?",
        [$jornada, $torneo, $temporadaId]
    );
    if ($override && !empty($override['deadline_override'])) {
        return $override['deadline_override'];
    }

    // Prioridad 2: 1 hora antes del primer partido pendiente o en_curso de la jornada
    $row = Database::fetchOne(
        "SELECT MIN(CONCAT(fecha, ' ', hora)) AS primer_partido
         FROM partidos
         WHERE jornada = ? AND torneo = ? AND temporada_id = ? AND categoria = 'Primera'
           AND estado IN ('pendiente', 'en_curso')",
        [$jornada, $torneo, $temporadaId]
    );

    if (!$row || !$row['primer_partido']) {
        return null;
    }

    $ts = strtotime($row['primer_partido']) - 3600; // -1 hora
    return date('Y-m-d H:i:s', $ts);
}

/**
 * Devuelve si el deadline ya pasó (pronósticos bloqueados).
 */
function deadlinePasado(string $deadline): bool
{
    return strtotime($deadline) <= time();
}

// ---------------------------------------------------------------------------
// Registro de rutas
// ---------------------------------------------------------------------------

function registerProdeRoutes(Router $router): void
{
    $router->group('/prode', function (Router $r) {

        // -----------------------------------------------------------------------
        // GET /prode/config — público: lee si el prode está habilitado
        // -----------------------------------------------------------------------
        $r->get('/config', function (Request $req, Response $res) {
            $val     = getHomeConfigValue('prode_enabled');
            $enabled = ($val === null || $val === '1');
            return $res->json(['enabled' => $enabled]);
        });

        // -----------------------------------------------------------------------
        // PUT /prode/config — admin: activa o desactiva el prode
        // -----------------------------------------------------------------------
        $r->put('/config', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $enabled = isset($req->body['enabled']) ? (bool) $req->body['enabled'] : true;
            setHomeConfigValue('prode_enabled', $enabled ? '1' : '0');
            return $res->json(['enabled' => $enabled]);
        });

        // -----------------------------------------------------------------------
        // POST /prode/verificar/solicitar
        // -----------------------------------------------------------------------
        $r->post('/verificar/solicitar', function (Request $req, Response $res) {
            try {
            ensureProdeTables();

            $email = strtolower(trim((string) ($req->body['email'] ?? '')));
            $alias = trim((string) ($req->body['alias'] ?? ''));

            if (!validarEmail($email)) {
                return $res->status(400)->json(['error' => 'Email inválido']);
            }

            // ¿Ya existe? → re-login automático con el alias original
            $participante = Database::fetchOne(
                "SELECT id, alias FROM prode_participantes WHERE email = ?",
                [$email]
            );

            if ($participante) {
                $token    = bin2hex(random_bytes(32));
                $expiraAt = date('Y-m-d H:i:s', time() + 30 * 24 * 3600);
                Database::execute(
                    "INSERT INTO prode_sesiones (participante_id, token, expira_at) VALUES (?, ?, ?)",
                    [(int) $participante['id'], $token, $expiraAt]
                );
                $jornadaActiva = getJornadaActivaPrimera();
                return $res->json([
                    'token'          => $token,
                    'alias'          => $participante['alias'],
                    'nuevo'          => false,
                    'jornada_activa' => $jornadaActiva ? (int) $jornadaActiva['jornada'] : null,
                ]);
            }

            // Nuevo usuario: alias requerido
            if ($alias === '') {
                return $res->status(400)->json(['error' => 'El alias es requerido para registrarte']);
            }
            if (mb_strlen($alias) < 2 || mb_strlen($alias) > 30) {
                return $res->status(400)->json(['error' => 'El alias debe tener entre 2 y 30 caracteres']);
            }
            if (!preg_match('/^[a-zA-Z0-9_\-. ñÑáéíóúÁÉÍÓÚ]+$/u', $alias)) {
                return $res->status(400)->json(['error' => 'El alias solo puede contener letras, números, espacios y guiones']);
            }
            $aliasOcupado = Database::fetchOne(
                "SELECT id FROM prode_participantes WHERE alias = ?",
                [$alias]
            );
            if ($aliasOcupado) {
                return $res->status(409)->json(['error' => 'Ese alias ya está en uso, elegí otro']);
            }

            // Crear participante + sesión
            Database::execute(
                "INSERT INTO prode_participantes (alias, email) VALUES (?, ?)",
                [$alias, $email]
            );
            $participanteId = (int) Database::lastInsertId();

            $token    = bin2hex(random_bytes(32));
            $expiraAt = date('Y-m-d H:i:s', time() + 30 * 24 * 3600);
            Database::execute(
                "INSERT INTO prode_sesiones (participante_id, token, expira_at) VALUES (?, ?, ?)",
                [$participanteId, $token, $expiraAt]
            );

            $jornadaActiva = getJornadaActivaPrimera();
            return $res->json([
                'token'          => $token,
                'alias'          => $alias,
                'nuevo'          => true,
                'jornada_activa' => $jornadaActiva ? (int) $jornadaActiva['jornada'] : null,
            ]);
            } catch (\Throwable $e) {
                error_log('Error POST /prode/verificar/solicitar: ' . $e->getMessage());
                return $res->status(500)->json(['error' => 'Error interno. Intentá de nuevo.']);
            }
        });

        // -----------------------------------------------------------------------
        // GET /prode/jornada-activa
        // -----------------------------------------------------------------------
        $r->get('/jornada-activa', function (Request $req, Response $res) {
            ensureProdeTables();

            $torneo     = $req->query['torneo']      ?? null;
            $temporadaId = isset($req->query['temporada']) ? (int) $req->query['temporada'] : null;

            $jornada = getJornadaActivaPrimera($torneo, $temporadaId);

            if (!$jornada) {
                return $res->json(['jornada' => null, 'mensaje' => 'No hay jornadas pendientes']);
            }

            $jornNum     = (int) $jornada['jornada'];
            $torneoNombre = $jornada['torneo'];
            $tempId       = (int) $jornada['temporada_id'];

            $deadline = calcularDeadline($jornNum, $torneoNombre, $tempId);

            // Estado en prode_fechas (si ya existe el registro)
            $prodeFecha = Database::fetchOne(
                "SELECT estado, puntos_calculados FROM prode_fechas
                 WHERE jornada = ? AND categoria = 'Primera' AND torneo = ? AND temporada_id = ?",
                [$jornNum, $torneoNombre, $tempId]
            );
            $abierta = !$prodeFecha || $prodeFecha['estado'] === 'abierta';

            return $res->json([
                'jornada'          => $jornNum,
                'torneo'           => $torneoNombre,
                'temporada_id'     => $tempId,
                'total_partidos'   => (int) $jornada['total_partidos'],
                'deadline'         => $deadline,
                'bloqueado'        => $deadline ? deadlinePasado($deadline) : false,
                'abierta'          => $abierta,
                'puntos_calculados'=> $prodeFecha ? (bool) $prodeFecha['puntos_calculados'] : false,
                'suspendida'       => !empty($jornada['suspendida']),
            ]);
        });

        // -----------------------------------------------------------------------
        // GET /prode/partidos
        // -----------------------------------------------------------------------
        $r->get('/partidos', function (Request $req, Response $res) {
            ensureProdeTables();

            $torneo      = $req->query['torneo']      ?? null;
            $temporadaId = isset($req->query['temporada']) ? (int) $req->query['temporada'] : null;

            // Resolver jornada: parámetro explícito o activa
            $activa = null;
            if (isset($req->query['jornada'])) {
                $jornNum = (int) $req->query['jornada'];

                // Inferir torneo/temporada si no se proveen
                if (!$torneo || !$temporadaId) {
                    $infer = Database::fetchOne(
                        "SELECT torneo, temporada_id FROM partidos
                         WHERE jornada = ? AND categoria = 'Primera'
                         LIMIT 1",
                        [$jornNum]
                    );
                    if ($infer) {
                        $torneo      = $torneo      ?? $infer['torneo'];
                        $temporadaId = $temporadaId ?? (int) $infer['temporada_id'];
                    }
                }
            } else {
                $activa = getJornadaActivaPrimera($torneo, $temporadaId);
                if (!$activa) {
                    return $res->json(['jornada' => null, 'partidos' => [], 'bloqueado' => false]);
                }
                $jornNum     = (int) $activa['jornada'];
                $torneo      = $torneo      ?? $activa['torneo'];
                $temporadaId = $temporadaId ?? (int) $activa['temporada_id'];
            }

            $deadline = calcularDeadline($jornNum, (string) $torneo, (int) $temporadaId);
            $bloqueado = $deadline ? deadlinePasado($deadline) : false;

            $partidos = Database::fetchAll(
                "SELECT p.id, p.fecha, p.hora, p.estadio, p.estado,
                        p.goleslocal AS golesLocal, p.golesvisitante AS golesVisitante,
                        p.jornada, p.torneo, p.categoria, p.temporada_id,
                        el.nombre AS local, ev.nombre AS visitante,
                        el.escudo AS localEscudo, ev.escudo AS visitanteEscudo
                 FROM partidos p
                 JOIN equipos el ON el.id = p.local
                 JOIN equipos ev ON ev.id = p.visitante
                 WHERE p.jornada = ? AND p.categoria = 'Primera'
                   AND p.torneo = ? AND p.temporada_id = ?
                 ORDER BY p.fecha ASC, p.hora ASC",
                [$jornNum, $torneo, $temporadaId]
            );

            foreach ($partidos as &$p) {
                // Normalizar escudos
                foreach (['localEscudo', 'visitanteEscudo'] as $field) {
                    $v = $p[$field] ?? '';
                    if (str_starts_with($v, '/logos/')) {
                        $p[$field] = '/assets-equipos/logos/' . basename($v);
                    }
                }
            }
            unset($p);

            return $res->json([
                'jornada'  => $jornNum,
                'torneo'   => $torneo,
                'deadline' => $deadline,
                'bloqueado'=> $bloqueado,
                'suspendida'=> !empty($activa['suspendida']),
                'partidos' => array_values($partidos),
            ]);
        });

        // -----------------------------------------------------------------------
        // POST /prode/pronostico  [requiere X-Prode-Token]
        // -----------------------------------------------------------------------
        $r->post('/pronostico', function (Request $req, Response $res) {
            if (!prodeAuthMiddleware($req, $res)) return;
            ensureProdeTables();

            $pronosticos = $req->body['pronosticos'] ?? [];
            if (!is_array($pronosticos) || count($pronosticos) === 0) {
                return $res->status(400)->json(['error' => 'Pronosticos es requerido y debe ser un array']);
            }

            // Validar estructura básica
            foreach ($pronosticos as $idx => $pron) {
                $gl = $pron['goles_local']     ?? null;
                $gv = $pron['goles_visitante'] ?? null;
                $pid = $pron['partido_id']     ?? null;
                if ($pid === null || $gl === null || $gv === null) {
                    return $res->status(400)->json(['error' => "Pronóstico #{$idx}: partido_id, goles_local y goles_visitante son requeridos"]);
                }
                if (!is_numeric($gl) || !is_numeric($gv) || (int) $gl < 0 || (int) $gv < 0) {
                    return $res->status(400)->json(['error' => "Pronóstico #{$idx}: los goles deben ser números no negativos"]);
                }
            }

            $partidoIds = array_unique(array_map(fn($p) => (int) $p['partido_id'], $pronosticos));
            $placeholders = implode(',', array_fill(0, count($partidoIds), '?'));

            $partidos = Database::fetchAll(
                "SELECT id, jornada, torneo, temporada_id, estado
                 FROM partidos
                 WHERE id IN ($placeholders) AND categoria = 'Primera'",
                $partidoIds
            );

            if (count($partidos) !== count($partidoIds)) {
                return $res->status(400)->json(['error' => 'Uno o más partidos no encontrados o no son de Primera División']);
            }

            // Verificar que todos los partidos sean de la misma jornada y que el deadline no pasó
            $jornadas = array_unique(array_column($partidos, 'jornada'));
            if (count($jornadas) !== 1) {
                return $res->status(400)->json(['error' => 'Todos los pronósticos deben ser de la misma jornada']);
            }

            $primerPartido = $partidos[0];
            $deadline = calcularDeadline(
                (int) $primerPartido['jornada'],
                $primerPartido['torneo'],
                (int) $primerPartido['temporada_id']
            );

            if ($deadline && deadlinePasado($deadline)) {
                return $res->status(403)->json(['error' => 'El plazo para cargar pronósticos ya cerró']);
            }

            // Verificar estado prode_fechas
            $prodeFecha = Database::fetchOne(
                "SELECT estado FROM prode_fechas
                 WHERE jornada = ? AND categoria = 'Primera' AND torneo = ? AND temporada_id = ?",
                [(int) $primerPartido['jornada'], $primerPartido['torneo'], (int) $primerPartido['temporada_id']]
            );
            if ($prodeFecha && $prodeFecha['estado'] === 'cerrada') {
                return $res->status(403)->json(['error' => 'Esta fecha ya fue cerrada']);
            }

            $participanteId = $req->prodeUser->id;
            $guardados = 0;

            $partidoById = [];
            foreach ($partidos as $p) {
                $partidoById[(int) $p['id']] = $p;
            }

            foreach ($pronosticos as $pron) {
                $partidoId = (int) $pron['partido_id'];
                $gl        = (int) $pron['goles_local'];
                $gv        = (int) $pron['goles_visitante'];
                $partido   = $partidoById[$partidoId];

                Database::execute(
                    "INSERT INTO prode_pronosticos
                        (participante_id, partido_id, jornada, temporada_id, goles_local, goles_visitante)
                     VALUES (?, ?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE
                        goles_local = VALUES(goles_local),
                        goles_visitante = VALUES(goles_visitante),
                        updated_at = NOW()",
                    [
                        $participanteId,
                        $partidoId,
                        (int) $partido['jornada'],
                        (int) $partido['temporada_id'],
                        $gl,
                        $gv,
                    ]
                );
                $guardados++;
            }

            return $res->json(['guardados' => $guardados, 'mensaje' => 'Pronósticos guardados correctamente']);
        });

        // -----------------------------------------------------------------------
        // GET /prode/mis-pronosticos  [requiere X-Prode-Token]
        // -----------------------------------------------------------------------
        $r->get('/mis-pronosticos', function (Request $req, Response $res) {
            if (!prodeAuthMiddleware($req, $res)) return;
            ensureProdeTables();

            $participanteId = $req->prodeUser->id;
            $jornada        = isset($req->query['jornada']) ? (int) $req->query['jornada'] : null;
            $torneo         = $req->query['torneo'] ?? null;
            $temporadaId    = isset($req->query['temporada']) ? (int) $req->query['temporada'] : null;

            if (!$jornada) {
                $activa = getJornadaActivaPrimera($torneo, $temporadaId);
                if (!$activa) {
                    return $res->json(['jornada' => null, 'pronosticos' => [], 'total_puntos' => 0]);
                }
                $jornada     = (int) $activa['jornada'];
                $torneo      = $torneo      ?? $activa['torneo'];
                $temporadaId = $temporadaId ?? (int) $activa['temporada_id'];
            }

            $rows = Database::fetchAll(
                "SELECT pp.partido_id, pp.goles_local, pp.goles_visitante, pp.puntos_obtenidos,
                        p.estado, p.goleslocal AS golesLocalReal, p.golesvisitante AS golesVisitanteReal,
                        el.nombre AS local, ev.nombre AS visitante,
                        el.escudo AS localEscudo, ev.escudo AS visitanteEscudo,
                        p.fecha, p.hora
                 FROM prode_pronosticos pp
                 JOIN partidos p ON p.id = pp.partido_id
                 JOIN equipos el ON el.id = p.local
                 JOIN equipos ev ON ev.id = p.visitante
                 WHERE pp.participante_id = ? AND pp.jornada = ?
                 ORDER BY p.fecha ASC, p.hora ASC",
                [$participanteId, $jornada]
            );

            $totalPuntos = 0;
            foreach ($rows as &$row) {
                foreach (['localEscudo', 'visitanteEscudo'] as $field) {
                    $v = $row[$field] ?? '';
                    if (str_starts_with($v, '/logos/')) {
                        $row[$field] = '/assets-equipos/logos/' . basename($v);
                    }
                }
                if ($row['puntos_obtenidos'] !== null) {
                    $totalPuntos += (int) $row['puntos_obtenidos'];
                }
            }
            unset($row);

            return $res->json([
                'jornada'      => $jornada,
                'pronosticos'  => array_values($rows),
                'total_puntos' => $totalPuntos,
            ]);
        });

        // -----------------------------------------------------------------------
        // GET /prode/ranking
        // -----------------------------------------------------------------------
        $r->get('/ranking', function (Request $req, Response $res) {
            ensureProdeTables();

            $limit = max(1, min(100, (int) ($req->query['limit'] ?? 50)));

            $rows = Database::fetchAll(
                "SELECT p.alias,
                        COALESCE(SUM(pp.puntos_obtenidos), 0) AS puntos_total,
                        COUNT(pp.id) AS pronosticos_count,
                        SUM(CASE WHEN pp.puntos_obtenidos >= 4 THEN 1 ELSE 0 END) AS aciertos_exactos,
                        SUM(CASE WHEN pp.puntos_obtenidos > 0 THEN 1 ELSE 0 END) AS aciertos_resultado
                 FROM prode_participantes p
                 LEFT JOIN prode_pronosticos pp ON pp.participante_id = p.id
                    AND pp.puntos_obtenidos IS NOT NULL
                 WHERE EXISTS (SELECT 1 FROM prode_pronosticos WHERE participante_id = p.id)
                 GROUP BY p.id, p.alias
                 ORDER BY puntos_total DESC, aciertos_exactos DESC, pronosticos_count DESC
                 LIMIT ?",
                [$limit]
            );

            $ranking = [];
            foreach ($rows as $i => $row) {
                $ranking[] = [
                    'posicion'          => $i + 1,
                    'alias'             => $row['alias'],
                    'puntos_total'      => (int) $row['puntos_total'],
                    'pronosticos_count' => (int) $row['pronosticos_count'],
                    'aciertos_exactos'  => (int) $row['aciertos_exactos'],
                    'aciertos_resultado'=> (int) $row['aciertos_resultado'],
                ];
            }

            return $res->json(['ranking' => $ranking]);
        });

        // -----------------------------------------------------------------------
        // GET /prode/ranking/jornada/:jornada
        // -----------------------------------------------------------------------
        $r->get('/ranking/jornada/:jornada', function (Request $req, Response $res) {
            ensureProdeTables();

            $jornada = (int) $req->params['jornada'];
            if ($jornada <= 0) {
                return $res->status(400)->json(['error' => 'Jornada inválida']);
            }

            $rows = Database::fetchAll(
                "SELECT p.alias,
                        COALESCE(SUM(pp.puntos_obtenidos), 0) AS puntos,
                        COUNT(pp.id) AS pronosticos_count,
                        SUM(CASE WHEN pp.puntos_obtenidos >= 4 THEN 1 ELSE 0 END) AS aciertos_exactos
                 FROM prode_participantes p
                 INNER JOIN prode_pronosticos pp ON pp.participante_id = p.id
                 WHERE pp.jornada = ? AND pp.puntos_obtenidos IS NOT NULL
                 GROUP BY p.id, p.alias
                 ORDER BY puntos DESC, aciertos_exactos DESC
                 LIMIT 100",
                [$jornada]
            );

            $ranking = [];
            foreach ($rows as $i => $row) {
                $ranking[] = [
                    'posicion'         => $i + 1,
                    'alias'            => $row['alias'],
                    'puntos'           => (int) $row['puntos'],
                    'pronosticos_count'=> (int) $row['pronosticos_count'],
                    'aciertos_exactos' => (int) $row['aciertos_exactos'],
                ];
            }

            return $res->json(['jornada' => $jornada, 'ranking' => $ranking]);
        });

        // -----------------------------------------------------------------------
        // GET /prode/admin/fechas  [requiere JWT admin]
        // -----------------------------------------------------------------------
        $r->get('/admin/fechas', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            ensureProdeTables();

            $rows = Database::fetchAll(
                "SELECT pf.jornada, pf.torneo, pf.temporada_id, pf.estado,
                        pf.puntos_calculados, pf.closed_at, pf.deadline_override,
                        COUNT(DISTINCT pp.participante_id) AS participantes,
                        COUNT(pp.id) AS pronosticos_total
                 FROM prode_fechas pf
                 LEFT JOIN prode_pronosticos pp ON pp.jornada = pf.jornada
                    AND pp.temporada_id = pf.temporada_id
                 WHERE pf.categoria = 'Primera'
                 GROUP BY pf.id
                 ORDER BY pf.jornada DESC"
            );

            // Incluir también las jornadas con pronósticos pero sin registro en prode_fechas
            $jornadasConPronos = Database::fetchAll(
                "SELECT pp.jornada, pp.temporada_id, p.torneo,
                        COUNT(DISTINCT pp.participante_id) AS participantes,
                        COUNT(pp.id) AS pronosticos_total
                 FROM prode_pronosticos pp
                 JOIN partidos p ON p.id = pp.partido_id AND p.categoria = 'Primera'
                 WHERE NOT EXISTS (
                     SELECT 1 FROM prode_fechas pf2
                     WHERE pf2.jornada = pp.jornada AND pf2.temporada_id = pp.temporada_id
                 )
                 GROUP BY pp.jornada, pp.temporada_id, p.torneo
                 ORDER BY pp.jornada DESC"
            );

            // Calcular deadlines y enriquecer
            $result = [];
            foreach ($rows as $row) {
                $deadline = calcularDeadline((int) $row['jornada'], $row['torneo'], (int) $row['temporada_id']);
                $suspendida = !empty($row['deadline_override']) && $row['estado'] === 'abierta';
                $result[] = array_merge($row, [
                    'deadline'          => $deadline,
                    'deadline_override' => $row['deadline_override'] ?? null,
                    'jornada'           => (int) $row['jornada'],
                    'participantes'     => (int) $row['participantes'],
                    'pronosticos_total' => (int) $row['pronosticos_total'],
                    'suspendida'        => $suspendida,
                ]);
            }

            foreach ($jornadasConPronos as $row) {
                $deadline = calcularDeadline((int) $row['jornada'], $row['torneo'], (int) $row['temporada_id']);
                $result[] = [
                    'jornada'           => (int) $row['jornada'],
                    'torneo'            => $row['torneo'],
                    'temporada_id'      => (int) $row['temporada_id'],
                    'estado'            => 'abierta',
                    'puntos_calculados' => 0,
                    'closed_at'         => null,
                    'deadline_override' => null,
                    'deadline'          => $deadline,
                    'participantes'     => (int) $row['participantes'],
                    'pronosticos_total' => (int) $row['pronosticos_total'],
                    'suspendida'        => false,
                ];
            }

            usort($result, fn($a, $b) => $b['jornada'] <=> $a['jornada']);

            return $res->json(['fechas' => $result]);
        });

        // -----------------------------------------------------------------------
        // PUT /prode/admin/fecha/:jornada/deadline  [requiere JWT admin]
        // Permite establecer o borrar un deadline_override para jornadas suspendidas.
        // -----------------------------------------------------------------------
        $r->put('/admin/fecha/:jornada/deadline', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            ensureProdeTables();

            $jornada = (int) $req->params['jornada'];
            if ($jornada <= 0) {
                return $res->status(400)->json(['error' => 'Jornada inválida']);
            }

            $torneo          = $req->body['torneo']           ?? null;
            $temporadaId     = isset($req->body['temporada_id']) ? (int) $req->body['temporada_id'] : null;
            $deadlineOverride = isset($req->body['deadline_override'])
                ? ($req->body['deadline_override'] === '' || $req->body['deadline_override'] === null
                    ? null
                    : (string) $req->body['deadline_override'])
                : null;

            // Validar formato si se provee
            if ($deadlineOverride !== null) {
                $ts = strtotime($deadlineOverride);
                if ($ts === false) {
                    return $res->status(400)->json(['error' => 'Formato de deadline inválido. Usar YYYY-MM-DD HH:MM:SS']);
                }
                $deadlineOverride = date('Y-m-d H:i:s', $ts);
            }

            // Inferir torneo/temporada si no vienen
            if (!$torneo || !$temporadaId) {
                $infer = Database::fetchOne(
                    "SELECT torneo, temporada_id FROM partidos
                     WHERE jornada = ? AND categoria = 'Primera'
                     LIMIT 1",
                    [$jornada]
                );
                if (!$infer) {
                    return $res->status(404)->json(['error' => 'No se encontraron partidos para esta jornada']);
                }
                $torneo      = $torneo      ?? $infer['torneo'];
                $temporadaId = $temporadaId ?? (int) $infer['temporada_id'];
            }

            // Crear o actualizar registro en prode_fechas
            Database::execute(
                "INSERT INTO prode_fechas
                    (jornada, categoria, torneo, temporada_id, estado, deadline_override)
                 VALUES (?, 'Primera', ?, ?, 'abierta', ?)
                 ON DUPLICATE KEY UPDATE
                    deadline_override = VALUES(deadline_override)",
                [$jornada, $torneo, $temporadaId, $deadlineOverride]
            );

            return $res->json([
                'jornada'           => $jornada,
                'torneo'            => $torneo,
                'temporada_id'      => $temporadaId,
                'deadline_override' => $deadlineOverride,
                'mensaje'           => $deadlineOverride
                    ? "Plazo extendido hasta {$deadlineOverride}"
                    : 'Deadline override eliminado. Vuelve al cálculo automático.',
            ]);
        });

        // -----------------------------------------------------------------------
        // POST /prode/admin/fecha/:jornada/cerrar  [requiere JWT admin]
        // -----------------------------------------------------------------------
        $r->post('/admin/fecha/:jornada/cerrar', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            ensureProdeTables();

            $jornada = (int) $req->params['jornada'];
            if ($jornada <= 0) {
                return $res->status(400)->json(['error' => 'Jornada inválida']);
            }

            $torneo      = $req->body['torneo']      ?? null;
            $temporadaId = isset($req->body['temporada_id']) ? (int) $req->body['temporada_id'] : null;

            // Resolver torneo/temporada si no vienen
            if (!$torneo || !$temporadaId) {
                $infer = Database::fetchOne(
                    "SELECT torneo, temporada_id FROM prode_pronosticos pp
                     JOIN partidos p ON p.id = pp.partido_id
                     WHERE pp.jornada = ? AND p.categoria = 'Primera'
                     LIMIT 1",
                    [$jornada]
                );
                if (!$infer) {
                    return $res->status(404)->json(['error' => 'No hay pronósticos para esta jornada']);
                }
                $torneo      = $torneo      ?? $infer['torneo'];
                $temporadaId = $temporadaId ?? (int) $infer['temporada_id'];
            }

            // Verificar que no esté ya cerrada
            $prodeFecha = Database::fetchOne(
                "SELECT id, estado FROM prode_fechas
                 WHERE jornada = ? AND categoria = 'Primera' AND torneo = ? AND temporada_id = ?",
                [$jornada, $torneo, $temporadaId]
            );
            if ($prodeFecha && $prodeFecha['estado'] === 'cerrada') {
                return $res->status(409)->json(['error' => 'Esta fecha ya está cerrada y los puntos calculados']);
            }

            // Obtener todos los partidos de la jornada con resultado
            $partidos = Database::fetchAll(
                "SELECT id, goleslocal, golesvisitante, estado
                 FROM partidos
                 WHERE jornada = ? AND categoria = 'Primera' AND torneo = ? AND temporada_id = ?",
                [$jornada, $torneo, $temporadaId]
            );

            if (empty($partidos)) {
                return $res->status(404)->json(['error' => 'No se encontraron partidos para esta jornada']);
            }

            $jugados = array_filter($partidos, fn($p) => $p['estado'] === 'jugado');
            if (count($jugados) === 0) {
                return $res->status(400)->json(['error' => 'Ningún partido de esta jornada fue cargado aún']);
            }

            $partidoById = [];
            foreach ($partidos as $p) {
                $partidoById[(int) $p['id']] = $p;
            }

            // Calcular puntos para cada pronóstico
            $db = Database::getConnection();
            $db->beginTransaction();

            try {
                $pronosticos = Database::fetchAll(
                    "SELECT id, partido_id, goles_local, goles_visitante
                     FROM prode_pronosticos
                     WHERE jornada = ? AND temporada_id = ?",
                    [$jornada, $temporadaId]
                );

                $calculados = 0;

                foreach ($pronosticos as $pron) {
                    $partidoId = (int) $pron['partido_id'];
                    $partido   = $partidoById[$partidoId] ?? null;

                    if (!$partido || $partido['estado'] !== 'jugado') {
                        // Partido no jugado aún: puntos = 0 (podrán recalcularse si se fuerza)
                        $puntos = 0;
                    } else {
                        $realLocal     = (int) $partido['goleslocal'];
                        $realVisitante = (int) $partido['golesvisitante'];
                        $pronLocal     = (int) $pron['goles_local'];
                        $pronVisitante = (int) $pron['goles_visitante'];

                        // Resultado real: 1=local, 0=empate, -1=visitante
                        $resultadoReal = $realLocal <=> $realVisitante;
                        $resultadoPron = $pronLocal <=> $pronVisitante;

                        $puntos = 0;
                        if ($resultadoReal === $resultadoPron) {
                            $puntos += 1; // Acertó V/E/D
                            if ($realLocal === $pronLocal && $realVisitante === $pronVisitante) {
                                $puntos += 3; // Marcador exacto: +3 adicionales (total 4)
                            }
                        }
                    }

                    Database::execute(
                        "UPDATE prode_pronosticos SET puntos_obtenidos = ? WHERE id = ?",
                        [$puntos, (int) $pron['id']]
                    );
                    $calculados++;
                }

                // Crear o actualizar registro en prode_fechas
                Database::execute(
                    "INSERT INTO prode_fechas
                        (jornada, categoria, torneo, temporada_id, estado, puntos_calculados, closed_at)
                     VALUES (?, 'Primera', ?, ?, 'cerrada', 1, NOW())
                     ON DUPLICATE KEY UPDATE
                        estado = 'cerrada', puntos_calculados = 1, closed_at = NOW()",
                    [$jornada, $torneo, $temporadaId]
                );

                $db->commit();

                return $res->json([
                    'calculados'    => $calculados,
                    'jornada'       => $jornada,
                    'torneo'        => $torneo,
                    'partidos_jugados' => count($jugados),
                    'mensaje'       => "Puntos calculados para {$calculados} pronósticos",
                ]);
            } catch (\Throwable $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                throw $e;
            }
        });

        // -----------------------------------------------------------------------
        // GET /prode/admin/stats  [requiere JWT admin]
        // -----------------------------------------------------------------------
        $r->get('/admin/stats', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            ensureProdeTables();
            $participantes = (int) (Database::fetchOne(
                "SELECT COUNT(*) AS total FROM prode_participantes"
            )['total'] ?? 0);
            $pronosticos = (int) (Database::fetchOne(
                "SELECT COUNT(*) AS total FROM prode_pronosticos"
            )['total'] ?? 0);
            $jornadasCerradas = (int) (Database::fetchOne(
                "SELECT COUNT(*) AS total FROM prode_fechas WHERE estado = 'cerrada'"
            )['total'] ?? 0);
            return $res->json([
                'participantes'    => $participantes,
                'pronosticos'      => $pronosticos,
                'jornadas_cerradas'=> $jornadasCerradas,
            ]);
        });

        // -----------------------------------------------------------------------
        // GET /prode/admin/participantes  [requiere JWT admin]
        // -----------------------------------------------------------------------
        $r->get('/admin/participantes', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            ensureProdeTables();
            $rows = Database::fetchAll(
                "SELECT p.id, p.alias, p.email, p.created_at,
                        COALESCE(SUM(pp.puntos_obtenidos), 0) AS puntos_total,
                        COUNT(pp.id) AS pronosticos_count,
                        SUM(CASE WHEN pp.puntos_obtenidos >= 4 THEN 1 ELSE 0 END) AS aciertos_exactos
                 FROM prode_participantes p
                 LEFT JOIN prode_pronosticos pp ON pp.participante_id = p.id
                 GROUP BY p.id, p.alias, p.email, p.created_at
                 ORDER BY puntos_total DESC, p.created_at ASC"
            );
            foreach ($rows as &$row) {
                $row['id']               = (int) $row['id'];
                $row['puntos_total']     = (int) $row['puntos_total'];
                $row['pronosticos_count']= (int) $row['pronosticos_count'];
                $row['aciertos_exactos'] = (int) $row['aciertos_exactos'];
            }
            unset($row);
            return $res->json(['participantes' => array_values($rows)]);
        });

        // -----------------------------------------------------------------------
        // DELETE /prode/admin/participantes/:id  [requiere JWT admin]
        // -----------------------------------------------------------------------
        $r->delete('/admin/participantes/:id', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            ensureProdeTables();
            $id = (int) $req->params['id'];
            if ($id <= 0) {
                return $res->status(400)->json(['error' => 'ID inválido']);
            }
            $participante = Database::fetchOne(
                "SELECT id, alias FROM prode_participantes WHERE id = ?", [$id]
            );
            if (!$participante) {
                return $res->status(404)->json(['error' => 'Participante no encontrado']);
            }
            // ON DELETE CASCADE elimina sus pronósticos y sesiones
            Database::execute("DELETE FROM prode_participantes WHERE id = ?", [$id]);
            return $res->json(['eliminado' => true, 'alias' => $participante['alias']]);
        });

    }); // end group /prode
}
