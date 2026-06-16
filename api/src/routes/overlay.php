<?php
namespace App\Routes;

use App\Router;
use App\Database;
use App\Request;
use App\Response;
use App\OverlayState;

use function App\Middleware\authMiddleware;

require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../OverlayState.php';

function registerOverlayRoutes(Router $router): void
{
    $router->group('/overlay', function (Router $r) {

        // ---------------------------------------------------------------
        // SSE — stream de eventos en tiempo real
        // Público: cualquier cliente (overlays OBS, panel) puede suscribirse
        // ---------------------------------------------------------------
        $r->get('/events', function (Request $req, Response $res) {
            // Deshabilitar output buffering y timeouts
            @ini_set('output_buffering', 'off');
            @ini_set('zlib.output_compression', false);
            if (ob_get_level()) @ob_end_clean();

            set_time_limit(0);
            ignore_user_abort(false);

            header('Content-Type: text/event-stream; charset=utf-8');
            header('Cache-Control: no-cache');
            header('X-Accel-Buffering: no'); // Nginx: deshabilita buffering
            header('Connection: keep-alive');
            // Remover el Content-Type: application/json seteado globalmente
            header_remove('Content-Type');
            header('Content-Type: text/event-stream; charset=utf-8');

            $lastMtime  = 0;
            $lastJson   = '';
            $statePath  = dirname(__DIR__, 2) . '/overlay-state.json';
            $pingEvery  = 15; // segundos entre pings si no hubo cambios
            $lastPing   = time();

            // Enviar estado inicial inmediatamente
            $state = OverlayState::load();
            $json  = json_encode($state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            echo "data: {$json}\n\n";
            $lastJson  = $json;
            $lastMtime = file_exists($statePath) ? filemtime($statePath) : 0;
            flush();

            while (!connection_aborted()) {
                usleep(300000); // 300ms entre polls

                clearstatcache(true, $statePath);
                $mtime = file_exists($statePath) ? filemtime($statePath) : 0;

                if ($mtime !== $lastMtime) {
                    $state = OverlayState::load();
                    $json  = json_encode($state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                    if ($json !== $lastJson) {
                        echo "data: {$json}\n\n";
                        $lastJson = $json;
                        flush();
                    }
                    $lastMtime = $mtime;
                    $lastPing  = time();
                }

                // Ping periódico para mantener la conexión viva
                if (time() - $lastPing >= $pingEvery) {
                    echo ": ping\n\n";
                    flush();
                    $lastPing = time();
                }
            }
            exit;
        });

        // ---------------------------------------------------------------
        // GET /overlay/state — snapshot del estado actual
        // ---------------------------------------------------------------
        $r->get('/state', function (Request $req, Response $res) {
            $res->json(OverlayState::load());
        });

        // ---------------------------------------------------------------
        // GET /overlay/equipos — lista de equipos para dropdowns
        // ---------------------------------------------------------------
        $r->get('/equipos', function (Request $req, Response $res) {
            try {
                $rows = Database::fetchAll(
                    "SELECT nombre, escudo FROM equipos ORDER BY nombre"
                );
                $result = [];
                foreach ($rows as $row) {
                    $escudo = trim((string)($row['escudo'] ?? ''));
                    if (str_starts_with($escudo, '/logos/')) {
                        $escudo = '/assets-equipos/logos/' . basename($escudo);
                    }
                    $result[$row['nombre']] = $escudo;
                }
                $res->json($result);
            } catch (\Exception $e) {
                error_log('[overlay/equipos] ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al listar equipos']);
            }
        });

        // ---------------------------------------------------------------
        // GET /overlay/history
        // ---------------------------------------------------------------
        $r->get('/history', function (Request $req, Response $res) {
            $state = OverlayState::load();
            $res->json(array_slice($state['history'] ?? [], 0, 30));
        });

        // ---------------------------------------------------------------
        // PUT /overlay/state — reemplazar estado completo (protegido)
        // ---------------------------------------------------------------
        $r->put('/state', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $new = $req->body;
            if (empty($new) || !is_array($new)) {
                $res->status(400)->json(['error' => 'Body inválido']);
                return;
            }
            OverlayState::save($new);
            $res->json($new);
        });

        // ---------------------------------------------------------------
        // POST /overlay/state/reset
        // ---------------------------------------------------------------
        $r->post('/state/reset', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $default = OverlayState::defaultState();
            OverlayState::save($default);
            $res->json($default);
        });

        // ---------------------------------------------------------------
        // PATCH /overlay/match
        // ---------------------------------------------------------------
        $r->patch('/match', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $state = OverlayState::load();
            OverlayState::patch($state['match'], array_intersect_key(
                $req->body,
                array_flip(['status', 'minute', 'referee', 'stadium', 'tournament'])
            ));
            OverlayState::addHistory($state, 'patch/match', $req->body);
            OverlayState::save($state);
            $res->json($state['match']);
        });

        // ---------------------------------------------------------------
        // PATCH /overlay/local  y  PATCH /overlay/visitante
        // Nota: estas rutas deben registrarse ANTES del genérico :side
        // ---------------------------------------------------------------
        foreach (['local', 'visitante'] as $side) {
            $r->patch("/{$side}", function (Request $req, Response $res) use ($side) {
                return authMiddleware($req, $res);
            }, function (Request $req, Response $res) use ($side) {
                $state = OverlayState::load();
                $allowed = ['nombre', 'logo', 'color', 'colorNumero', 'formacion', 'goles', 'jugadores', 'suplentes'];
                $updates = array_intersect_key($req->body, array_flip($allowed));
                OverlayState::patch($state[$side], $updates);
                OverlayState::addHistory($state, "patch/{$side}", $updates);
                OverlayState::save($state);
                $res->json($state[$side]);
            });
        }

        // ---------------------------------------------------------------
        // PUT /overlay/resultados
        // ---------------------------------------------------------------
        $r->put('/resultados', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $state = OverlayState::load();
            if (isset($req->body['resultados'])) {
                $state['resultados'] = (array)$req->body['resultados'];
            }
            if (array_key_exists('libre', $req->body)) {
                $state['libre'] = (string)$req->body['libre'];
            }
            OverlayState::addHistory($state, 'put/resultados', []);
            OverlayState::save($state);
            $res->json(['resultados' => $state['resultados'], 'libre' => $state['libre']]);
        });

        // ---------------------------------------------------------------
        // PATCH /overlay/dt
        // ---------------------------------------------------------------
        $r->patch('/dt', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $state = OverlayState::load();
            OverlayState::patch($state['dt'], array_intersect_key(
                $req->body,
                array_flip(['local', 'visitante'])
            ));
            OverlayState::save($state);
            $res->json($state['dt']);
        });

        // ---------------------------------------------------------------
        // Timer
        // ---------------------------------------------------------------
        $r->post('/timer/start', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $state = OverlayState::load();
            if (!$state['timer']['running']) {
                $state['timer']['running']        = true;
                $state['timer']['startTimestamp'] = (int)round(microtime(true) * 1000);
            }
            OverlayState::addHistory($state, 'timer/start', []);
            OverlayState::save($state);
            $res->json($state['timer']);
        });

        $r->post('/timer/pause', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $state = OverlayState::load();
            if ($state['timer']['running']) {
                $state['timer']['baseMinute']     = OverlayState::getTimerMinute($state['timer']);
                $state['timer']['running']        = false;
                $state['timer']['startTimestamp'] = null;
            }
            OverlayState::addHistory($state, 'timer/pause', []);
            OverlayState::save($state);
            $res->json($state['timer']);
        });

        $r->post('/timer/reset', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $state = OverlayState::load();
            $state['timer'] = [
                'running'        => false,
                'startTimestamp' => null,
                'baseMinute'     => 0,
                'period'         => 'PT',
                'addedTime'      => 0,
            ];
            OverlayState::addHistory($state, 'timer/reset', []);
            OverlayState::save($state);
            $res->json($state['timer']);
        });

        $r->patch('/timer/period', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $state = OverlayState::load();
            // Pausar el timer antes de cambiar período
            if ($state['timer']['running']) {
                $state['timer']['baseMinute'] = OverlayState::getTimerMinute($state['timer']);
                $state['timer']['running']    = false;
                $state['timer']['startTimestamp'] = null;
            }
            if (isset($req->body['period'])) {
                $state['timer']['period'] = $req->body['period'];
            }
            if (isset($req->body['baseMinute'])) {
                $state['timer']['baseMinute'] = (int)$req->body['baseMinute'];
            }
            OverlayState::addHistory($state, 'timer/period', $req->body);
            OverlayState::save($state);
            $res->json($state['timer']);
        });

        $r->patch('/timer/added', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $state = OverlayState::load();
            $state['timer']['addedTime'] = (int)($req->body['addedTime'] ?? 0);
            OverlayState::save($state);
            $res->json($state['timer']);
        });

        // ---------------------------------------------------------------
        // Incidencias
        // ---------------------------------------------------------------
        $r->post('/incidents', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $state = OverlayState::load();
            $id    = (string)round(microtime(true) * 1000);
            $incident = [
                'id'        => $id,
                'type'      => $req->body['type']      ?? 'gol',
                'team'      => $req->body['team']      ?? 'local',
                'player'    => $req->body['player']    ?? '',
                'playerOut' => $req->body['playerOut'] ?? '',
                'minute'    => isset($req->body['minute']) ? (int)$req->body['minute'] : null,
                'timestamp' => (int)$id,
            ];
            $state['incidents'][] = $incident;
            OverlayState::addHistory($state, 'incidents/add', $incident);
            OverlayState::save($state);
            $res->json($incident);
        });

        $r->delete('/incidents/:id', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $state = OverlayState::load();
            $id    = $req->params['id'] ?? '';
            $state['incidents'] = array_values(array_filter(
                $state['incidents'],
                fn($i) => ($i['id'] ?? '') !== $id
            ));
            OverlayState::addHistory($state, 'incidents/delete', ['id' => $id]);
            OverlayState::save($state);
            $res->json(['ok' => true]);
        });

        // ---------------------------------------------------------------
        // Widgets
        // ---------------------------------------------------------------
        $r->patch('/widgets', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $state = OverlayState::load();
            $allowed = ['marcador', 'formaciones', 'otrosPartidos', 'tablaPosiciones', 'ticker', 'publicidad', 'estadisticas', 'alerta', 'formacionPrevia'];
            $updates = array_intersect_key($req->body, array_flip($allowed));
            OverlayState::patch($state['widgets'], $updates);
            OverlayState::addHistory($state, 'patch/widgets', $updates);
            OverlayState::save($state);
            $res->json($state['widgets']);
        });

        // ---------------------------------------------------------------
        // Alertas
        // ---------------------------------------------------------------
        $r->patch('/alerts', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $state = OverlayState::load();
            $allowed = ['active', 'type', 'team', 'player', 'minute'];
            $updates = array_intersect_key($req->body, array_flip($allowed));
            OverlayState::patch($state['alerts'], $updates);
            // autoClean se maneja en el cliente (JS setTimeout 12s)
            OverlayState::addHistory($state, 'patch/alerts', $updates);
            OverlayState::save($state);
            $res->json($state['alerts']);
        });

        // ---------------------------------------------------------------
        // Modo emergencia (toggle)
        // ---------------------------------------------------------------
        $r->post('/emergency', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $state = OverlayState::load();
            $state['emergencyMode'] = !$state['emergencyMode'];
            OverlayState::addHistory($state, 'toggle/emergency', ['value' => $state['emergencyMode']]);
            OverlayState::save($state);
            $res->json(['emergencyMode' => $state['emergencyMode']]);
        });

        // ---------------------------------------------------------------
        // Publicidad
        // ---------------------------------------------------------------
        $r->patch('/ads', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $state   = OverlayState::load();
            $allowed = ['active', 'current', 'sponsor', 'logoSponsor', 'autoEnabled', 'autoIntervalMin', 'nextAt'];
            $updates = array_intersect_key($req->body, array_flip($allowed));
            OverlayState::patch($state['ads'], $updates);
            OverlayState::addHistory($state, 'patch/ads', $updates);
            OverlayState::save($state);
            $res->json($state['ads']);
        });

        // ---------------------------------------------------------------
        // Estadísticas
        // ---------------------------------------------------------------
        $r->patch('/stats', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $state = OverlayState::load();
            if (isset($req->body['local']) && is_array($req->body['local'])) {
                OverlayState::patch($state['stats']['local'], $req->body['local']);
            }
            if (isset($req->body['visitante']) && is_array($req->body['visitante'])) {
                OverlayState::patch($state['stats']['visitante'], $req->body['visitante']);
            }
            if (isset($req->body['jugadorDestacado']) && is_array($req->body['jugadorDestacado'])) {
                OverlayState::patch($state['stats']['jugadorDestacado'], $req->body['jugadorDestacado']);
            }
            OverlayState::save($state);
            $res->json($state['stats']);
        });

        // ---------------------------------------------------------------
        // Ticker
        // ---------------------------------------------------------------
        $r->patch('/ticker', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $state = OverlayState::load();
            if (isset($req->body['items']) && is_array($req->body['items'])) {
                $state['ticker']['items'] = $req->body['items'];
            }
            OverlayState::save($state);
            $res->json($state['ticker']);
        });
    });
}
