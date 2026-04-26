-- ============================================================
-- ALTER TABLE PARA PRODUCCIÓN — juegpdee_base
-- Compatible MySQL 5.6 / MariaDB
-- Ejecutar en phpMyAdmin: pegar en la pestaña SQL, dejar
-- el delimitador en // o cambiar el campo "Delimiter" a //
-- ============================================================

DELIMITER //

-- Helpers: agregar columna solo si no existe ---------------------
DROP PROCEDURE IF EXISTS _add_col //
CREATE PROCEDURE _add_col(IN tbl VARCHAR(100), IN col VARCHAR(100), IN def TEXT)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = tbl AND COLUMN_NAME = col
    ) THEN
        SET @_sql = CONCAT('ALTER TABLE `', tbl, '` ADD COLUMN `', col, '` ', def);
        PREPARE _stmt FROM @_sql;
        EXECUTE _stmt;
        DEALLOCATE PREPARE _stmt;
    END IF;
END //

-- Helper: eliminar índice solo si existe -------------------------
DROP PROCEDURE IF EXISTS _drop_idx //
CREATE PROCEDURE _drop_idx(IN tbl VARCHAR(100), IN idx VARCHAR(100))
BEGIN
    IF EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = tbl AND INDEX_NAME = idx
    ) THEN
        SET @_sql = CONCAT('ALTER TABLE `', tbl, '` DROP INDEX `', idx, '`');
        PREPARE _stmt FROM @_sql;
        EXECUTE _stmt;
        DEALLOCATE PREPARE _stmt;
    END IF;
END //

-- Helper: crear índice UNIQUE solo si no existe ------------------
DROP PROCEDURE IF EXISTS _add_unique //
CREATE PROCEDURE _add_unique(IN tbl VARCHAR(100), IN idx VARCHAR(100), IN cols TEXT)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = tbl AND INDEX_NAME = idx
    ) THEN
        SET @_sql = CONCAT('ALTER TABLE `', tbl, '` ADD UNIQUE KEY `', idx, '` (', cols, ')');
        PREPARE _stmt FROM @_sql;
        EXECUTE _stmt;
        DEALLOCATE PREPARE _stmt;
    END IF;
END //

DELIMITER ;

-- ============================================================
-- 1. TABLA temporadas (nueva)
-- ============================================================
CREATE TABLE IF NOT EXISTS temporadas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(20) NOT NULL,
    slug VARCHAR(20) NOT NULL,
    anio_inicio SMALLINT NOT NULL,
    anio_fin SMALLINT NOT NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'borrador',
    is_active TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY temporadas_nombre_unique (nombre),
    UNIQUE KEY temporadas_slug_unique (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Temporadas base (la 2 es la activa 2025/26)
INSERT INTO temporadas (id, nombre, slug, anio_inicio, anio_fin, estado, is_active) VALUES
    (1, '2024/25', '2024-25', 2024, 2025, 'archivada', 0),
    (2, '2025/26', '2025-26', 2025, 2026, 'activa',    1)
ON DUPLICATE KEY UPDATE
    nombre       = VALUES(nombre),
    slug         = VALUES(slug),
    anio_inicio  = VALUES(anio_inicio),
    anio_fin     = VALUES(anio_fin),
    estado       = VALUES(estado),
    is_active    = VALUES(is_active);

-- ============================================================
-- 2. equipos — temporada_id + reconstruir UNIQUE KEY
-- ============================================================
CALL _add_col('equipos', 'temporada_id', 'INT NOT NULL DEFAULT 2');

CALL _drop_idx('equipos', 'equipos_nombre_unique');
CALL _drop_idx('equipos', 'equipos_nombre_categoria_unique');
CALL _add_unique('equipos', 'equipos_nombre_categoria_unique', 'nombre, categoria, temporada_id');

-- ============================================================
-- 3. jugadores — temporada_id + estado + reconstruir UNIQUE KEY
-- ============================================================
CALL _add_col('jugadores', 'temporada_id', 'INT NOT NULL DEFAULT 2');
CALL _add_col('jugadores', 'estado',       "VARCHAR(20) NOT NULL DEFAULT 'activo'");

CALL _drop_idx('jugadores', 'jugadores_nombre_equipo_unique');
CALL _add_unique('jugadores', 'jugadores_nombre_equipo_unique', 'nombre, equipo, temporada_id');

-- ============================================================
-- 4. partidos — temporada_id + jornada + broadcast + reconstruir UNIQUE KEY
-- ============================================================
CALL _add_col('partidos', 'temporada_id', 'INT NOT NULL DEFAULT 2');
CALL _add_col('partidos', 'jornada',      'INT NULL');
CALL _add_col('partidos', 'broadcast',    'VARCHAR(512) NULL');

CALL _drop_idx('partidos', 'partidos_unico');
CALL _add_unique('partidos', 'partidos_unico', 'fecha, hora, local, visitante, torneo, temporada_id');

-- ============================================================
-- 5. eventos — temporada_id
-- ============================================================
CALL _add_col('eventos', 'temporada_id', 'INT NOT NULL DEFAULT 2');

-- ============================================================
-- 6. TABLA noticias (nueva)
-- ============================================================
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
    deleted_at DATETIME NULL,
    UNIQUE KEY noticias_slug_unique (slug),
    KEY noticias_estado_idx (estado),
    KEY noticias_temporada_idx (temporada_id),
    KEY noticias_destacada_idx (destacada)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. TABLAS admin_account y users (nuevas)
-- ============================================================
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

-- ============================================================
-- 8. TABLA noticias_relacionadas (nueva)
-- ============================================================
CREATE TABLE IF NOT EXISTS noticias_relacionadas (
    noticia_id INT NOT NULL,
    noticia_relacionada_id INT NOT NULL,
    PRIMARY KEY (noticia_id, noticia_relacionada_id),
    CONSTRAINT fk_noticias_rel_principal  FOREIGN KEY (noticia_id)            REFERENCES noticias(id) ON DELETE CASCADE,
    CONSTRAINT fk_noticias_rel_secundaria FOREIGN KEY (noticia_relacionada_id) REFERENCES noticias(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Limpieza de procedimientos temporales
-- ============================================================
DROP PROCEDURE IF EXISTS _add_col;
DROP PROCEDURE IF EXISTS _drop_idx;
DROP PROCEDURE IF EXISTS _add_unique;
