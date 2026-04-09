<?php
namespace App;

class Database {
    private static ?\PDO $pdo = null;

    public static function getConnection(): \PDO {
        if (self::$pdo === null) {
            $config = require __DIR__ . '/config.php';
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $config['db_host'],
                $config['db_port'],
                $config['db_name']
            );
            $options = [
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            self::$pdo = new \PDO($dsn, $config['db_user'], $config['db_password'], $options);
        }
        return self::$pdo;
    }

    public static function query(string $sql, array $params = []): \PDOStatement {
        $stmt = self::getConnection()->prepare(self::convertPlaceholders($sql));
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetchAll(string $sql, array $params = []): array {
        return self::query($sql, $params)->fetchAll();
    }

    public static function fetchOne(string $sql, array $params = []): array|false {
        return self::query($sql, $params)->fetch();
    }

    public static function execute(string $sql, array $params = []): int {
        return self::query($sql, $params)->rowCount();
    }

    public static function lastInsertId(): int {
        return (int) self::getConnection()->lastInsertId();
    }

    public static function columnExists(string $table, string $column): bool {
        return (bool) self::fetchOne(
            'SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ? LIMIT 1',
            [$table, $column]
        );
    }

    public static function indexExists(string $table, string $index): bool {
        return (bool) self::fetchOne(
            'SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ? LIMIT 1',
            [$table, $index]
        );
    }

    public static function dropIndexIfExists(string $table, string $index): void {
        if (!self::indexExists($table, $index)) {
            return;
        }

        self::getConnection()->exec(sprintf(
            'DROP INDEX %s ON %s',
            self::quoteIdentifier($index),
            self::quoteIdentifier($table)
        ));
    }

    public static function ensureUniqueIndex(string $table, string $index, string $columns): void {
        if (self::indexExists($table, $index)) {
            return;
        }

        self::getConnection()->exec(sprintf(
            'CREATE UNIQUE INDEX %s ON %s (%s)',
            self::quoteIdentifier($index),
            self::quoteIdentifier($table),
            $columns
        ));
    }

    public static function resetAutoIncrement(string $table): void {
        self::getConnection()->exec(sprintf(
            'ALTER TABLE %s AUTO_INCREMENT = 1',
            self::quoteIdentifier($table)
        ));
    }

    /**
     * Conserva compatibilidad con placeholders numerados heredados ($1, $2, ...).
     */
    private static function convertPlaceholders(string $sql): string {
        return preg_replace('/\$(\d+)/', '?', $sql);
    }

    private static function quoteIdentifier(string $identifier): string {
        if (!preg_match('/^[A-Za-z0-9_]+$/', $identifier)) {
            throw new \InvalidArgumentException('Identificador SQL inválido');
        }

        return '`' . $identifier . '`';
    }
}
