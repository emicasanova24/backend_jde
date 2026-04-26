<?php
namespace App\Routes;

use App\Router;
use App\Database;
use App\Request;
use App\Response;

function registerTablasRoutes(Router $router): void {
    $router->group('/tabla', function (Router $r) {

        // GET /tabla
        $r->get('', function (Request $req, Response $res) {
            try {
                $params = [];
                $query = "SELECT id, nombre, categoria FROM equipos";
                if (!empty($req->query['categoria'])) {
                    $query .= " WHERE LOWER(categoria) = LOWER(?)";
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

                $pParams = [];
                $pWhere = ["estado = 'jugado'"];
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
                $params = ['gol'];
                $where = ["ev.jugador_id IS NOT NULL", "p.estado = 'jugado'"];
                if (!empty($req->query['categoria'])) {
                    $params[] = $req->query['categoria'];
                    $where[] = "LOWER(eq.categoria) = LOWER(?)";
                }
                if (!empty($req->query['torneo'])) {
                    $params[] = $req->query['torneo'];
                    $where[] = "LOWER(p.torneo) = LOWER(?)";
                }

                $rows = Database::fetchAll(
                    "SELECT j.nombre, COALESCE(eq.nombre, '-') AS equipo, COUNT(*) AS goles
                     FROM eventos ev
                     INNER JOIN jugadores j ON ev.jugador_id = j.id
                     INNER JOIN equipos eq ON ev.equipo_id = eq.id
                     INNER JOIN partidos p ON ev.partido_id = p.id
                     WHERE ev.tipo = ? AND " . implode(' AND ', $where) . "
                     GROUP BY ev.jugador_id, j.nombre, eq.nombre
                     HAVING COUNT(*) > 0
                     ORDER BY goles DESC, j.nombre ASC",
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
                $params_a = ['amarilla'];
                $params_r = ['roja'];
                $where = ["ev.jugador_id IS NOT NULL", "p.estado = 'jugado'"];
                if (!empty($req->query['categoria'])) {
                    $params_a[] = $req->query['categoria'];
                    $params_r[] = $req->query['categoria'];
                    $where[] = "LOWER(eq.categoria) = LOWER(?)";
                }
                if (!empty($req->query['torneo'])) {
                    $params_a[] = $req->query['torneo'];
                    $params_r[] = $req->query['torneo'];
                    $where[] = "LOWER(p.torneo) = LOWER(?)";
                }

                $amarillas = Database::fetchAll(
                    "SELECT j.nombre, COALESCE(eq.nombre, '-') AS equipo, COUNT(*) AS amarillas
                     FROM eventos ev
                     INNER JOIN jugadores j ON ev.jugador_id = j.id
                     INNER JOIN equipos eq ON ev.equipo_id = eq.id
                     INNER JOIN partidos p ON ev.partido_id = p.id
                     WHERE ev.tipo = ? AND " . implode(' AND ', $where) . "
                     GROUP BY ev.jugador_id, j.nombre, eq.nombre
                     HAVING COUNT(*) > 0
                     ORDER BY amarillas DESC, j.nombre ASC",
                    $params_a
                );

                $rojas = Database::fetchAll(
                    "SELECT j.nombre, COALESCE(eq.nombre, '-') AS equipo, COUNT(*) AS rojas
                     FROM eventos ev
                     INNER JOIN jugadores j ON ev.jugador_id = j.id
                     INNER JOIN equipos eq ON ev.equipo_id = eq.id
                     INNER JOIN partidos p ON ev.partido_id = p.id
                     WHERE ev.tipo = ? AND " . implode(' AND ', $where) . "
                     GROUP BY ev.jugador_id, j.nombre, eq.nombre
                     HAVING COUNT(*) > 0
                     ORDER BY rojas DESC, j.nombre ASC",
                    $params_r
                );

                $res->json(['amarillas' => $amarillas, 'rojas' => $rojas]);
            } catch (\Exception $e) {
                error_log('Error GET /tabla/tarjetas: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al obtener tarjetas']);
            }
        });
    });
}
