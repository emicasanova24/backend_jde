<?php
namespace App;

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Season.php';

function seedDatabase(): void {
    $db = Database::getConnection();

    try {
        ensureDefaultSeasons();
        $seasonId = resolveSeasonId(null);

        Database::execute("
            CREATE TABLE IF NOT EXISTS noticias (
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
                orden_home INT NOT NULL DEFAULT 999,
                temporada_id INT NOT NULL DEFAULT $seasonId,
                published_at DATETIME NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at DATETIME NULL,
                UNIQUE KEY noticias_slug_unique (slug),
                KEY noticias_estado_idx (estado),
                KEY noticias_temporada_idx (temporada_id),
                KEY noticias_destacada_idx (destacada)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        Database::execute("
            CREATE TABLE IF NOT EXISTS noticias_relacionadas (
                noticia_id INT NOT NULL,
                noticia_relacionada_id INT NOT NULL,
                PRIMARY KEY (noticia_id, noticia_relacionada_id),
                CONSTRAINT fk_noticias_relacionadas_principal FOREIGN KEY (noticia_id) REFERENCES noticias(id) ON DELETE CASCADE,
                CONSTRAINT fk_noticias_relacionadas_secundaria FOREIGN KEY (noticia_relacionada_id) REFERENCES noticias(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        Database::execute("
            CREATE TABLE IF NOT EXISTS equipos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nombre VARCHAR(150) NOT NULL,
                estadio VARCHAR(255) NOT NULL DEFAULT '',
                escudo TEXT NULL,
                categoria VARCHAR(40) NOT NULL DEFAULT 'Primera',
                temporada_id INT NOT NULL DEFAULT $seasonId
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        Database::execute("
            CREATE TABLE IF NOT EXISTS jugadores (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nombre VARCHAR(191) NOT NULL,
                posicion VARCHAR(100) NOT NULL,
                equipo INT NULL,
                temporada_id INT NOT NULL DEFAULT $seasonId,
                estado VARCHAR(20) NOT NULL DEFAULT 'activo',
                goles INT NOT NULL DEFAULT 0,
                amarillas INT NOT NULL DEFAULT 0,
                rojas INT NOT NULL DEFAULT 0,
                CONSTRAINT fk_jugadores_equipo FOREIGN KEY (equipo) REFERENCES equipos(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        Database::execute("
            CREATE TABLE IF NOT EXISTS partidos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                fecha DATE NOT NULL,
                hora TIME NOT NULL,
                estadio VARCHAR(255) NOT NULL DEFAULT '',
                local INT NULL,
                visitante INT NULL,
                estado VARCHAR(50) NOT NULL DEFAULT 'pendiente',
                goleslocal INT NOT NULL DEFAULT 0,
                golesvisitante INT NOT NULL DEFAULT 0,
                amarillas INT NOT NULL DEFAULT 0,
                rojas INT NOT NULL DEFAULT 0,
                torneo VARCHAR(40) NOT NULL DEFAULT 'Apertura',
                categoria VARCHAR(40) NOT NULL DEFAULT 'Primera',
                temporada_id INT NOT NULL DEFAULT $seasonId,
                jornada INT NULL,
                broadcast VARCHAR(512) NULL,
                CONSTRAINT fk_partidos_local FOREIGN KEY (local) REFERENCES equipos(id) ON DELETE CASCADE,
                CONSTRAINT fk_partidos_visitante FOREIGN KEY (visitante) REFERENCES equipos(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        Database::execute("
            CREATE TABLE IF NOT EXISTS eventos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                partido_id INT NULL,
                jugador_id INT NULL,
                equipo_id INT NULL,
                temporada_id INT NOT NULL DEFAULT $seasonId,
                tipo VARCHAR(20) NOT NULL,
                minuto INT NULL,
                CONSTRAINT fk_eventos_partido FOREIGN KEY (partido_id) REFERENCES partidos(id) ON DELETE CASCADE,
                CONSTRAINT fk_eventos_jugador FOREIGN KEY (jugador_id) REFERENCES jugadores(id) ON DELETE SET NULL,
                CONSTRAINT fk_eventos_equipo FOREIGN KEY (equipo_id) REFERENCES equipos(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        ensureColumn('equipos', 'categoria', "VARCHAR(40) NOT NULL DEFAULT 'Primera'");
        ensureColumn('equipos', 'estadio', "VARCHAR(255) NOT NULL DEFAULT ''");
        ensureColumn('equipos', 'escudo', 'TEXT NULL');
        ensureColumn('equipos', 'temporada_id', 'INT NOT NULL DEFAULT ' . $seasonId);

        ensureColumn('jugadores', 'equipo', 'INT NULL');
        ensureColumn('jugadores', 'temporada_id', 'INT NOT NULL DEFAULT ' . $seasonId);
        ensureColumn('jugadores', 'estado', "VARCHAR(20) NOT NULL DEFAULT 'activo'");
        ensureColumn('jugadores', 'goles', 'INT NOT NULL DEFAULT 0');
        ensureColumn('jugadores', 'amarillas', 'INT NOT NULL DEFAULT 0');
        ensureColumn('jugadores', 'rojas', 'INT NOT NULL DEFAULT 0');

        ensureColumn('partidos', 'torneo', "VARCHAR(40) NOT NULL DEFAULT 'Apertura'");
        ensureColumn('partidos', 'categoria', "VARCHAR(40) NOT NULL DEFAULT 'Primera'");
        ensureColumn('partidos', 'temporada_id', 'INT NOT NULL DEFAULT ' . $seasonId);
        ensureColumn('partidos', 'jornada', 'INT NULL');
        ensureColumn('partidos', 'broadcast', 'VARCHAR(512) NULL');
        ensureColumn('eventos', 'temporada_id', 'INT NOT NULL DEFAULT ' . $seasonId);

        Database::execute('UPDATE equipos SET temporada_id = ? WHERE temporada_id IS NULL OR temporada_id = 0', [$seasonId]);
        Database::execute('UPDATE jugadores SET temporada_id = ? WHERE temporada_id IS NULL OR temporada_id = 0', [$seasonId]);
        Database::execute('UPDATE partidos SET temporada_id = ? WHERE temporada_id IS NULL OR temporada_id = 0', [$seasonId]);
        Database::execute('UPDATE eventos SET temporada_id = ? WHERE temporada_id IS NULL OR temporada_id = 0', [$seasonId]);

        Database::dropIndexIfExists('equipos', 'equipos_nombre_unique');
        Database::dropIndexIfExists('equipos', 'equipos_nombre_categoria_unique');
        Database::ensureUniqueIndex('equipos', 'equipos_nombre_categoria_unique', 'nombre, categoria, temporada_id');
        Database::dropIndexIfExists('jugadores', 'jugadores_nombre_equipo_unique');
        Database::ensureUniqueIndex('jugadores', 'jugadores_nombre_equipo_unique', 'nombre, equipo, temporada_id');
        Database::dropIndexIfExists('partidos', 'partidos_unico');
        Database::ensureUniqueIndex('partidos', 'partidos_unico', 'fecha, hora, local, visitante, torneo, temporada_id');

        $db->beginTransaction();

        $equipos = getStoreEquipos();
        foreach ($equipos as $e) {
            Database::execute(
                "INSERT INTO equipos (nombre, estadio, escudo, categoria, temporada_id) VALUES (?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE estadio = VALUES(estadio), escudo = VALUES(escudo)",
                [$e['nombre'], $e['estadio'], $e['escudo'], $e['categoria'] ?? 'Primera', $seasonId]
            );
        }

        $jugadores = getStoreJugadores();
        foreach ($jugadores as $j) {
            Database::execute(
                "INSERT INTO jugadores (nombre, posicion, equipo, temporada_id, estado, goles, amarillas, rojas) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE posicion = VALUES(posicion), estado = VALUES(estado), goles = VALUES(goles), amarillas = VALUES(amarillas), rojas = VALUES(rojas)",
                [$j['nombre'], $j['posicion'], $j['equipo'], $seasonId, $j['estado'] ?? 'activo', $j['goles'], $j['amarillas'], $j['rojas']]
            );
        }

        $partidos = getStorePartidos();
        foreach ($partidos as $p) {
            Database::execute(
                "INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, temporada_id)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE estadio = VALUES(estadio), estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), amarillas = VALUES(amarillas), rojas = VALUES(rojas), categoria = VALUES(categoria)",
                [$p['fecha'], $p['hora'], $p['estadio'], $p['localId'], $p['visitanteId'], $p['estado'], $p['golesLocal'], $p['golesVisitante'], $p['amarillas'], $p['rojas'], $p['torneo'] ?? 'Apertura', $p['categoria'] ?? 'Primera', $seasonId]
            );
        }

        seedNoticias($seasonId);

        $db->commit();
    } catch (\Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }
}

function ensureColumn(string $table, string $column, string $definition): void {
    if (Database::columnExists($table, $column)) {
        return;
    }

    Database::execute(sprintf('ALTER TABLE `%s` ADD COLUMN `%s` %s', $table, $column, $definition));
}

function getStoreEquipos(): array {
    return json_decode(file_get_contents(__DIR__ . '/data/equipos.json'), true);
}

function getStoreJugadores(): array {
    return json_decode(file_get_contents(__DIR__ . '/data/jugadores.json'), true);
}

function getStorePartidos(): array {
    return json_decode(file_get_contents(__DIR__ . '/data/partidos.json'), true);
}

function seedNoticias(int $seasonId): void {
    $news = [
        [
            'slug' => 'apertura-2025-26-comenzo-con-triunfo-de-cecil-roberts',
            'titulo' => 'Apertura 2025/26: Cecil A. Roberts abrió la campaña con una victoria clave',
            'copete' => 'El arranque del torneo dejó un primer golpe de autoridad y marcó el tono competitivo de la temporada activa.',
            'contenido' => '<p>Cecil A. Roberts abrió la temporada con una actuación firme y un triunfo que ya lo posiciona entre los equipos a seguir. El cuerpo técnico destacó la intensidad, mientras que la liga comienza a perfilar a sus protagonistas.</p>',
            'imagenUrl' => '/assets/logo.png',
            'imagenAlt' => 'Inicio de la temporada Apertura 2025/26',
            'tag' => 'Apertura',
            'categoria' => 'Crónica',
            'autorNombre' => 'Redacción Juego de Equipo',
            'estado' => 'publicada',
            'destacada' => 1,
            'ordenHome' => 1,
            'publishedAt' => '2026-04-02 09:00:00',
        ],
        [
            'slug' => 'mercado-de-planteles-la-liga-empieza-a-mirar-el-recambio',
            'titulo' => 'Mercado de planteles: la liga empieza a mirar el recambio y el valor del histórico',
            'copete' => 'El nuevo ciclo obliga a distinguir entre protagonistas actuales y jugadores que ya forman parte del patrimonio estadístico del torneo.',
            'contenido' => '<p>La profesionalización del registro deportivo permite seguir a los futbolistas activos, detectar retiros y conservar trayectorias sin perder la historia de cada campaña. Esa capa histórica será clave para el nuevo panel.</p>',
            'imagenUrl' => '/assets/logo.png',
            'imagenAlt' => 'Jugadores y recambio generacional en la liga',
            'tag' => 'Planteles',
            'categoria' => 'Análisis',
            'autorNombre' => 'Equipo editorial JDE',
            'estado' => 'publicada',
            'destacada' => 1,
            'ordenHome' => 2,
            'publishedAt' => '2026-04-03 10:30:00',
        ],
        [
            'slug' => 'semanario-digital-una-nueva-portada-para-la-liga',
            'titulo' => 'Semanario digital: una portada editorial para contar la liga con otra jerarquía',
            'copete' => 'El sitio incorpora una narrativa más periodística para combinar resultados, contexto y contenido destacado en una misma experiencia.',
            'contenido' => '<p>La próxima etapa del producto va a integrar noticias destacadas, notas relacionadas y temporadas históricas en la home para que la cobertura tenga una lectura más profesional y menos operativa.</p>',
            'imagenUrl' => '/assets/logo.png',
            'imagenAlt' => 'Nueva portada editorial de Juego de Equipo',
            'tag' => 'Editorial',
            'categoria' => 'Institucional',
            'autorNombre' => 'Dirección Juego de Equipo',
            'estado' => 'publicada',
            'destacada' => 1,
            'ordenHome' => 3,
            'publishedAt' => '2026-04-04 08:15:00',
        ],
    ];

    foreach ($news as $item) {
        Database::execute(
            'INSERT INTO noticias (slug, titulo, copete, contenido, imagen_url, imagen_alt, tag, categoria, autor_nombre, estado, destacada, orden_home, temporada_id, published_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE titulo = VALUES(titulo), copete = VALUES(copete), contenido = VALUES(contenido), imagen_url = VALUES(imagen_url), imagen_alt = VALUES(imagen_alt), tag = VALUES(tag), categoria = VALUES(categoria), autor_nombre = VALUES(autor_nombre), estado = VALUES(estado), destacada = VALUES(destacada), orden_home = VALUES(orden_home), temporada_id = VALUES(temporada_id), published_at = VALUES(published_at)',
            [
                $item['slug'],
                $item['titulo'],
                $item['copete'],
                $item['contenido'],
                $item['imagenUrl'],
                $item['imagenAlt'],
                $item['tag'],
                $item['categoria'],
                $item['autorNombre'],
                $item['estado'],
                $item['destacada'],
                $item['ordenHome'],
                $seasonId,
                $item['publishedAt'],
            ]
        );
    }

    $rows = Database::fetchAll('SELECT id, slug FROM noticias ORDER BY orden_home ASC, id ASC');
    $bySlug = [];
    foreach ($rows as $row) {
        $bySlug[$row['slug']] = (int) $row['id'];
    }

    $relations = [
        'apertura-2025-26-comenzo-con-triunfo-de-cecil-roberts' => ['mercado-de-planteles-la-liga-empieza-a-mirar-el-recambio'],
        'mercado-de-planteles-la-liga-empieza-a-mirar-el-recambio' => ['apertura-2025-26-comenzo-con-triunfo-de-cecil-roberts', 'semanario-digital-una-nueva-portada-para-la-liga'],
        'semanario-digital-una-nueva-portada-para-la-liga' => ['mercado-de-planteles-la-liga-empieza-a-mirar-el-recambio'],
    ];

    Database::execute('DELETE FROM noticias_relacionadas');
    foreach ($relations as $slug => $relatedSlugs) {
        $newsId = $bySlug[$slug] ?? null;
        if (!$newsId) {
            continue;
        }

        foreach ($relatedSlugs as $relatedSlug) {
            $relatedId = $bySlug[$relatedSlug] ?? null;
            if (!$relatedId || $relatedId === $newsId) {
                continue;
            }

            Database::execute(
                'INSERT INTO noticias_relacionadas (noticia_id, noticia_relacionada_id) VALUES (?, ?)',
                [$newsId, $relatedId]
            );
        }
    }
}
