<?php
namespace App\Routes;

use App\Router;
use App\Database;
use App\Request;
use App\Response;

use function App\Middleware\authMiddleware;

require_once __DIR__ . '/../middleware/auth.php';

function normalizeEscudoPath(?string $escudo): string {
    $path = trim((string) $escudo);
    if ($path === '') {
        return '';
    }

    if (str_starts_with($path, '/logos/')) {
        return '/assets-equipos/logos/' . basename($path);
    }

    return $path;
}

function normalizeEquipoRecord(array $row): array {
    $row['escudo'] = normalizeEscudoPath($row['escudo'] ?? '');
    return $row;
}

function registerEquiposRoutes(Router $router): void {
    $router->group('/equipos', function (Router $r) {

        $r->get('', function (Request $req, Response $res) {
            try {
                $params = [];
                $query = "SELECT id, nombre, estadio, escudo, categoria FROM equipos";
                if (!empty($req->query['categoria'])) {
                    $params[] = $req->query['categoria'];
                    $query .= " WHERE LOWER(categoria) = LOWER(?)";
                }
                $query .= " ORDER BY categoria, nombre";
                $rows = Database::fetchAll($query, $params);
                $res->json(array_map('App\\Routes\\normalizeEquipoRecord', $rows));
            } catch (\Exception $e) {
                error_log('Error GET /equipos: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al listar equipos']);
            }
        });

        $r->post('', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $nombre = $req->body['nombre'] ?? '';
            $estadio = $req->body['estadio'] ?? '';
            $escudo = normalizeEscudoPath($req->body['escudo'] ?? '');
            $categoria = $req->body['categoria'] ?? '';
            if (!$nombre || !$estadio || !$categoria) {
                return $res->status(400)->json(['error' => 'Falta nombre, estadio o categoría']);
            }
            try {
                Database::execute("INSERT INTO equipos (nombre, estadio, escudo, categoria) VALUES (?, ?, ?, ?)", [$nombre, $estadio, $escudo, $categoria]);
                $row = fetchEquipoById(Database::lastInsertId());
                $res->status(201)->json($row);
            } catch (\Exception $e) {
                error_log('Error POST /equipos: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al crear equipo']);
            }
        });

        $r->put('/:id', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $id = (int)$req->params['id'];
            $nombre = $req->body['nombre'] ?? null;
            $estadio = $req->body['estadio'] ?? null;
            $escudo = array_key_exists('escudo', $req->body) ? normalizeEscudoPath($req->body['escudo']) : null;
            $categoria = $req->body['categoria'] ?? null;
            try {
                $row = fetchEquipoById($id);
                if (!$row) return $res->status(404)->json(['error' => 'Equipo no encontrado']);
                Database::execute(
                    "UPDATE equipos SET nombre = COALESCE(?, nombre), estadio = COALESCE(?, estadio), escudo = COALESCE(?, escudo), categoria = COALESCE(?, categoria) WHERE id = ?",
                    [$nombre, $estadio, $escudo, $categoria, $id]
                );
                $row = fetchEquipoById($id);
                $res->json($row);
            } catch (\Exception $e) {
                error_log('Error PUT /equipos/:id: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al actualizar equipo']);
            }
        });

        $r->delete('/:id', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $id = (int)$req->params['id'];
            try {
                $count = Database::execute("DELETE FROM equipos WHERE id = ?", [$id]);
                if (!$count) return $res->status(404)->json(['error' => 'Equipo no encontrado']);
                $res->json(['ok' => true]);
            } catch (\Exception $e) {
                error_log('Error DELETE /equipos/:id: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al eliminar equipo']);
            }
        });

        $r->post('/sync', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $input = $req->body['equipos'] ?? null;
            if (!is_array($input)) {
                return $res->status(400)->json(['error' => 'Debe enviar { equipos: [...] }']);
            }
            $names = array_values(array_unique(array_filter(array_map(fn($e) => trim($e['nombre'] ?? ''), $input))));

            try {
                if (count($names)) {
                    $placeholders = implode(', ', array_fill(0, count($names), '?'));
                    Database::execute("DELETE FROM equipos WHERE nombre NOT IN ($placeholders)", $names);
                } else {
                    Database::execute("DELETE FROM equipos");
                }

                foreach ($input as $equipo) {
                    $nombre = trim($equipo['nombre'] ?? '');
                    if (!$nombre) continue;
                    Database::execute(
                        "INSERT INTO equipos (nombre, estadio, escudo, categoria) VALUES (?, ?, ?, ?)
                         ON DUPLICATE KEY UPDATE estadio = VALUES(estadio), escudo = VALUES(escudo)",
                        [$nombre, $equipo['estadio'] ?? '', normalizeEscudoPath($equipo['escudo'] ?? ''), $equipo['categoria'] ?? 'Primera']
                    );
                }

                $rows = Database::fetchAll("SELECT id, nombre, estadio, escudo, categoria FROM equipos ORDER BY categoria, nombre");
                $res->json(['ok' => true, 'equipos' => array_map('App\\Routes\\normalizeEquipoRecord', $rows)]);
            } catch (\Exception $e) {
                error_log('Error POST /equipos/sync: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al sincronizar equipos']);
            }
        });
    });
}

function fetchEquipoById(int $id): array|false {
    $row = Database::fetchOne("SELECT id, nombre, estadio, escudo, categoria FROM equipos WHERE id = ?", [$id]);
    return $row ? normalizeEquipoRecord($row) : false;
}
