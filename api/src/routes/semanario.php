<?php
namespace App\Routes;

use App\Router;
use App\Database;
use App\Request;
use App\Response;

use function App\Middleware\authMiddleware;

require_once __DIR__ . '/../middleware/auth.php';

const CATEGORIAS = ['Primera', 'Tercera', 'Senior'];

function normalizeSemanarioTeamName(string $name): string {
    $normalized = trim(mb_strtolower($name, 'UTF-8'));
    if ($normalized === '') {
        return '';
    }

    $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $normalized);
    if ($ascii !== false) {
        $normalized = $ascii;
    }

    $normalized = preg_replace('/[^a-z0-9]+/', ' ', $normalized) ?? $normalized;
    return trim($normalized);
}

function parseSemanarioSvgLogos(): array {
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    $path = realpath(__DIR__ . '/../../../scripts/logos-svg-para-semanario.xml');
    if (!$path || !is_file($path)) {
        $cache = [];
        return $cache;
    }

    $content = file_get_contents($path);
    if (!is_string($content) || $content === '') {
        $cache = [];
        return $cache;
    }

    preg_match_all('/^\s*\d+\.\s*(.*?)\s*=\s*(<svg[\s\S]*?<\/svg>)\s*$/mu', $content, $matches, PREG_SET_ORDER);
    $logos = [];
    foreach ($matches as $match) {
        $teamName = trim((string) ($match[1] ?? ''));
        $svg = trim((string) ($match[2] ?? ''));
        if ($teamName === '' || $svg === '') {
            continue;
        }
        $logos[normalizeSemanarioTeamName($teamName)] = 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
    }

    $cache = $logos;
    return $cache;
}

function resolveSemanarioEscudoPath(?string $escudo, string $baseUrl): string {
    static $svgOverrides = [];

    $path = trim((string) $escudo);
    if ($path === '') {
        return '';
    }

    if (str_starts_with($path, '/logos/')) {
        $basename = basename($path);
        if (!array_key_exists($basename, $svgOverrides)) {
            $svgName = preg_replace('/\.[^.]+$/', '.svg', $basename) ?? $basename;
            $svgPath = realpath(__DIR__ . '/../../../assets-equipos/logos/' . $svgName);
            $svgOverrides[$basename] = $svgPath && is_file($svgPath)
                ? '/assets-equipos/logos/' . $svgName
                : null;
        }

        $path = $svgOverrides[$basename] ?? ('/assets-equipos/logos/' . $basename);
    }
    if (str_starts_with($path, '/')) {
        return $baseUrl . $path;
    }

    return $path;
}

function buildSemanarioData(string $torneo, string $baseUrl, array $categorias = CATEGORIAS): array {
    $data = [];
    foreach ($categorias as $categoria) {
        $catParam = [$categoria];

        // Tabla
        $equipos = Database::fetchAll(
            "SELECT id, nombre, categoria, escudo FROM equipos WHERE LOWER(categoria) = LOWER(?)",
            $catParam
        );
        $tabla = [];
        $lookup = [];
        foreach ($equipos as $e) {
            $escudo = resolveSemanarioEscudoPath($e['escudo'] ?? null, $baseUrl);
            $entry = [
                'equipo' => $e['nombre'], 'id' => $e['id'], 'escudo' => $escudo,
                'pj' => 0, 'pg' => 0, 'pe' => 0, 'pp' => 0,
                'gf' => 0, 'gc' => 0, 'dg' => 0, 'puntos' => 0,
            ];
            $tabla[] = &$entry;
            $lookup[$e['id']] = count($tabla) - 1;
            unset($entry);
        }

        $pWhere = ["estado = 'jugado'", "LOWER(categoria) = LOWER(?)"];
        $pParams = [$categoria];
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
        $gWhere = ["ev.tipo = 'gol'", "LOWER(p.categoria) = LOWER(?)", "p.estado = 'jugado'"];
        $gParams = [$categoria];
        if ($torneo) { $gWhere[] = "LOWER(p.torneo) = LOWER(?)"; $gParams[] = $torneo; }
        $goleadores = Database::fetchAll(
            "SELECT j.nombre, COALESCE(e.nombre, '-') AS equipo, COUNT(*) AS goles
             FROM eventos ev JOIN partidos p ON ev.partido_id = p.id JOIN jugadores j ON ev.jugador_id = j.id LEFT JOIN equipos e ON ev.equipo_id = e.id
             WHERE " . implode(' AND ', $gWhere) . " GROUP BY j.id, j.nombre, e.nombre ORDER BY goles DESC",
            $gParams
        );

        // Tarjetas
        $buildTarjetas = function (string $tipo) use ($categoria, $torneo) {
            $w = ["ev.tipo = '$tipo'", "LOWER(p.categoria) = LOWER(?)", "p.estado = 'jugado'"];
            $p = [$categoria];
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
        $mWhere = ["LOWER(p.categoria) = LOWER(?)"];
        $mParams = [$categoria];
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
            $m['localEscudo'] = resolveSemanarioEscudoPath($m['localEscudo'] ?? null, $baseUrl);
            $m['visitanteEscudo'] = resolveSemanarioEscudoPath($m['visitanteEscudo'] ?? null, $baseUrl);
        }
        unset($m);

        // Jornadas
        $jWhere = ["LOWER(categoria) = LOWER(?)", "jornada IS NOT NULL"];
        $jParams = [$categoria];
        if ($torneo) { $jWhere[] = "LOWER(torneo) = LOWER(?)"; $jParams[] = $torneo; }
        $jornadas = Database::fetchAll(
            "SELECT jornada, COUNT(*) AS total, 
                    SUM(CASE WHEN estado = 'jugado' THEN 1 ELSE 0 END) AS jugados,
                    SUM(CASE WHEN estado = 'en_curso' THEN 1 ELSE 0 END) AS enCurso 
             FROM partidos
             WHERE " . implode(' AND ', $jWhere) . " GROUP BY jornada ORDER BY jornada",
            $jParams
        );

        $data[$categoria] = [
            'tabla' => $tabla,
            'torneo' => $torneo ?: null,
            'goleadores' => $goleadores,
            'tarjetas' => $tarjetas,
            'partidos' => $matchRows,
            'jornadas' => $jornadas,
        ];
    }
    return $data;
}

function formatSemanarioMatchdayDay(?string $date): ?string {
    $value = trim((string) $date);
    if ($value === '') {
        return null;
    }

    try {
        $matchDate = new \DateTimeImmutable($value);
    } catch (\Exception) {
        return null;
    }

    $months = [
        1 => 'ene',
        2 => 'feb',
        3 => 'mar',
        4 => 'abr',
        5 => 'may',
        6 => 'jun',
        7 => 'jul',
        8 => 'ago',
        9 => 'sep',
        10 => 'oct',
        11 => 'nov',
        12 => 'dic',
    ];

    return sprintf('%d %s', (int) $matchDate->format('j'), $months[(int) $matchDate->format('n')] ?? $matchDate->format('m'));
}

function normalizeSemanarioMatchState(?string $state): string {
    $normalized = trim(mb_strtolower((string) $state, 'UTF-8'));
    if ($normalized === '' || $normalized === 'pendiente') {
        return 'programado';
    }

    if ($normalized === 'jugado') {
        return 'jugado';
    }

    return $normalized;
}

function buildSemanarioLatestMatchdayPayload(string $torneo, string $categoria = 'Primera'): array {
    $where = ['LOWER(categoria) = LOWER(?)', 'jornada IS NOT NULL'];
    $params = [$categoria];
    if ($torneo !== '') {
        $where[] = 'LOWER(torneo) = LOWER(?)';
        $params[] = $torneo;
    }

    $latest = Database::fetchOne(
        'SELECT MAX(jornada) AS jornada FROM partidos WHERE ' . implode(' AND ', $where),
        $params
    );

    $latestJornada = isset($latest['jornada']) ? (int) $latest['jornada'] : 0;
    if ($latestJornada <= 0) {
        throw new \RuntimeException('No hay fechas cargadas para el semanario');
    }

    $matchParams = [$categoria, $latestJornada];
    $matchWhere = ['LOWER(p.categoria) = LOWER(?)', 'p.jornada = ?'];
    if ($torneo !== '') {
        $matchWhere[] = 'LOWER(p.torneo) = LOWER(?)';
        $matchParams[] = $torneo;
    }

    $matchRows = Database::fetchAll(
        "SELECT p.fecha, p.hora, p.estadio, p.estado, p.goleslocal AS golesLocal, p.golesvisitante AS golesVisitante,
                p.torneo, el.nombre AS local, ev.nombre AS visitante
         FROM partidos p
         LEFT JOIN equipos el ON p.local = el.id
         LEFT JOIN equipos ev ON p.visitante = ev.id
         WHERE " . implode(' AND ', $matchWhere) . ' ORDER BY p.fecha, p.hora, p.id',
        $matchParams
    );

    if (!$matchRows) {
        throw new \RuntimeException('No se encontraron partidos para la ultima fecha del semanario');
    }

    $now = new \DateTimeImmutable('now', new \DateTimeZone('America/Argentina/Buenos_Aires'));
    $competition = $torneo;
    if ($competition === '') {
        $competition = (string) ($matchRows[0]['torneo'] ?? '');
    }

    $matches = array_map(static function (array $row): array {
        $state = normalizeSemanarioMatchState($row['estado'] ?? null);
        $isPlayed = $state === 'jugado';
        $isEnCurso = $state === 'en_curso';
        $showScore = $isPlayed || $isEnCurso;
        $stadium = trim((string) ($row['estadio'] ?? ''));

        return [
            'hora' => trim((string) ($row['hora'] ?? '')),
            'dia' => formatSemanarioMatchdayDay($row['fecha'] ?? null),
            'local' => (string) ($row['local'] ?? ''),
            'visitante' => (string) ($row['visitante'] ?? ''),
            'estadio' => $stadium !== '' ? $stadium : null,
            'golesLocal' => $showScore ? (int) ($row['golesLocal'] ?? 0) : null,
            'golesVisitante' => $showScore ? (int) ($row['golesVisitante'] ?? 0) : null,
            'estado' => $state,
        ];
    }, $matchRows);

    return [
        'ultimaActualizacion' => $now->format('c'),
        'version' => $now->format('YmdHi'),
        'competencia' => $competition,
        'fecha' => 'Fecha ' . $latestJornada,
        'partidos' => $matches,
    ];
}

function registerSemanarioRoutes(Router $router): void {
    $router->get('/semanario-data', function (Request $req, Response $res) {
        try {
            $torneo = trim($req->query['torneo'] ?? '');
            $categoria = trim($req->query['categoria'] ?? '');
            $config = require __DIR__ . '/../config.php';
            $baseUrl = $config['api_url'] ?: ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
            $cats = $categoria ? [$categoria] : ['Primera'];
            $data = buildSemanarioData($torneo, $baseUrl, $cats);
            $res->json($data);
        } catch (\Exception $e) {
            error_log('Error GET /semanario-data: ' . $e->getMessage());
            $res->status(500)->json(['error' => 'Error al obtener datos del semanario']);
        }
    });

    $router->get('/semanario-ultima-fecha', function (Request $req, Response $res) {
        try {
            $torneo = trim($req->query['torneo'] ?? 'Apertura');
            $payload = buildSemanarioLatestMatchdayPayload($torneo, 'Primera');
            $res->json($payload);
        } catch (\RuntimeException $e) {
            $res->status(404)->json(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            error_log('Error GET /semanario-ultima-fecha: ' . $e->getMessage());
            $res->status(500)->json(['error' => 'Error al obtener la ultima fecha del semanario']);
        }
    });

    $router->group('/setup', function (Router $r) {

        $r->get('/semanario-html', function (Request $req, Response $res) {
            try {
                $torneo = trim($req->query['torneo'] ?? '');
                $config = require __DIR__ . '/../config.php';
                $baseUrl = $config['api_url'] ?: ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
                $svgLogos = parseSemanarioSvgLogos();

                // Forzar plantilla en la raíz del proyecto
                $templatePath = realpath(dirname(__DIR__, 3) . '/semanario.html');
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
                        "SELECT id, nombre, categoria, escudo FROM equipos WHERE LOWER(categoria) = LOWER(?)",
                        $catParam
                    );
                    $tabla = [];
                    $lookup = [];
                    foreach ($equipos as $e) {
                        $escudo = resolveSemanarioEscudoPath($e['escudo'] ?? null, $baseUrl);
                        $entry = [
                            'equipo' => $e['nombre'], 'id' => $e['id'], 'escudo' => $escudo,
                            'pj' => 0, 'pg' => 0, 'pe' => 0, 'pp' => 0,
                            'gf' => 0, 'gc' => 0, 'dg' => 0, 'puntos' => 0,
                        ];
                        $tabla[] = &$entry;
                        $lookup[$e['id']] = count($tabla) - 1;
                        unset($entry);
                    }

                    $pWhere = ["estado = 'jugado'", "LOWER(categoria) = LOWER(?)"];
                    $pParams = [$categoria];
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
                    $gWhere = ["ev.tipo = 'gol'", "LOWER(p.categoria) = LOWER(?)", "p.estado = 'jugado'"];
                    $gParams = [$categoria];
                    if ($torneo) { $gWhere[] = "LOWER(p.torneo) = LOWER(?)"; $gParams[] = $torneo; }
                    $goleadores = Database::fetchAll(
                        "SELECT j.nombre, COALESCE(e.nombre, '-') AS equipo, COUNT(*) AS goles
                         FROM eventos ev JOIN partidos p ON ev.partido_id = p.id JOIN jugadores j ON ev.jugador_id = j.id LEFT JOIN equipos e ON ev.equipo_id = e.id
                         WHERE " . implode(' AND ', $gWhere) . " GROUP BY j.id, j.nombre, e.nombre ORDER BY goles DESC",
                        $gParams
                    );

                    // Tarjetas
                    $buildTarjetas = function (string $tipo) use ($categoria, $torneo) {
                        $w = ["ev.tipo = '$tipo'", "LOWER(p.categoria) = LOWER(?)", "p.estado = 'jugado'"];
                        $p = [$categoria];
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
                    $mWhere = ["LOWER(p.categoria) = LOWER(?)"];
                    $mParams = [$categoria];
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
                        $m['localEscudo'] = resolveSemanarioEscudoPath($m['localEscudo'] ?? null, $baseUrl);
                        $m['visitanteEscudo'] = resolveSemanarioEscudoPath($m['visitanteEscudo'] ?? null, $baseUrl);
                    }
                    unset($m);

                    // Jornadas
                    $jWhere = ["LOWER(categoria) = LOWER(?)", "jornada IS NOT NULL"];
                    $jParams = [$categoria];
                    if ($torneo) { $jWhere[] = "LOWER(torneo) = LOWER(?)"; $jParams[] = $torneo; }
                    $jornadas = Database::fetchAll(
                        "SELECT jornada, COUNT(*) AS total, 
                                SUM(CASE WHEN estado = 'jugado' THEN 1 ELSE 0 END) AS jugados,
                                SUM(CASE WHEN estado = 'en_curso' THEN 1 ELSE 0 END) AS enCurso 
                         FROM partidos
                         WHERE " . implode(' AND ', $jWhere) . " GROUP BY jornada ORDER BY jornada",
                        $jParams
                    );

                    $data[$categoria] = [
                        'tabla' => $tabla,
                        'torneo' => $torneo ?: null,
                        'goleadores' => $goleadores,
                        'tarjetas' => $tarjetas,
                        'partidos' => $matchRows,
                        'jornadas' => $jornadas,
                    ];
                }

                $html = buildHtmlFromTemplate($template, $data, $baseUrl, $svgLogos);
                $res->json(['html' => $html, 'torneo' => $torneo ?: null, 'generatedAt' => date('c')]);
            } catch (\Exception $e) {
                error_log('Error GET /setup/semanario-html: ' . $e->getMessage());
                $res->status(500)->json(['error' => 'Error al generar el semanario HTML']);
            }
        });
    });
}

function buildHtmlFromTemplate(string $template, array $data, string $baseUrl, array $svgLogos = []): string {
    $html = $template;
    $html = preg_replace('/(<img[^>]*data-jde-logo="[^"]*"[^>]*src=")[^"]*(")/', '$1' . $baseUrl . '/assets/logo.png$2', $html) ?? $html;

    $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
    $jsonData = str_replace('</', '<\\/', $jsonData);

    $html = str_replace('let DATA=null;', 'let DATA=' . $jsonData . ';', $html);
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
