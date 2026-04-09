<?php
namespace App;

require_once __DIR__ . '/Database.php';

function ensureTemporadasTable(): void {
    Database::execute(
        "CREATE TABLE IF NOT EXISTS temporadas (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
}

function inferCurrentSeasonYears(): array {
    $year = (int) date('Y');
    $month = (int) date('n');
    if ($month < 7) {
        return [$year - 1, $year];
    }

    return [$year, $year + 1];
}

function buildSeasonName(int $startYear, int $endYear): string {
    return sprintf('%d/%02d', $startYear, $endYear % 100);
}

function buildSeasonSlug(int $startYear, int $endYear): string {
    return sprintf('%d-%02d', $startYear, $endYear % 100);
}

function ensureDefaultSeasons(): void {
    ensureTemporadasTable();

    $count = Database::fetchOne('SELECT COUNT(*) AS total FROM temporadas');
    if ((int) ($count['total'] ?? 0) === 0) {
        [$startYear, $endYear] = inferCurrentSeasonYears();

        $defaults = [
            [$startYear - 1, $endYear - 1, 'archivada', 0],
            [$startYear, $endYear, 'activa', 1],
        ];

        foreach ($defaults as [$from, $to, $status, $isActive]) {
            Database::execute(
                'INSERT INTO temporadas (nombre, slug, anio_inicio, anio_fin, estado, is_active) VALUES (?, ?, ?, ?, ?, ?)',
                [buildSeasonName($from, $to), buildSeasonSlug($from, $to), $from, $to, $status, $isActive]
            );
        }

        return;
    }

    $active = Database::fetchOne('SELECT id FROM temporadas WHERE is_active = 1 LIMIT 1');
    if ($active) {
        return;
    }

    $latest = Database::fetchOne('SELECT id FROM temporadas ORDER BY anio_fin DESC, anio_inicio DESC LIMIT 1');
    if ($latest) {
        Database::execute('UPDATE temporadas SET is_active = 0');
        Database::execute("UPDATE temporadas SET is_active = 1, estado = 'activa' WHERE id = ?", [(int) $latest['id']]);
    }
}

function fetchActiveSeason(): array {
    ensureDefaultSeasons();

    $season = Database::fetchOne('SELECT id, nombre, slug, anio_inicio, anio_fin, estado, is_active FROM temporadas WHERE is_active = 1 LIMIT 1');
    if ($season) {
        return $season;
    }

    throw new \RuntimeException('No hay una temporada activa configurada');
}

function fetchSeasonById(int $id): array|false {
    ensureDefaultSeasons();

    return Database::fetchOne(
        'SELECT id, nombre, slug, anio_inicio, anio_fin, estado, is_active FROM temporadas WHERE id = ?',
        [$id]
    );
}

function resolveSeasonId(null|int|string $rawSeason = null): int {
    ensureDefaultSeasons();

    if ($rawSeason === null || $rawSeason === '') {
        return (int) fetchActiveSeason()['id'];
    }

    if (is_numeric($rawSeason)) {
        $season = fetchSeasonById((int) $rawSeason);
        if ($season) {
            return (int) $season['id'];
        }
    }

    $season = Database::fetchOne(
        'SELECT id FROM temporadas WHERE slug = ? OR nombre = ? LIMIT 1',
        [(string) $rawSeason, (string) $rawSeason]
    );
    if ($season) {
        return (int) $season['id'];
    }

    throw new \InvalidArgumentException('La temporada solicitada no existe');
}

function setActiveSeason(int $seasonId): array {
    ensureDefaultSeasons();

    $season = fetchSeasonById($seasonId);
    if (!$season) {
        throw new \InvalidArgumentException('La temporada indicada no existe');
    }

    Database::execute('UPDATE temporadas SET is_active = 0');
    Database::execute("UPDATE temporadas SET is_active = 1, estado = 'activa' WHERE id = ?", [$seasonId]);

    return fetchActiveSeason();
}