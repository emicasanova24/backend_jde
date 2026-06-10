<?php
namespace App\Routes;

use App\Router;
use App\Database;
use App\Request;
use App\Response;

use function App\Middleware\authMiddleware;

require_once __DIR__ . '/../middleware/auth.php';

// ─────────────────────────────────────────────────
// Tabla + seed inicial
// ─────────────────────────────────────────────────
function ensureBannersTable(): void {
    static $done = false;
    if ($done) return;
    $done = true;

    Database::execute("
        CREATE TABLE IF NOT EXISTS `banners` (
          `id`          INT            NOT NULL AUTO_INCREMENT,
          `position_id` VARCHAR(64)    NOT NULL,
          `label`       VARCHAR(120)   NOT NULL DEFAULT '',
          `page`        VARCHAR(60)    NOT NULL DEFAULT '',
          `type`        VARCHAR(32)    NOT NULL DEFAULT 'rectangle',
          `width`       SMALLINT       NOT NULL DEFAULT 300,
          `height`      SMALLINT       NOT NULL DEFAULT 250,
          `image_url`   VARCHAR(512)   NULL,
          `link_url`    VARCHAR(512)   NULL,
          `alt_text`    VARCHAR(255)   NULL DEFAULT '',
          `active`      TINYINT(1)     NOT NULL DEFAULT 0,
          `updated_at`  DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uk_position_id` (`position_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $count = (int) (Database::fetchOne("SELECT COUNT(*) AS c FROM `banners`")['c'] ?? 0);
    if ($count > 0) return;

    $seed = [
        ['home-hero-d',     'Home Hero — Desktop',             'home', 'billboard',     970,  90,  null, null, '', 0],
        ['home-news-d',     'Home Noticias — Desktop',          'home', 'leaderboard',   728,  90,  null, null, '', 0],
        ['home-fxt-tbl-d1', 'Home Fixture/Tabla #1 — Desktop', 'home', 'rectangle',     300, 250,  null, null, '', 0],
        ['home-fxt-tbl-d2', 'Home Fixture/Tabla #2 — Desktop', 'home', 'rectangle',     300, 250,
            '/banners-sponsors/eduardo-banner.gif',
            'https://api.whatsapp.com/send?phone=5492392517048&text=Hola%20Eduardo!%20Me%20gustar%C3%ADa%20consultarte%20por%20un%20presupuesto.',
            'Eduardo Banner', 1],
        ['home-tbl-gol',    'Home Tabla/Goleadores',           'home', 'leaderboard',   728,  90,  null, null, '', 0],
        ['home-prefooter-d','Home Pre-footer — Desktop',       'home', 'leaderboard',   728,  90,
            '/banners-sponsors/banner-juanoliva.png',
            'https://api.whatsapp.com/send?phone=5492392559254&text=Hola%20Juan!%20Me%20gustar%C3%ADa%20consultarte.',
            'Juan Oliva', 1],
        ['home-hero-m',     'Home Hero — Mobile',              'home', 'mobile-large',  320, 100,
            '/banners-sponsors/banner-el-punto.png', null, 'El Punto', 1],
        ['home-news-m',     'Home Noticias — Mobile',          'home', 'mobile-banner', 320,  50,
            '/banners-sponsors/pampa-banner.png', null, 'Pampa', 1],
        ['home-fxt-tbl-m',  'Home Fixture/Tabla — Mobile',    'home', 'rectangle',     300, 250,
            '/banners-sponsors/eduardo-banner.gif',
            'https://api.whatsapp.com/send?phone=5492392517048&text=Hola%20Eduardo!%20Me%20gustar%C3%ADa%20consultarte%20por%20un%20presupuesto.',
            'Eduardo Banner', 1],
        ['home-tbl-gol-m',   'Home Tabla/Goleadores — Mobile',  'home', 'mobile-banner', 320,  50,  null, null, '', 0],
        ['home-gol-tarj-m', 'Home Goleadores/Tarjetas — Mobile','home', 'mobile-banner', 320,  50,  null, null, '', 0],
        ['home-prefooter-m','Home Pre-footer — Mobile',        'home', 'mobile-banner', 320,  50,  null, null, '', 0],
        ['home-sticky-m',   'Home Sticky — Mobile',           'home', 'mobile-sticky', 320,  50,  null, null, '', 0],
        ['nota-article-d',  'Nota Artículo — Desktop',        'nota', 'leaderboard',   728,  90,  null, null, '', 0],
        ['nota-article-m',  'Nota Artículo — Mobile',         'nota', 'mobile-large',  320, 100,  null, null, '', 0],
        ['nota-sidebar',    'Nota Sidebar — Desktop',         'nota', 'half-page',     300, 600,  null, null, '', 0],
    ];

    foreach ($seed as $row) {
        Database::execute(
            "INSERT IGNORE INTO `banners`
             (`position_id`,`label`,`page`,`type`,`width`,`height`,`image_url`,`link_url`,`alt_text`,`active`)
             VALUES (?,?,?,?,?,?,?,?,?,?)",
            $row
        );
    }
}

// ─────────────────────────────────────────────────
// Regenerar cache JSON en disco
// ─────────────────────────────────────────────────
function regenerateBannersCache(): void {
    $rows = Database::fetchAll(
        "SELECT `position_id`, `type`, `width`, `height`, `image_url`, `link_url`, `alt_text`, `active`
         FROM `banners`
         ORDER BY `id` ASC"
    );

    $indexed = [];
    foreach ($rows as $row) {
        $indexed[$row['position_id']] = [
            'type'      => $row['type'],
            'width'     => (int) $row['width'],
            'height'    => (int) $row['height'],
            'image_url' => $row['image_url'],
            'link_url'  => $row['link_url'],
            'alt_text'  => $row['alt_text'],
            'active'    => (bool) $row['active'],
        ];
    }

    $cacheDir  = dirname(__DIR__, 3) . '/uploads';
    $cachePath = $cacheDir . '/banners-cache.json';

    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }

    file_put_contents($cachePath, json_encode($indexed, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
}

// ─────────────────────────────────────────────────
// Rutas
// ─────────────────────────────────────────────────
function registerBannersRoutes(Router $router): void {
    $router->group('/banners', function (Router $r) {

        // GET /banners — lista completa (público)
        $r->get('', function (Request $req, Response $res) {
            try {
                ensureBannersTable();
                $rows = Database::fetchAll(
                    "SELECT `id`, `position_id` AS positionId, `label`, `page`, `type`,
                            `width`, `height`, `image_url` AS imageUrl,
                            `link_url` AS linkUrl, `alt_text` AS altText, `active`,
                            `updated_at` AS updatedAt
                     FROM `banners`
                     ORDER BY `id` ASC"
                );
                // Normalizar tipos
                foreach ($rows as &$row) {
                    $row['width']  = (int) $row['width'];
                    $row['height'] = (int) $row['height'];
                    $row['active'] = (bool) $row['active'];
                }
                unset($row);
                $res->json($rows);
            } catch (\Throwable $e) {
                error_log('Error GET /banners: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al listar banners']);
            }
        });

        // PUT /banners/:id — actualizar un banner (requiere auth)
        $r->put('/:id', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            try {
                ensureBannersTable();
                $id = (int) $req->params['id'];

                $body = $req->body ?? [];

                // Verificar que el banner existe
                $banner = Database::fetchOne("SELECT * FROM `banners` WHERE `id` = ?", [$id]);
                if (!$banner) {
                    $res->status(404)->json(['error' => 'Banner no encontrado']);
                    return;
                }

                // Construir SET dinámico con solo los campos enviados
                $allowed = ['type', 'image_url', 'link_url', 'alt_text', 'active', 'width', 'height'];
                $sets    = [];
                $params  = [];

                foreach ($allowed as $field) {
                    $bodyKey = lcfirst(str_replace('_', '', ucwords($field, '_')));
                    // Aceptar tanto snake_case como camelCase
                    if (array_key_exists($field, $body)) {
                        $sets[]   = "`$field` = ?";
                        $params[] = $body[$field];
                    } elseif (array_key_exists($bodyKey, $body)) {
                        $sets[]   = "`$field` = ?";
                        $params[] = $body[$bodyKey];
                    }
                }

                if (empty($sets)) {
                    $res->status(400)->json(['error' => 'No se enviaron campos para actualizar']);
                    return;
                }

                // Sanitizar
                foreach ($params as $i => $v) {
                    if ($params[$i] !== null) {
                        $params[$i] = htmlspecialchars_decode(trim((string) $v), ENT_QUOTES);
                    }
                }

                $params[] = $id;
                Database::execute(
                    "UPDATE `banners` SET " . implode(', ', $sets) . " WHERE `id` = ?",
                    $params
                );

                // Regenerar cache JSON para páginas públicas
                regenerateBannersCache();

                $updated = Database::fetchOne(
                    "SELECT `id`, `position_id` AS positionId, `label`, `page`, `type`,
                            `width`, `height`, `image_url` AS imageUrl,
                            `link_url` AS linkUrl, `alt_text` AS altText, `active`,
                            `updated_at` AS updatedAt
                     FROM `banners` WHERE `id` = ?",
                    [$id]
                );
                $updated['width']  = (int) $updated['width'];
                $updated['height'] = (int) $updated['height'];
                $updated['active'] = (bool) $updated['active'];

                $res->json($updated);
            } catch (\Throwable $e) {
                error_log('Error PUT /banners/:id — ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al actualizar el banner']);
            }
        });

        // DELETE image_url — limpiar imagen de un banner (requiere auth)
        $r->delete('/:id/image', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            try {
                ensureBannersTable();
                $id = (int) $req->params['id'];

                $banner = Database::fetchOne("SELECT * FROM `banners` WHERE `id` = ?", [$id]);
                if (!$banner) {
                    $res->status(404)->json(['error' => 'Banner no encontrado']);
                    return;
                }

                Database::execute(
                    "UPDATE `banners` SET `image_url` = NULL, `link_url` = NULL, `alt_text` = '', `active` = 0 WHERE `id` = ?",
                    [$id]
                );

                regenerateBannersCache();
                $res->json(['ok' => true]);
            } catch (\Throwable $e) {
                error_log('Error DELETE /banners/:id/image — ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al limpiar el banner']);
            }
        });
    });
}
