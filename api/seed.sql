SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS temporadas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(20) NOT NULL,
    slug VARCHAR(20) NOT NULL,
    anio_inicio SMALLINT NOT NULL,
    anio_fin SMALLINT NOT NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'borrador',
    is_active TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY temporadas_nombre_unique (nombre),
    UNIQUE KEY temporadas_slug_unique (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO temporadas (id, nombre, slug, anio_inicio, anio_fin, estado, is_active) VALUES
    (1, '2024/25', '2024-25', 2024, 2025, 'archivada', 0),
    (2, '2025/26', '2025-26', 2025, 2026, 'activa', 1)
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    slug = VALUES(slug),
    anio_inicio = VALUES(anio_inicio),
    anio_fin = VALUES(anio_fin),
    estado = VALUES(estado),
    is_active = VALUES(is_active);

CREATE TABLE IF NOT EXISTS equipos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    estadio VARCHAR(255) NOT NULL DEFAULT '',
    escudo TEXT NULL,
    categoria VARCHAR(40) NOT NULL DEFAULT 'Primera',
    temporada_id INT NOT NULL DEFAULT 2,
    UNIQUE KEY equipos_nombre_categoria_unique (nombre, categoria, temporada_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS jugadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(191) NOT NULL,
    posicion VARCHAR(100) NOT NULL,
    equipo INT NULL,
    temporada_id INT NOT NULL DEFAULT 2,
    estado VARCHAR(20) NOT NULL DEFAULT 'activo',
    goles INT NOT NULL DEFAULT 0,
    amarillas INT NOT NULL DEFAULT 0,
    rojas INT NOT NULL DEFAULT 0,
    UNIQUE KEY jugadores_nombre_equipo_unique (nombre, equipo, temporada_id),
    CONSTRAINT fk_jugadores_equipo FOREIGN KEY (equipo) REFERENCES equipos(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
    temporada_id INT NOT NULL DEFAULT 2,
    jornada INT NULL,
    broadcast VARCHAR(512) NULL,
    UNIQUE KEY partidos_unico (fecha, hora, local, visitante, torneo, temporada_id),
    CONSTRAINT fk_partidos_local FOREIGN KEY (local) REFERENCES equipos(id) ON DELETE CASCADE,
    CONSTRAINT fk_partidos_visitante FOREIGN KEY (visitante) REFERENCES equipos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    partido_id INT NULL,
    jugador_id INT NULL,
    equipo_id INT NULL,
    temporada_id INT NOT NULL DEFAULT 2,
    tipo VARCHAR(20) NOT NULL,
    minuto INT NULL,
    CONSTRAINT fk_eventos_partido FOREIGN KEY (partido_id) REFERENCES partidos(id) ON DELETE CASCADE,
    CONSTRAINT fk_eventos_jugador FOREIGN KEY (jugador_id) REFERENCES jugadores(id) ON DELETE SET NULL,
    CONSTRAINT fk_eventos_equipo FOREIGN KEY (equipo_id) REFERENCES equipos(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
    temporada_id INT NOT NULL DEFAULT 2,
    published_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    UNIQUE KEY noticias_slug_unique (slug),
    KEY noticias_estado_idx (estado),
    KEY noticias_temporada_idx (temporada_id),
    KEY noticias_destacada_idx (destacada)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS noticias_relacionadas (
    noticia_id INT NOT NULL,
    noticia_relacionada_id INT NOT NULL,
    PRIMARY KEY (noticia_id, noticia_relacionada_id),
    CONSTRAINT fk_noticias_relacionadas_principal FOREIGN KEY (noticia_id) REFERENCES noticias(id) ON DELETE CASCADE,
    CONSTRAINT fk_noticias_relacionadas_secundaria FOREIGN KEY (noticia_relacionada_id) REFERENCES noticias(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS home_config (
    clave VARCHAR(120) NOT NULL PRIMARY KEY,
    valor_string VARCHAR(512) NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'editor',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

START TRANSACTION;

INSERT INTO equipos (nombre, estadio, escudo, categoria, temporada_id) VALUES
    ('Cecil A. Roberts', 'Estadio 1', '/assets-equipos/logos/roberts.png', 'Primera', 2),
    ('Unión Deportiva', 'Estadio 2', '/assets-equipos/logos/uniontl.png', 'Primera', 2),
    ('Unión de Bonifacio', 'Estadio 3', '/assets-equipos/logos/bonifacio.png', 'Primera', 2),
    ('Deportivo Garré', 'Estadio 4', '/assets-equipos/logos/garre.png', 'Primera', 2),
    ('Deportivo Maza', 'Estadio 5', '/assets-equipos/logos/maza.png', 'Primera', 2),
    ('Deportivo Argentino', 'Estadio 6', '/assets-equipos/logos/deportivoarg.png', 'Primera', 2),
    ('Atlético Quenumá', 'Estadio 7', '/assets-equipos/logos/quenuma.png', 'Primera', 2),
    ('Atlético Argentino', 'Estadio 8', '/assets-equipos/logos/argentinos.png', 'Primera', 2),
    ('Salazar FC', 'Estadio 9', '/assets-equipos/logos/salazar.png', 'Primera', 2),
    ('Jorge Newbery', 'Estadio 10', '/assets-equipos/logos/newbery.png', 'Primera', 2),
    ('El Ceibo', 'Estadio 11', '/assets-equipos/logos/ceibo.png', 'Primera', 2),
    ('La Gloria', 'Estadio 12', '/assets-equipos/logos/lagloria.png', 'Primera', 2),
    ('Deportivo 17', 'Estadio 13', '/assets-equipos/logos/17.png', 'Primera', 2),
    ('Juventud Unida', 'Estadio 14', '/assets-equipos/logos/juventud.png', 'Primera', 2),
    ('Cecil A. Roberts', 'Estadio 1', '/assets-equipos/logos/roberts.png', 'Tercera', 2),
    ('Unión Deportiva', 'Estadio 2', '/assets-equipos/logos/uniontl.png', 'Tercera', 2),
    ('Unión de Bonifacio', 'Estadio 3', '/assets-equipos/logos/bonifacio.png', 'Tercera', 2),
    ('Deportivo Garré', 'Estadio 4', '/assets-equipos/logos/garre.png', 'Tercera', 2),
    ('Deportivo Maza', 'Estadio 5', '/assets-equipos/logos/maza.png', 'Tercera', 2),
    ('Deportivo Argentino', 'Estadio 6', '/assets-equipos/logos/deportivoarg.png', 'Tercera', 2),
    ('Atlético Quenumá', 'Estadio 7', '/assets-equipos/logos/quenuma.png', 'Tercera', 2),
    ('Atlético Argentino', 'Estadio 8', '/assets-equipos/logos/argentinos.png', 'Tercera', 2),
    ('Salazar FC', 'Estadio 9', '/assets-equipos/logos/salazar.png', 'Tercera', 2),
    ('Jorge Newbery', 'Estadio 10', '/assets-equipos/logos/newbery.png', 'Tercera', 2),
    ('El Ceibo', 'Estadio 11', '/assets-equipos/logos/ceibo.png', 'Tercera', 2),
    ('La Gloria', 'Estadio 12', '/assets-equipos/logos/lagloria.png', 'Tercera', 2),
    ('Deportivo 17', 'Estadio 13', '/assets-equipos/logos/17.png', 'Tercera', 2),
    ('Juventud Unida', 'Estadio 14', '/assets-equipos/logos/juventud.png', 'Tercera', 2),
    ('Cecil A. Roberts', 'Estadio 1', '/assets-equipos/logos/roberts.png', 'Senior', 2),
    ('Unión Deportiva', 'Estadio 2', '/assets-equipos/logos/uniontl.png', 'Senior', 2),
    ('Unión de Bonifacio', 'Estadio 3', '/assets-equipos/logos/bonifacio.png', 'Senior', 2),
    ('Deportivo Garré', 'Estadio 4', '/assets-equipos/logos/garre.png', 'Senior', 2),
    ('Deportivo Maza', 'Estadio 5', '/assets-equipos/logos/maza.png', 'Senior', 2),
    ('Deportivo Argentino', 'Estadio 6', '/assets-equipos/logos/deportivoarg.png', 'Senior', 2),
    ('Ferro', 'Estadio 15', '/assets-equipos/logos/ferro.png', 'Senior', 2),
    ('Atlético Argentino', 'Estadio 8', '/assets-equipos/logos/argentinos.png', 'Senior', 2),
    ('Salazar FC', 'Estadio 9', '/assets-equipos/logos/salazar.png', 'Senior', 2),
    ('Jorge Newbery', 'Estadio 10', '/assets-equipos/logos/newbery.png', 'Senior', 2),
    ('El Ceibo', 'Estadio 11', '/assets-equipos/logos/ceibo.png', 'Senior', 2),
    ('La Gloria', 'Estadio 12', '/assets-equipos/logos/lagloria.png', 'Senior', 2),
    ('Deportivo 17', 'Estadio 13', '/assets-equipos/logos/17.png', 'Senior', 2),
    ('Juventud Unida', 'Estadio 14', '/assets-equipos/logos/juventud.png', 'Senior', 2)
ON DUPLICATE KEY UPDATE
    estadio = VALUES(estadio),
    escudo = VALUES(escudo);

INSERT INTO jugadores (nombre, posicion, equipo, temporada_id, estado, goles, amarillas, rojas) VALUES
    ('Juan Pérez', 'Delantero', 1, 2, 'activo', 5, 1, 0),
    ('Luis Gómez', 'Volante', 2, 2, 'activo', 2, 2, 0),
    ('Pablo Campos', 'Defensor', 3, 2, 'retirado', 1, 3, 1)
ON DUPLICATE KEY UPDATE
    posicion = VALUES(posicion),
    estado = VALUES(estado),
    goles = VALUES(goles),
    amarillas = VALUES(amarillas),
    rojas = VALUES(rojas);

INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, temporada_id) VALUES
    ('2026-04-01', '18:30', 'Estadio Norte', 1, 2, 'jugado', 2, 1, 3, 0, 'Apertura', 'Primera', 2),
    ('2026-04-05', '20:00', 'Estadio Centro', 2, 3, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Primera', 2)
ON DUPLICATE KEY UPDATE
    estadio = VALUES(estadio),
    estado = VALUES(estado),
    goleslocal = VALUES(goleslocal),
    golesvisitante = VALUES(golesvisitante),
    amarillas = VALUES(amarillas),
    rojas = VALUES(rojas),
    categoria = VALUES(categoria);

INSERT INTO noticias (slug, titulo, copete, contenido, imagen_url, imagen_alt, tag, categoria, autor_nombre, estado, destacada, orden_home, temporada_id, published_at) VALUES
    ('apertura-2025-26-comenzo-con-triunfo-de-cecil-roberts', 'Apertura 2025/26: Cecil A. Roberts abrió la campaña con una victoria clave', 'El arranque del torneo dejó un primer golpe de autoridad y marcó el tono competitivo de la temporada activa.', '<p>Cecil A. Roberts abrió la temporada con una actuación firme y un triunfo que ya lo posiciona entre los equipos a seguir.</p>', '/assets/logo.png', 'Inicio de la temporada Apertura 2025/26', 'Apertura', 'Crónica', 'Redacción Juego de Equipo', 'publicada', 1, 1, 2, '2026-04-02 09:00:00'),
    ('mercado-de-planteles-la-liga-empieza-a-mirar-el-recambio', 'Mercado de planteles: la liga empieza a mirar el recambio y el valor del histórico', 'El nuevo ciclo obliga a distinguir entre protagonistas actuales y jugadores que ya forman parte del patrimonio estadístico del torneo.', '<p>La profesionalización del registro deportivo permite seguir a los futbolistas activos, detectar retiros y conservar trayectorias sin perder la historia de cada campaña.</p>', '/assets/logo.png', 'Jugadores y recambio generacional en la liga', 'Planteles', 'Análisis', 'Equipo editorial JDE', 'publicada', 1, 2, 2, '2026-04-03 10:30:00'),
    ('semanario-digital-una-nueva-portada-para-la-liga', 'Semanario digital: una nueva portada para contar la liga con otra jerarquía', 'El sitio incorpora una narrativa más periodística para combinar resultados, contexto y contenido destacado en una misma experiencia.', '<p>La próxima etapa del producto va a integrar noticias destacadas, notas relacionadas y temporadas históricas en la home.</p>', '/assets/logo.png', 'Nueva portada editorial de Juego de Equipo', 'Editorial', 'Institucional', 'Dirección Juego de Equipo', 'publicada', 1, 3, 2, '2026-04-04 08:15:00')
ON DUPLICATE KEY UPDATE
    titulo = VALUES(titulo),
    copete = VALUES(copete),
    contenido = VALUES(contenido),
    imagen_url = VALUES(imagen_url),
    imagen_alt = VALUES(imagen_alt),
    tag = VALUES(tag),
    categoria = VALUES(categoria),
    autor_nombre = VALUES(autor_nombre),
    estado = VALUES(estado),
    destacada = VALUES(destacada),
    orden_home = VALUES(orden_home),
    temporada_id = VALUES(temporada_id),
    published_at = VALUES(published_at);

DELETE FROM noticias_relacionadas;

INSERT INTO noticias_relacionadas (noticia_id, noticia_relacionada_id)
SELECT a.id, b.id FROM noticias a CROSS JOIN noticias b
WHERE a.slug = 'apertura-2025-26-comenzo-con-triunfo-de-cecil-roberts'
  AND b.slug = 'mercado-de-planteles-la-liga-empieza-a-mirar-el-recambio';

INSERT INTO noticias_relacionadas (noticia_id, noticia_relacionada_id)
SELECT a.id, b.id FROM noticias a CROSS JOIN noticias b
WHERE a.slug = 'mercado-de-planteles-la-liga-empieza-a-mirar-el-recambio'
  AND b.slug IN ('apertura-2025-26-comenzo-con-triunfo-de-cecil-roberts', 'semanario-digital-una-nueva-portada-para-la-liga');

INSERT INTO noticias_relacionadas (noticia_id, noticia_relacionada_id)
SELECT a.id, b.id FROM noticias a CROSS JOIN noticias b
WHERE a.slug = 'semanario-digital-una-nueva-portada-para-la-liga'
  AND b.slug = 'mercado-de-planteles-la-liga-empieza-a-mirar-el-recambio';

INSERT INTO admin_account (id, full_name, email, password, phone, bio, role_label, admin_code, avatar_url, email_notifications, live_notifications, developer_mode, member_since, verified)
VALUES (
        1,
        'Administrador Juego de Equipo',
        'admin@juegodeequipo.com',
        '$2y$12$4TU0E4nwmP4wI0D2MZL3NubK9dTj8fZhyM0J4E74f6kA4L3o.rB4y',
        '',
        'Gestión integral de la plataforma administrativa y supervisión de ligas regionales.',
        'Administrador Principal',
        'ADMIN-001',
        '/assets/logo.png',
        1,
        1,
        0,
        '2023',
        1
)
ON DUPLICATE KEY UPDATE
        full_name = VALUES(full_name),
        email = VALUES(email),
        role_label = VALUES(role_label),
        admin_code = VALUES(admin_code),
        avatar_url = VALUES(avatar_url),
        email_notifications = VALUES(email_notifications),
        live_notifications = VALUES(live_notifications),
        developer_mode = VALUES(developer_mode),
        member_since = VALUES(member_since),
        verified = VALUES(verified);

COMMIT;