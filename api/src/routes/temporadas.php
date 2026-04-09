<?php
namespace App\Routes;

use App\Router;
use App\Database;
use App\Request;
use App\Response;

use function App\Middleware\authMiddleware;
use function App\ensureDefaultSeasons;
use function App\fetchActiveSeason;
use function App\fetchSeasonById;
use function App\setActiveSeason;
use function App\buildSeasonName;
use function App\buildSeasonSlug;

require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../Season.php';

function registerTemporadasRoutes(Router $router): void {
    $router->group('/temporadas', function (Router $r) {
        $r->get('', function (Request $req, Response $res) {
            try {
                ensureDefaultSeasons();
                $rows = Database::fetchAll('SELECT id, nombre, slug, anio_inicio AS anioInicio, anio_fin AS anioFin, estado, is_active AS isActive FROM temporadas ORDER BY anio_fin DESC, anio_inicio DESC');
                $res->json($rows);
            } catch (\Throwable $e) {
                error_log('Error GET /temporadas: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al listar temporadas']);
            }
        });

        $r->get('/activa', function (Request $req, Response $res) {
            try {
                $season = fetchActiveSeason();
                $res->json([
                    'id' => (int) $season['id'],
                    'nombre' => $season['nombre'],
                    'slug' => $season['slug'],
                    'anioInicio' => (int) $season['anio_inicio'],
                    'anioFin' => (int) $season['anio_fin'],
                    'estado' => $season['estado'],
                    'isActive' => (bool) $season['is_active'],
                ]);
            } catch (\Throwable $e) {
                error_log('Error GET /temporadas/activa: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al obtener la temporada activa']);
            }
        });

        $r->post('', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            try {
                ensureDefaultSeasons();
                $startYear = (int) ($req->body['anioInicio'] ?? 0);
                $endYear = (int) ($req->body['anioFin'] ?? 0);
                $estado = trim($req->body['estado'] ?? 'borrador');

                if ($startYear < 2000 || $endYear < $startYear) {
                    return $res->status(400)->json(['error' => 'Los años de la temporada son inválidos']);
                }

                $name = trim($req->body['nombre'] ?? buildSeasonName($startYear, $endYear));
                $slug = trim($req->body['slug'] ?? buildSeasonSlug($startYear, $endYear));
                $isActive = !empty($req->body['isActive']);

                Database::execute(
                    'INSERT INTO temporadas (nombre, slug, anio_inicio, anio_fin, estado, is_active) VALUES (?, ?, ?, ?, ?, ?)',
                    [$name, $slug, $startYear, $endYear, $estado, $isActive ? 1 : 0]
                );

                $seasonId = Database::lastInsertId();
                if ($isActive) {
                    setActiveSeason($seasonId);
                }

                $season = fetchSeasonById($seasonId);
                $res->status(201)->json($season);
            } catch (\Throwable $e) {
                error_log('Error POST /temporadas: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al crear la temporada']);
            }
        });

        $r->put('/:id', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            try {
                ensureDefaultSeasons();
                $id = (int) $req->params['id'];
                if (!fetchSeasonById($id)) {
                    return $res->status(404)->json(['error' => 'Temporada no encontrada']);
                }

                $startYear = isset($req->body['anioInicio']) ? (int) $req->body['anioInicio'] : null;
                $endYear = isset($req->body['anioFin']) ? (int) $req->body['anioFin'] : null;
                $name = isset($req->body['nombre']) ? trim((string) $req->body['nombre']) : null;
                $slug = isset($req->body['slug']) ? trim((string) $req->body['slug']) : null;
                $estado = isset($req->body['estado']) ? trim((string) $req->body['estado']) : null;

                Database::execute(
                    'UPDATE temporadas SET nombre = COALESCE(?, nombre), slug = COALESCE(?, slug), anio_inicio = COALESCE(?, anio_inicio), anio_fin = COALESCE(?, anio_fin), estado = COALESCE(?, estado) WHERE id = ?',
                    [$name, $slug, $startYear, $endYear, $estado, $id]
                );

                if (!empty($req->body['isActive'])) {
                    setActiveSeason($id);
                }

                $res->json(fetchSeasonById($id));
            } catch (\Throwable $e) {
                error_log('Error PUT /temporadas/:id: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al actualizar la temporada']);
            }
        });

        $r->post('/:id/activar', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            try {
                $season = setActiveSeason((int) $req->params['id']);
                $res->json($season);
            } catch (\Throwable $e) {
                error_log('Error POST /temporadas/:id/activar: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al activar la temporada']);
            }
        });
    });
}