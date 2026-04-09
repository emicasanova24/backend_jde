<?php
namespace App\Routes;

use App\Router;
use App\Database;
use App\Request;
use App\Response;

use function App\resolveSeasonId;

require_once __DIR__ . '/../Season.php';

function registerTablasRoutes(Router $router): void {
    $router->group('/tabla', function (Router $r) {

        // GET /tabla
        $r->get('', function (Request $req, Response $res) {
            try {
                $seasonId = resolveSeasonId($req->query['temporada'] ?? null);
                $params = [$seasonId];
                $query = "SELECT id, nombre, categoria FROM equipos WHERE temporada_id = ?";
                if (!empty($req->query['categoria'])) {
                    $query .= " AND LOWER(categoria) = LOWER(?)";
                    $params[] = $req->query['categoria'];
                }
                $equipos = Database::fetchAll($query, $params);

                $tabla = [];
                $lookup = [];
                foreach ($equipos as $e) {
                    $entry = [
                        'equipo' => $e['nombre'], 'id' => $e['id'],
                        'pj' => 0, 'pg' => 0, 'pe' => 0, 'pp' => 0,
                        'gf' => 0, 'gc' => 0, 'dg' => 0, 'puntos' => 0,
                    ];
                    $tabla[] = &$entry;
                    $lookup[$e['id']] = count($tabla) - 1;
                    unset($entry);
                }

                $pParams = [$seasonId];
                $pWhere = ['temporada_id = ?', "estado = 'jugado'"];
                if (!empty($req->query['categoria'])) {
                    $pParams[] = $req->query['categoria'];
                    $pWhere[] = "LOWER(categoria) = LOWER(?)";
                }
                if (!empty($req->query['torneo'])) {
                    $pParams[] = $req->query['torneo'];
                    $pWhere[] = "LOWER(torneo) = LOWER(?)";
                }

                $partidos = Database::fetchAll(
                    "SELECT local, visitante, goleslocal, golesvisitante FROM partidos WHERE " . implode(' AND ', $pWhere),
                    $pParams
                );

                foreach ($partidos as $p) {
                    $li = $lookup[$p['local']] ?? null;
                    $vi = $lookup[$p['visitante']] ?? null;
                    if ($li === null || $vi === null) continue;

                    $tabla[$li]['pj']++;
                    $tabla[$vi]['pj']++;
                    $gl = (int)$p['goleslocal'];
                    $gv = (int)$p['golesvisitante'];
                    $tabla[$li]['gf'] += $gl;
                    $tabla[$li]['gc'] += $gv;
                    $tabla[$vi]['gf'] += $gv;
                    $tabla[$vi]['gc'] += $gl;

                    if ($gl > $gv) {
                        $tabla[$li]['pg']++;
                        $tabla[$vi]['pp']++;
                    } elseif ($gl < $gv) {
                        $tabla[$vi]['pg']++;
                        $tabla[$li]['pp']++;
                    } else {
                        $tabla[$li]['pe']++;
                        $tabla[$vi]['pe']++;
                    }
                }

                foreach ($tabla as &$e) {
                    $e['dg'] = $e['gf'] - $e['gc'];
                    $e['puntos'] = $e['pg'] * 3 + $e['pe'];
                }
                unset($e);

                usort($tabla, function ($a, $b) {
                    return ($b['puntos'] - $a['puntos']) ?: ($b['dg'] - $a['dg']) ?: ($b['gf'] - $a['gf']);
                });

                $res->json($tabla);
            } catch (\Exception $e) {
                error_log('Error GET /tabla: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al calcular la tabla']);
            }
        });

        // GET /tabla/goleadores
        $r->get('/goleadores', function (Request $req, Response $res) {
            try {
                $seasonId = resolveSeasonId($req->query['temporada'] ?? null);
                $params = [$seasonId];
                $where = ['p.temporada_id = ?', "ev.tipo = 'gol'"];
                if (!empty($req->query['categoria'])) {
                    $params[] = $req->query['categoria'];
                    $where[] = "LOWER(p.categoria) = LOWER(?)";
                }
                if (!empty($req->query['torneo'])) {
                    $params[] = $req->query['torneo'];
                    $where[] = "LOWER(p.torneo) = LOWER(?)";
                }
                $rows = Database::fetchAll(
                    "SELECT j.nombre, COALESCE(e.nombre, '-') AS equipo, COUNT(*) AS goles
                     FROM eventos ev
                     JOIN partidos p ON ev.partido_id = p.id
                     JOIN jugadores j ON ev.jugador_id = j.id
                     LEFT JOIN equipos e ON ev.equipo_id = e.id
                     WHERE " . implode(' AND ', $where) . "
                     GROUP BY j.id, j.nombre, e.nombre
                     ORDER BY goles DESC",
                    $params
                );
                $res->json($rows);
            } catch (\Exception $e) {
                error_log('Error GET /tabla/goleadores: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al obtener goleadores']);
            }
        });

        // GET /tabla/tarjetas
        $r->get('/tarjetas', function (Request $req, Response $res) {
            try {
                $buildWhere = function (string $tipo) use ($req) {
                    $params = [resolveSeasonId($req->query['temporada'] ?? null)];
                    $where = ['p.temporada_id = ?', "ev.tipo = '$tipo'"];
                    if (!empty($req->query['categoria'])) {
                        $params[] = $req->query['categoria'];
                        $where[] = "LOWER(p.categoria) = LOWER(?)";
                    }
                    if (!empty($req->query['torneo'])) {
                        $params[] = $req->query['torneo'];
                        $where[] = "LOWER(p.torneo) = LOWER(?)";
                    }
                    return ['params' => $params, 'where' => implode(' AND ', $where)];
                };

                $aq = $buildWhere('amarilla');
                $rq = $buildWhere('roja');

                $amarillas = Database::fetchAll(
                    "SELECT j.nombre, COALESCE(e.nombre, '-') AS equipo, COUNT(*) AS amarillas
                     FROM eventos ev
                     JOIN partidos p ON ev.partido_id = p.id
                     JOIN jugadores j ON ev.jugador_id = j.id
                     LEFT JOIN equipos e ON ev.equipo_id = e.id
                     WHERE {$aq['where']}
                     GROUP BY j.id, j.nombre, e.nombre
                     ORDER BY amarillas DESC",
                    $aq['params']
                );

                $rojas = Database::fetchAll(
                    "SELECT j.nombre, COALESCE(e.nombre, '-') AS equipo, COUNT(*) AS rojas
                     FROM eventos ev
                     JOIN partidos p ON ev.partido_id = p.id
                     JOIN jugadores j ON ev.jugador_id = j.id
                     LEFT JOIN equipos e ON ev.equipo_id = e.id
                     WHERE {$rq['where']}
                     GROUP BY j.id, j.nombre, e.nombre
                     ORDER BY rojas DESC",
                    $rq['params']
                );

                $res->json(['amarillas' => $amarillas, 'rojas' => $rojas]);
            } catch (\Exception $e) {
                error_log('Error GET /tabla/tarjetas: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al obtener tarjetas']);
            }
        });
    });
}
