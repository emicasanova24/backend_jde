<?php
namespace App\Routes;

use App\Router;
use App\Database;
use App\Request;
use App\Response;

use function App\Middleware\authMiddleware;
use function App\resolveSeasonId;
use function App\fetchSeasonById;

require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../Season.php';

const CATEGORIAS = ['Primera', 'Tercera', 'Senior'];

function registerSemanarioRoutes(Router $router): void {
    $router->group('/setup', function (Router $r) {

        $r->get('/semanario-html', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            try {
                $torneo = trim($req->query['torneo'] ?? '');
                $seasonId = resolveSeasonId($req->query['temporada'] ?? null);
                $season = fetchSeasonById($seasonId);
                $config = require __DIR__ . '/../config.php';
                $baseUrl = $config['api_url'] ?: ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));

                $templatePath = realpath(__DIR__ . '/../../semanario-2026-04-02.html');
                if (!$templatePath || !file_exists($templatePath)) {
                    // Buscar en la raíz del proyecto
                    $templatePath = realpath(__DIR__ . '/../../../semanario-2026-04-02.html');
                }
                if (!$templatePath || !file_exists($templatePath)) {
                    return $res->status(500)->json(['error' => 'Plantilla del semanario no encontrada']);
                }
                $template = file_get_contents($templatePath);

                $data = [];
                foreach (CATEGORIAS as $categoria) {
                    $catParam = [$categoria];
                    $torneoParam = $torneo ? [$categoria, $torneo] : $catParam;

                    // Tabla
                    $equipos = Database::fetchAll(
                        "SELECT id, nombre, categoria FROM equipos WHERE temporada_id = ? AND LOWER(categoria) = LOWER(?)",
                        array_merge([$seasonId], $catParam)
                    );
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

                    $pWhere = ['temporada_id = ?', "estado = 'jugado'", "LOWER(categoria) = LOWER(?)"];
                    $pParams = [$seasonId, $categoria];
                    if ($torneo) {
                        $pWhere[] = "LOWER(torneo) = LOWER(?)";
                        $pParams[] = $torneo;
                    }
                    $partidos = Database::fetchAll(
                        "SELECT local, visitante, goleslocal, golesvisitante FROM partidos WHERE " . implode(' AND ', $pWhere),
                        $pParams
                    );
                    foreach ($partidos as $p) {
                        $li = $lookup[$p['local']] ?? null;
                        $vi = $lookup[$p['visitante']] ?? null;
                        if ($li === null || $vi === null) continue;
                        $tabla[$li]['pj']++; $tabla[$vi]['pj']++;
                        $gl = (int)$p['goleslocal']; $gv = (int)$p['golesvisitante'];
                        $tabla[$li]['gf'] += $gl; $tabla[$li]['gc'] += $gv;
                        $tabla[$vi]['gf'] += $gv; $tabla[$vi]['gc'] += $gl;
                        if ($gl > $gv) { $tabla[$li]['pg']++; $tabla[$vi]['pp']++; }
                        elseif ($gl < $gv) { $tabla[$vi]['pg']++; $tabla[$li]['pp']++; }
                        else { $tabla[$li]['pe']++; $tabla[$vi]['pe']++; }
                    }
                    foreach ($tabla as &$e) {
                        $e['dg'] = $e['gf'] - $e['gc'];
                        $e['puntos'] = $e['pg'] * 3 + $e['pe'];
                    }
                    unset($e);
                    usort($tabla, fn($a, $b) => ($b['puntos'] - $a['puntos']) ?: ($b['dg'] - $a['dg']) ?: ($b['gf'] - $a['gf']));

                    // Goleadores
                    $gWhere = ['p.temporada_id = ?', "ev.tipo = 'gol'", "LOWER(p.categoria) = LOWER(?)"];
                    $gParams = [$seasonId, $categoria];
                    if ($torneo) { $gWhere[] = "LOWER(p.torneo) = LOWER(?)"; $gParams[] = $torneo; }
                    $goleadores = Database::fetchAll(
                        "SELECT j.nombre, COALESCE(e.nombre, '-') AS equipo, COUNT(*) AS goles
                         FROM eventos ev JOIN partidos p ON ev.partido_id = p.id JOIN jugadores j ON ev.jugador_id = j.id LEFT JOIN equipos e ON ev.equipo_id = e.id
                         WHERE " . implode(' AND ', $gWhere) . " GROUP BY j.id, j.nombre, e.nombre ORDER BY goles DESC",
                        $gParams
                    );

                    // Tarjetas
                    $buildTarjetas = function (string $tipo) use ($categoria, $torneo) {
                        $seasonId = resolveSeasonId($_GET['temporada'] ?? null);
                        $w = ['p.temporada_id = ?', "ev.tipo = '$tipo'", "LOWER(p.categoria) = LOWER(?)"];
                        $p = [$seasonId, $categoria];
                        if ($torneo) { $w[] = "LOWER(p.torneo) = LOWER(?)"; $p[] = $torneo; }
                        return Database::fetchAll(
                            "SELECT j.nombre, COALESCE(e.nombre, '-') AS equipo, COUNT(*) AS $tipo
                             FROM eventos ev JOIN partidos p ON ev.partido_id = p.id JOIN jugadores j ON ev.jugador_id = j.id LEFT JOIN equipos e ON ev.equipo_id = e.id
                             WHERE " . implode(' AND ', $w) . " GROUP BY j.id, j.nombre, e.nombre ORDER BY $tipo DESC",
                            $p
                        );
                    };
                    $tarjetas = ['amarillas' => $buildTarjetas('amarilla'), 'rojas' => $buildTarjetas('roja')];

                    // Partidos completos
                    $mWhere = ['p.temporada_id = ?', "LOWER(p.categoria) = LOWER(?)"];
                    $mParams = [$seasonId, $categoria];
                    if ($torneo) { $mWhere[] = "LOWER(p.torneo) = LOWER(?)"; $mParams[] = $torneo; }
                    $matchRows = Database::fetchAll(
                        "SELECT p.id, p.fecha, p.hora, p.estadio, p.local AS \"localId\", p.visitante AS \"visitanteId\", p.estado,
                            p.goleslocal AS \"golesLocal\", p.golesvisitante AS \"golesVisitante\", p.amarillas, p.rojas,
                            p.torneo, p.categoria, p.jornada, p.broadcast,
                            el.nombre AS local, ev.nombre AS visitante,
                            el.escudo AS \"localEscudo\", ev.escudo AS \"visitanteEscudo\"
                        FROM partidos p LEFT JOIN equipos el ON p.local = el.id LEFT JOIN equipos ev ON p.visitante = ev.id
                        WHERE " . implode(' AND ', $mWhere) . " ORDER BY p.jornada, p.fecha, p.hora",
                        $mParams
                    );
                    foreach ($matchRows as &$m) {
                        if (!empty($m['localEscudo']) && str_starts_with($m['localEscudo'], '/')) $m['localEscudo'] = $baseUrl . $m['localEscudo'];
                        if (!empty($m['visitanteEscudo']) && str_starts_with($m['visitanteEscudo'], '/')) $m['visitanteEscudo'] = $baseUrl . $m['visitanteEscudo'];
                    }
                    unset($m);

                    // Jornadas
                    $jWhere = ['temporada_id = ?', "LOWER(categoria) = LOWER(?)", "jornada IS NOT NULL"];
                    $jParams = [$seasonId, $categoria];
                    if ($torneo) { $jWhere[] = "LOWER(torneo) = LOWER(?)"; $jParams[] = $torneo; }
                    $jornadas = Database::fetchAll(
                        "SELECT jornada, COUNT(*) AS total, SUM(CASE WHEN estado = 'jugado' THEN 1 ELSE 0 END) AS jugados FROM partidos
                         WHERE " . implode(' AND ', $jWhere) . " GROUP BY jornada ORDER BY jornada",
                        $jParams
                    );

                    $data[$categoria] = [
                        'tabla' => $tabla,
                        'goleadores' => $goleadores,
                        'tarjetas' => $tarjetas,
                        'partidos' => $matchRows,
                        'jornadas' => $jornadas,
                    ];
                }

                $dateStr = formatSpanishDate(new \DateTime());

                $html = buildHtmlFromTemplate($template, $data, $dateStr, $baseUrl, $torneo);
                $res->json(['html' => $html, 'torneo' => $torneo ?: null, 'generatedAt' => date('c')]);
            } catch (\Exception $e) {
                error_log('Error GET /setup/semanario-html: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al generar el semanario HTML']);
            }
        });
    });
}

function buildHtmlFromTemplate(string $template, array $data, string $dateStr, string $baseUrl, string $torneo): string {
    $html = $template;
                $seasonLabel = $season ? ($season['nombre'] ?? '') : '';
                $dateLabel = trim(($seasonLabel ? "$seasonLabel • " : '') . ($torneo ? "Torneo $torneo • " : '') . $dateStr, ' •');
    $html = preg_replace(
        '/(<header[\s\S]*?<img[^>]*src=")[^"]*("[^>]*class="[^"]*object-contain[^"]*"[^>]*>)/',
        '$1' . $baseUrl . '/assets/logo.png$2',
        $html,
        1
    ) ?? $html;
    $html = preg_replace('/<p class="text-on-surface-variant\/60 text-xs sm:text-sm mt-1">[\s\S]*?<\/p>/', "<p class=\"text-on-surface-variant/60 text-xs sm:text-sm mt-1\">$dateLabel</p>", $html);
    $html = preg_replace('/<p class="text-on-surface-variant\/40 text-xs">Generado por <span class="text-primary font-bold">Juego de Equipo<\/span> &bull; [\s\S]*?<\/p>/', "<p class=\"text-on-surface-variant/40 text-xs\">Generado por <span class=\"text-primary font-bold\">Juego de Equipo</span> &bull; $dateLabel</p>", $html);

    $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
    $jsonData = str_replace('</', '<\\/', $jsonData);

    $start = strpos($html, 'const DATA=');
    $end = strpos($html, "\nconst CATS=");
    if ($start === false || $end === false) {
        throw new \RuntimeException('No se pudo actualizar el bloque DATA en la plantilla');
    }
    $html = substr($html, 0, $start) . "const DATA=$jsonData;" . substr($html, $end);
    return $html;
}

function formatSpanishDate(\DateTimeInterface $date): string {
    if (class_exists('IntlDateFormatter')) {
        $formatter = new \IntlDateFormatter('es_AR', \IntlDateFormatter::FULL, \IntlDateFormatter::NONE);
        $formatted = $formatter->format($date);
        if (is_string($formatted) && $formatted !== '') {
            return $formatted;
        }
    }

    $days = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
    $months = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

    return sprintf(
        '%s, %d de %s de %d',
        $days[(int)$date->format('w')],
        (int)$date->format('j'),
        $months[(int)$date->format('n')],
        (int)$date->format('Y')
    );
}
