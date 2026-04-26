<?php
namespace App\Routes;

use App\Router;
use App\Database;
use App\Request;
use App\Response;

use function App\Middleware\authMiddleware;
use function App\ensureDefaultSeasons;
use function App\resolveSeasonId;

require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../Season.php';

function registerNoticiasRoutes(Router $router): void {
    $router->group('/noticias', function (Router $r) {
        $r->get('', function (Request $req, Response $res) {
            try {
                ensureNoticiasTables();
                $params = [];
                $where = ['n.deleted_at IS NULL'];
                $limit = max(1, min(50, (int) ($req->query['limit'] ?? 12)));
                $isAdmin = !empty($_SERVER['HTTP_AUTHORIZATION']);

                if (!empty($req->query['temporada'])) {
                    $params[] = resolveSeasonId($req->query['temporada']);
                    $where[] = 'n.temporada_id = ?';
                }
                if (!empty($req->query['categoria'])) {
                    $params[] = $req->query['categoria'];
                    $where[] = 'LOWER(n.categoria) = LOWER(?)';
                }
                if (!empty($req->query['tag'])) {
                    $params[] = '%' . trim((string) $req->query['tag']) . '%';
                    $where[] = 'LOWER(n.tag) LIKE LOWER(?)';
                }
                if (isset($req->query['featured'])) {
                    $where[] = !empty($req->query['featured']) ? 'n.destacada = 1' : 'n.destacada = 0';
                }

                if ($isAdmin) {
                    if (!empty($req->query['estado'])) {
                        $params[] = $req->query['estado'];
                        $where[] = 'LOWER(n.estado) = LOWER(?)';
                    }
                } else {
                    $where[] = "n.estado = 'publicada'";
                }

                $rows = Database::fetchAll(
                    "SELECT n.id, n.slug, n.titulo, n.volanta, n.copete, n.contenido, n.imagen_url AS imagenUrl, n.imagen_alt AS imagenAlt,
                        n.tag, n.categoria, n.autor_nombre AS autorNombre, n.estado, n.destacada AS destacada,
                        n.tipo_contenido AS tipoContenido, n.boletin_pdf_url AS boletinPdfUrl,
                        n.orden_home AS ordenHome, n.published_at AS publishedAt, n.created_at AS createdAt,
                        n.temporada_id AS temporadaId, t.nombre AS temporadaNombre
                    FROM noticias n
                    LEFT JOIN temporadas t ON n.temporada_id = t.id
                    WHERE " . implode(' AND ', $where) . "
                    ORDER BY n.destacada DESC, n.orden_home ASC, COALESCE(n.published_at, n.created_at) DESC
                    LIMIT $limit",
                    $params
                );

                $res->json($rows);
            } catch (\Throwable $e) {
                error_log('Error GET /noticias: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al listar noticias']);
            }
        });

        $r->get('/:id', function (Request $req, Response $res) {
            try {
                ensureNoticiasTables();
                $idOrSlug = trim((string) $req->params['id']);
                $field = ctype_digit($idOrSlug) ? 'n.id' : 'n.slug';
                $row = Database::fetchOne(
                    "SELECT n.id, n.slug, n.titulo, n.volanta, n.copete, n.contenido, n.imagen_url AS imagenUrl, n.imagen_alt AS imagenAlt,
                        n.tag, n.categoria, n.autor_nombre AS autorNombre, n.estado, n.destacada AS destacada,
                        n.tipo_contenido AS tipoContenido, n.boletin_pdf_url AS boletinPdfUrl, n.galeria_json AS galeriaJson,
                        n.orden_home AS ordenHome, n.published_at AS publishedAt, n.created_at AS createdAt,
                        n.temporada_id AS temporadaId, t.nombre AS temporadaNombre
                    FROM noticias n
                    LEFT JOIN temporadas t ON n.temporada_id = t.id
                    WHERE n.deleted_at IS NULL AND $field = ?",
                    [$idOrSlug]
                );

                if (!$row) {
                    return $res->status(404)->json(['error' => 'Noticia no encontrada']);
                }

                $related = Database::fetchAll(
                    "SELECT nr.noticia_relacionada_id AS id, n.slug, n.titulo, n.copete, n.imagen_url AS imagenUrl, n.tag, n.categoria
                    FROM noticias_relacionadas nr
                    JOIN noticias n ON nr.noticia_relacionada_id = n.id
                    WHERE nr.noticia_id = ? AND n.deleted_at IS NULL AND n.estado = 'publicada'
                    ORDER BY n.destacada DESC, n.orden_home ASC, COALESCE(n.published_at, n.created_at) DESC",
                    [(int) $row['id']]
                );

                $row['relacionadas'] = $related;
                $row['galeria'] = normalizeGaleriaFromStorage($row['galeriaJson'] ?? null);
                $res->json($row);
            } catch (\Throwable $e) {
                error_log('Error GET /noticias/:id: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al obtener la noticia']);
            }
        });

        $r->post('', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            try {
                ensureNoticiasTables();
                $payload = normalizeNoticiaPayload($req);
                Database::execute(
                    'INSERT INTO noticias (slug, titulo, volanta, copete, contenido, imagen_url, imagen_alt, tag, categoria, autor_nombre, estado, destacada, tipo_contenido, boletin_pdf_url, galeria_json, orden_home, temporada_id, published_at)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                    [
                        $payload['slug'],
                        $payload['titulo'],
                        $payload['volanta'],
                        $payload['copete'],
                        $payload['contenido'],
                        $payload['imagenUrl'],
                        $payload['imagenAlt'],
                        $payload['tag'],
                        $payload['categoria'],
                        $payload['autorNombre'],
                        $payload['estado'],
                        $payload['destacada'] ? 1 : 0,
                        $payload['tipoContenido'],
                        $payload['boletinPdfUrl'],
                        $payload['galeriaJson'],
                        $payload['ordenHome'],
                        $payload['temporadaId'],
                        $payload['publishedAt'],
                    ]
                );

                $newsId = Database::lastInsertId();
                syncNoticiasRelacionadas($newsId, $payload['relacionadas']);
                $res->status(201)->json(fetchNoticiaById($newsId));
            } catch (\Throwable $e) {
                error_log('Error POST /noticias: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al crear la noticia: ' . $e->getMessage()]);
            }
        });

        $r->put('/:id', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            try {
                ensureNoticiasTables();
                $id = (int) $req->params['id'];
                if (!fetchNoticiaById($id)) {
                    return $res->status(404)->json(['error' => 'Noticia no encontrada']);
                }

                $payload = normalizeNoticiaPayload($req, true);
                Database::execute(
                    'UPDATE noticias SET slug = COALESCE(?, slug), titulo = COALESCE(?, titulo), volanta = COALESCE(?, volanta), copete = COALESCE(?, copete), contenido = COALESCE(?, contenido), imagen_url = COALESCE(?, imagen_url), imagen_alt = COALESCE(?, imagen_alt), tag = COALESCE(?, tag), categoria = COALESCE(?, categoria), autor_nombre = COALESCE(?, autor_nombre), estado = COALESCE(?, estado), destacada = COALESCE(?, destacada), tipo_contenido = COALESCE(?, tipo_contenido), boletin_pdf_url = CASE WHEN ? IS NULL THEN boletin_pdf_url WHEN ? = \'\' THEN NULL ELSE ? END, galeria_json = COALESCE(?, galeria_json), orden_home = COALESCE(?, orden_home), temporada_id = COALESCE(?, temporada_id), published_at = COALESCE(?, published_at) WHERE id = ?',
                    [
                        $payload['slug'],
                        $payload['titulo'],
                        $payload['volanta'],
                        $payload['copete'],
                        $payload['contenido'],
                        $payload['imagenUrl'],
                        $payload['imagenAlt'],
                        $payload['tag'],
                        $payload['categoria'],
                        $payload['autorNombre'],
                        $payload['estado'],
                        $payload['destacada'] === null ? null : ($payload['destacada'] ? 1 : 0),
                        $payload['tipoContenido'],
                        $payload['boletinPdfUrl'],
                        $payload['boletinPdfUrl'],
                        $payload['boletinPdfUrl'],
                        $payload['galeriaJson'],
                        $payload['ordenHome'],
                        $payload['temporadaId'],
                        $payload['publishedAt'],
                        $id,
                    ]
                );

                if ($payload['relacionadas'] !== null) {
                    syncNoticiasRelacionadas($id, $payload['relacionadas']);
                }

                $res->json(fetchNoticiaById($id));
            } catch (\Throwable $e) {
                error_log('Error PUT /noticias/:id: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al actualizar la noticia']);
            }
        });

        $r->delete('/:id', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            try {
                ensureNoticiasTables();
                $id = (int) $req->params['id'];
                Database::execute('UPDATE noticias SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?', [$id]);
                $res->json(['ok' => true]);
            } catch (\Throwable $e) {
                error_log('Error DELETE /noticias/:id: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al eliminar la noticia']);
            }
        });
    });
}

function ensureNoticiasTables(): void {
    ensureDefaultSeasons();
    Database::execute(
        "CREATE TABLE IF NOT EXISTS noticias (
            id INT AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(180) NOT NULL,
            titulo VARCHAR(255) NOT NULL,
            copete TEXT NOT NULL,
            contenido MEDIUMTEXT NOT NULL,
            imagen_url VARCHAR(500) NULL,
            imagen_alt VARCHAR(255) NULL,
            tag VARCHAR(80) NULL,
            categoria VARCHAR(80) NOT NULL,
            autor_nombre VARCHAR(120) NOT NULL DEFAULT 'Redacción Juego de Equipo',
            estado VARCHAR(20) NOT NULL DEFAULT 'borrador',
            destacada TINYINT(1) NOT NULL DEFAULT 0,
            tipo_contenido VARCHAR(20) NOT NULL DEFAULT 'comun',
            boletin_pdf_url VARCHAR(500) NULL,
            galeria_json MEDIUMTEXT NULL,
            orden_home INT NOT NULL DEFAULT 999,
            temporada_id INT NOT NULL,
            published_at DATETIME NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at DATETIME NULL,
            UNIQUE KEY noticias_slug_unique (slug),
            KEY noticias_estado_idx (estado),
            KEY noticias_temporada_idx (temporada_id),
            KEY noticias_destacada_idx (destacada)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    Database::execute(
        "CREATE TABLE IF NOT EXISTS noticias_relacionadas (
            noticia_id INT NOT NULL,
            noticia_relacionada_id INT NOT NULL,
            PRIMARY KEY (noticia_id, noticia_relacionada_id),
            CONSTRAINT fk_noticias_relacionadas_principal FOREIGN KEY (noticia_id) REFERENCES noticias(id) ON DELETE CASCADE,
            CONSTRAINT fk_noticias_relacionadas_secundaria FOREIGN KEY (noticia_relacionada_id) REFERENCES noticias(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    // Migración: agregar volanta si no existe
    try {
        Database::execute("ALTER TABLE noticias ADD COLUMN volanta VARCHAR(255) NULL AFTER titulo");
    } catch (\Throwable $e) {
        // La columna ya existe; ignorar
    }

    try {
        Database::execute("ALTER TABLE noticias ADD COLUMN tipo_contenido VARCHAR(20) NOT NULL DEFAULT 'comun' AFTER destacada");
    } catch (\Throwable $e) {
        // La columna ya existe; ignorar
    }

    try {
        Database::execute("ALTER TABLE noticias ADD COLUMN boletin_pdf_url VARCHAR(500) NULL AFTER tipo_contenido");
    } catch (\Throwable $e) {
        // La columna ya existe; ignorar
    }

    try {
        Database::execute("ALTER TABLE noticias ADD COLUMN galeria_json MEDIUMTEXT NULL AFTER boletin_pdf_url");
    } catch (\Throwable $e) {
        // La columna ya existe; ignorar
    }
}

function fetchNoticiaById(int $id): array|false {
    $row = Database::fetchOne(
        "SELECT n.id, n.slug, n.titulo, n.volanta, n.copete, n.contenido, n.imagen_url AS imagenUrl, n.imagen_alt AS imagenAlt,
            n.tag, n.categoria, n.autor_nombre AS autorNombre, n.estado, n.destacada AS destacada,
            n.tipo_contenido AS tipoContenido, n.boletin_pdf_url AS boletinPdfUrl, n.galeria_json AS galeriaJson,
            n.orden_home AS ordenHome, n.published_at AS publishedAt, n.created_at AS createdAt,
            n.temporada_id AS temporadaId, t.nombre AS temporadaNombre
        FROM noticias n
        LEFT JOIN temporadas t ON n.temporada_id = t.id
        WHERE n.id = ? AND n.deleted_at IS NULL",
        [$id]
    );

    if (!$row) {
        return false;
    }

    $row['relacionadas'] = Database::fetchAll(
        'SELECT noticia_relacionada_id AS id FROM noticias_relacionadas WHERE noticia_id = ? ORDER BY noticia_relacionada_id',
        [$id]
    );
    $row['galeria'] = normalizeGaleriaFromStorage($row['galeriaJson'] ?? null);

    return $row;
}

function normalizeNoticiaPayload(Request $req, bool $partial = false): array {
    $titulo = array_key_exists('titulo', $req->body) ? trim((string) $req->body['titulo']) : null;
    $copete = array_key_exists('copete', $req->body) ? trim((string) $req->body['copete']) : null;
    $contenido = array_key_exists('contenido', $req->body) ? trim((string) $req->body['contenido']) : null;
    $estado = array_key_exists('estado', $req->body) ? trim((string) $req->body['estado']) : 'borrador';
    $tipoContenido = array_key_exists('tipoContenido', $req->body) ? normalizeTipoContenido((string) $req->body['tipoContenido']) : ($partial ? null : 'comun');
    $boletinPdfUrl = array_key_exists('boletinPdfUrl', $req->body) ? trim((string) $req->body['boletinPdfUrl']) : null;
    $galeria = array_key_exists('galeria', $req->body) ? normalizeGaleria($req->body['galeria']) : [];

    if (!$partial && $tipoContenido === null) {
        $tipoContenido = 'comun';
    }

    if (!$partial && $estado !== 'borrador' && !$titulo) {
        throw new \InvalidArgumentException('Título es obligatorio para publicar');
    }

    if (!$partial && $estado !== 'borrador' && !$copete) {
        throw new \InvalidArgumentException('Copete es obligatorio para publicar');
    }

    if (!$partial && $estado !== 'borrador') {
        if ($tipoContenido === 'galeria' && count($galeria) === 0) {
            throw new \InvalidArgumentException('La nota galería requiere al menos una imagen');
        }
        if (($tipoContenido === 'comun' || $tipoContenido === 'boletin') && !$contenido) {
            throw new \InvalidArgumentException('El contenido es obligatorio para este tipo de nota');
        }
        if ($tipoContenido === 'boletin' && $boletinPdfUrl === '') {
            throw new \InvalidArgumentException('La nota boletín requiere un PDF');
        }
    }

    // Para borradores: garantizar valores no-null que cumplan las restricciones de la tabla
    if (!$partial && $estado === 'borrador') {
        $titulo    = $titulo ?: '';
        $copete    = $copete ?: '';
        if ($tipoContenido === 'galeria') {
            $contenido = buildGaleriaHtml($galeria);
        } else {
            $contenido = $contenido ?: '';
        }
    }

    if ($tipoContenido === 'galeria') {
        $contenido = buildGaleriaHtml($galeria);
    }

    if ($tipoContenido !== null && $tipoContenido !== 'boletin') {
        $boletinPdfUrl = $partial ? '' : null;
    }

    $temporadaId = array_key_exists('temporadaId', $req->body) ? resolveSeasonId($req->body['temporadaId']) : ($partial ? null : resolveSeasonId(null));
    $slug = array_key_exists('slug', $req->body) && trim((string) $req->body['slug']) !== ''
        ? normalizeSlug((string) $req->body['slug'])
        : ($titulo !== '' && $titulo !== null ? normalizeSlug($titulo) : null);

    // Slug único para borradores sin título
    if (!$partial && !$slug) {
        $slug = 'borrador-' . uniqid();
    }

    // Garantizar que categoria no sea vacía
    $categoria = array_key_exists('categoria', $req->body) ? trim((string) $req->body['categoria']) : ($partial ? null : 'General');
    if (!$partial && ($categoria === '' || $categoria === null)) {
        $categoria = 'General';
    }

    return [
        'slug' => $slug,
        'titulo' => $titulo,
        'volanta' => array_key_exists('volanta', $req->body) ? (trim((string) $req->body['volanta']) ?: null) : null,
        'copete' => $copete,
        'contenido' => $contenido,
        'imagenUrl' => array_key_exists('imagenUrl', $req->body) ? trim((string) $req->body['imagenUrl']) : null,
        'imagenAlt' => array_key_exists('imagenAlt', $req->body) ? trim((string) $req->body['imagenAlt']) : null,
        'tag' => array_key_exists('tag', $req->body) ? trim((string) $req->body['tag']) : null,
        'categoria' => $categoria,
        'autorNombre' => array_key_exists('autorNombre', $req->body) ? trim((string) $req->body['autorNombre']) : ($partial ? null : 'Redacción Juego de Equipo'),
        'estado' => array_key_exists('estado', $req->body) ? trim((string) $req->body['estado']) : ($partial ? null : 'borrador'),
        'destacada' => array_key_exists('destacada', $req->body) ? (bool) $req->body['destacada'] : ($partial ? null : false),
        'tipoContenido' => $tipoContenido,
        'boletinPdfUrl' => $boletinPdfUrl !== '' ? $boletinPdfUrl : null,
        'galeriaJson' => array_key_exists('galeria', $req->body) ? json_encode($galeria, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : ($partial ? null : json_encode([], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
        'ordenHome' => array_key_exists('ordenHome', $req->body) ? (int) $req->body['ordenHome'] : ($partial ? null : 999),
        'temporadaId' => $temporadaId,
        'publishedAt' => array_key_exists('publishedAt', $req->body) ? ($req->body['publishedAt'] ?: null) : ($partial ? null : null),
        'relacionadas' => array_key_exists('relacionadas', $req->body) ? normalizeRelacionadas($req->body['relacionadas']) : null,
    ];
}

function normalizeTipoContenido(string $value): ?string {
    $value = strtolower(trim($value));
    if ($value === '') {
        return null;
    }

    if (!in_array($value, ['comun', 'galeria', 'boletin'], true)) {
        return 'comun';
    }

    return $value;
}

function normalizeGaleria(mixed $raw): array {
    if (!is_array($raw)) {
        return [];
    }

    $normalized = [];
    foreach ($raw as $item) {
        if (!is_array($item)) {
            continue;
        }

        $imagenUrl = trim((string) ($item['imagenUrl'] ?? ''));
        if ($imagenUrl === '') {
            continue;
        }

        $epigrafe = trim((string) ($item['epigrafe'] ?? ''));
        $normalized[] = [
            'imagenUrl' => $imagenUrl,
            'epigrafe' => $epigrafe,
        ];
    }

    return $normalized;
}

function normalizeGaleriaFromStorage(?string $galeriaJson): array {
    if (!$galeriaJson) {
        return [];
    }

    $decoded = json_decode($galeriaJson, true);
    if (!is_array($decoded)) {
        return [];
    }

    return normalizeGaleria($decoded);
}

function buildGaleriaHtml(array $galeria): string {
    if (count($galeria) === 0) {
        return '';
    }

    $chunks = [];
    foreach ($galeria as $item) {
        $url = htmlspecialchars((string) ($item['imagenUrl'] ?? ''), ENT_QUOTES, 'UTF-8');
        if ($url === '') {
            continue;
        }

        $epigrafe = trim((string) ($item['epigrafe'] ?? ''));
        $caption = $epigrafe !== '' ? '<figcaption>' . htmlspecialchars($epigrafe, ENT_QUOTES, 'UTF-8') . '</figcaption>' : '';
        $chunks[] = '<figure class="gallery-item"><img src="' . $url . '" alt="" loading="lazy" />' . $caption . '</figure>';
    }

    return implode("\n", $chunks);
}

function normalizeSlug(string $value): string {
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/i', '-', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value);
    $value = trim((string) $value, '-');

    return $value !== '' ? $value : 'noticia';
}

function normalizeRelacionadas(mixed $raw): array {
    if (!is_array($raw)) {
        return [];
    }

    return array_values(array_unique(array_filter(array_map(static function ($id) {
        return is_numeric($id) ? (int) $id : null;
    }, $raw))));
}

function syncNoticiasRelacionadas(int $newsId, array $relatedIds): void {
    Database::execute('DELETE FROM noticias_relacionadas WHERE noticia_id = ?', [$newsId]);

    foreach ($relatedIds as $relatedId) {
        if ($relatedId === $newsId) {
            continue;
        }

        Database::execute(
            'INSERT INTO noticias_relacionadas (noticia_id, noticia_relacionada_id) VALUES (?, ?)',
            [$newsId, $relatedId]
        );
    }
}