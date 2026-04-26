<?php
namespace App;

require_once __DIR__ . '/Database.php';

function seedDatabase(): void {
    $db = Database::getConnection();

    try {
        $db->beginTransaction();

        Database::execute("
            CREATE TABLE IF NOT EXISTS equipos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nombre VARCHAR(255) NOT NULL,
                estadio VARCHAR(255) NOT NULL DEFAULT '',
                escudo TEXT NULL,
                categoria VARCHAR(100) NOT NULL DEFAULT 'Primera'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        Database::execute("
            CREATE TABLE IF NOT EXISTS jugadores (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nombre VARCHAR(255) NOT NULL,
                posicion VARCHAR(100) NOT NULL,
                equipo INT NULL,
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
                torneo VARCHAR(100) NOT NULL DEFAULT 'Apertura',
                categoria VARCHAR(100) NOT NULL DEFAULT 'Primera',
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
                tipo VARCHAR(20) NOT NULL,
                minuto INT NULL,
                CONSTRAINT fk_eventos_partido FOREIGN KEY (partido_id) REFERENCES partidos(id) ON DELETE CASCADE,
                CONSTRAINT fk_eventos_jugador FOREIGN KEY (jugador_id) REFERENCES jugadores(id) ON DELETE SET NULL,
                CONSTRAINT fk_eventos_equipo FOREIGN KEY (equipo_id) REFERENCES equipos(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        Database::execute(" 
            CREATE TABLE IF NOT EXISTS admin_account (
                id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
                full_name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                phone VARCHAR(60) NOT NULL DEFAULT '',
                bio VARCHAR(1000) NOT NULL DEFAULT '',
                role_label VARCHAR(120) NOT NULL DEFAULT 'Administrador Principal',
                admin_code VARCHAR(32) NOT NULL DEFAULT 'ADMIN-001',
                avatar_url VARCHAR(255) NOT NULL DEFAULT '/assets/logo.png',
                email_notifications TINYINT(1) NOT NULL DEFAULT 1,
                live_notifications TINYINT(1) NOT NULL DEFAULT 1,
                developer_mode TINYINT(1) NOT NULL DEFAULT 0,
                member_since VARCHAR(16) NOT NULL DEFAULT '2023',
                verified TINYINT(1) NOT NULL DEFAULT 1,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        Database::execute(" 
            CREATE TABLE IF NOT EXISTS users (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                full_name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                role VARCHAR(50) NOT NULL DEFAULT 'editor',
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        ensureColumn('equipos', 'categoria', "VARCHAR(100) NOT NULL DEFAULT 'Primera'");
        ensureColumn('equipos', 'estadio', "VARCHAR(255) NOT NULL DEFAULT ''");
        ensureColumn('equipos', 'escudo', 'TEXT NULL');

        ensureColumn('jugadores', 'equipo', 'INT NULL');
        ensureColumn('jugadores', 'goles', 'INT NOT NULL DEFAULT 0');
        ensureColumn('jugadores', 'amarillas', 'INT NOT NULL DEFAULT 0');
        ensureColumn('jugadores', 'rojas', 'INT NOT NULL DEFAULT 0');

        ensureColumn('partidos', 'torneo', "VARCHAR(100) NOT NULL DEFAULT 'Apertura'");
        ensureColumn('partidos', 'categoria', "VARCHAR(100) NOT NULL DEFAULT 'Primera'");
        ensureColumn('partidos', 'jornada', 'INT NULL');
        ensureColumn('partidos', 'broadcast', 'VARCHAR(512) NULL');

        Database::dropIndexIfExists('equipos', 'equipos_nombre_unique');
        Database::ensureUniqueIndex('equipos', 'equipos_nombre_categoria_unique', 'nombre, categoria');
        Database::ensureUniqueIndex('jugadores', 'jugadores_nombre_equipo_unique', 'nombre, equipo');
        Database::ensureUniqueIndex('partidos', 'partidos_unico', 'fecha, hora, local, visitante, torneo');

        $equipos = getStoreEquipos();
        foreach ($equipos as $e) {
            Database::execute(
                "INSERT INTO equipos (nombre, estadio, escudo, categoria) VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE estadio = VALUES(estadio), escudo = VALUES(escudo)",
                [$e['nombre'], $e['estadio'], $e['escudo'], $e['categoria'] ?? 'Primera']
            );
        }

        $jugadores = getStoreJugadores();
        foreach ($jugadores as $j) {
            Database::execute(
                "INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas) VALUES (?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE posicion = VALUES(posicion), goles = VALUES(goles), amarillas = VALUES(amarillas), rojas = VALUES(rojas)",
                [$j['nombre'], $j['posicion'], $j['equipo'], $j['goles'], $j['amarillas'], $j['rojas']]
            );
        }

        $partidos = getStorePartidos();
        foreach ($partidos as $p) {
            Database::execute(
                "INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE estadio = VALUES(estadio), estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), amarillas = VALUES(amarillas), rojas = VALUES(rojas), categoria = VALUES(categoria)",
                [$p['fecha'], $p['hora'], $p['estadio'], $p['localId'], $p['visitanteId'], $p['estado'], $p['golesLocal'], $p['golesVisitante'], $p['amarillas'], $p['rojas'], $p['torneo'] ?? 'Apertura', $p['categoria'] ?? 'Primera']
            );
        }

        $config = require __DIR__ . '/config.php';
        if (!empty($config['admin_email']) && !empty($config['admin_password'])) {
            $hashedPassword = password_hash($config['admin_password'], PASSWORD_BCRYPT, ['cost' => 12]);
            Database::execute(
                "INSERT INTO admin_account (id, full_name, email, password, phone, bio, role_label, admin_code, avatar_url, email_notifications, live_notifications, developer_mode, member_since, verified)
                 VALUES (1, ?, ?, ?, '', 'Gestión integral de la plataforma administrativa y supervisión de ligas regionales.', 'Administrador Principal', 'ADMIN-001', '/assets/logo.png', 1, 1, 0, '2023', 1)
                 ON DUPLICATE KEY UPDATE
                    full_name = VALUES(full_name),
                    email = VALUES(email),
                    password = VALUES(password),
                    role_label = VALUES(role_label),
                    admin_code = VALUES(admin_code),
                    avatar_url = VALUES(avatar_url),
                    email_notifications = VALUES(email_notifications),
                    live_notifications = VALUES(live_notifications),
                    developer_mode = VALUES(developer_mode),
                    member_since = VALUES(member_since),
                    verified = VALUES(verified)",
                ['Administrador Juego de Equipo', $config['admin_email'], $hashedPassword]
            );
        }

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
