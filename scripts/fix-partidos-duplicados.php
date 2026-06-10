<?php
/**
 * fix-partidos-duplicados.php
 * Detecta y elimina partidos duplicados (mismo local, visitante y fecha) en la BD.
 * Conserva el partido con mayor prioridad: primero en_curso, luego mayor id.
 *
 * Uso:
 *   php scripts/fix-partidos-duplicados.php          → modo seguro (solo muestra duplicados)
 *   php scripts/fix-partidos-duplicados.php --fix    → ejecuta el borrado real
 */

$dryRun = !in_array('--fix', $argv ?? [], true);

// Cargar config
$config = require __DIR__ . '/../api/src/config.php';

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    $config['db_host'],
    $config['db_port'],
    $config['db_name']
);

try {
    $pdo = new PDO($dsn, $config['db_user'], $config['db_password'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    echo "ERROR conexión: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

echo "=== Fix partidos duplicados " . ($dryRun ? "[DRY RUN — sin cambios]" : "[MODO REAL]") . " ===" . PHP_EOL . PHP_EOL;

// 1. Buscar grupos de duplicados
$sql = "
    SELECT
        a.id        AS id_a,
        a.categoria AS cat_a,
        a.estado    AS estado_a,
        b.id        AS id_b,
        b.categoria AS cat_b,
        b.estado    AS estado_b,
        a.fecha,
        ea.nombre   AS local,
        ev.nombre   AS visitante
    FROM partidos a
    JOIN partidos b
        ON a.fecha     = b.fecha
       AND a.local     = b.local
       AND a.visitante = b.visitante
       AND a.id < b.id
    LEFT JOIN equipos ea ON a.local     = ea.id
    LEFT JOIN equipos ev ON a.visitante = ev.id
    ORDER BY a.fecha, ea.nombre
";

$duplicados = $pdo->query($sql)->fetchAll();

if (empty($duplicados)) {
    echo "No se encontraron partidos duplicados. La BD está limpia." . PHP_EOL;
    exit(0);
}

echo "Duplicados encontrados: " . count($duplicados) . PHP_EOL . PHP_EOL;

$idsAEliminar = [];

foreach ($duplicados as $row) {
    $idA      = (int) $row['id_a'];
    $idB      = (int) $row['id_b'];
    $aEsVivo  = $row['estado_a'] === 'en_curso';
    $bEsVivo  = $row['estado_b'] === 'en_curso';

    // Determinar cuál conservar: prioridad en_curso, si empate → mayor id
    if (!$aEsVivo && $bEsVivo) {
        $conservar  = $idB;
        $eliminar   = $idA;
    } elseif ($aEsVivo && !$bEsVivo) {
        $conservar  = $idA;
        $eliminar   = $idB;
    } else {
        // Ambos mismo estado → conservar el mayor id
        $conservar  = max($idA, $idB);
        $eliminar   = min($idA, $idB);
    }

    $idsAEliminar[] = $eliminar;

    echo sprintf(
        "  %s vs %s  (%s)" . PHP_EOL,
        $row['local'],
        $row['visitante'],
        $row['fecha']
    );
    echo sprintf(
        "    ID %d [%s / %s] → ELIMINAR" . PHP_EOL,
        $eliminar,
        $eliminar === $idA ? $row['cat_a'] : $row['cat_b'],
        $eliminar === $idA ? $row['estado_a'] : $row['estado_b']
    );
    echo sprintf(
        "    ID %d [%s / %s] → CONSERVAR" . PHP_EOL . PHP_EOL,
        $conservar,
        $conservar === $idA ? $row['cat_a'] : $row['cat_b'],
        $conservar === $idA ? $row['estado_a'] : $row['estado_b']
    );
}

$idsAEliminar = array_unique($idsAEliminar);
echo "IDs a eliminar: " . implode(', ', $idsAEliminar) . PHP_EOL . PHP_EOL;

if ($dryRun) {
    echo "DRY RUN: no se realizaron cambios." . PHP_EOL;
    echo "Para ejecutar el borrado real, corré:" . PHP_EOL;
    echo "  php scripts/fix-partidos-duplicados.php --fix" . PHP_EOL;
    exit(0);
}

// 2. Borrar en transacción
$placeholders = implode(',', array_fill(0, count($idsAEliminar), '?'));

try {
    $pdo->beginTransaction();

    $stmtEv = $pdo->prepare("DELETE FROM eventos WHERE partido_id IN ($placeholders)");
    $stmtEv->execute($idsAEliminar);
    echo "Eventos eliminados: " . $stmtEv->rowCount() . PHP_EOL;

    $stmtP = $pdo->prepare("DELETE FROM partidos WHERE id IN ($placeholders)");
    $stmtP->execute($idsAEliminar);
    echo "Partidos eliminados: " . $stmtP->rowCount() . PHP_EOL;

    $pdo->commit();
    echo PHP_EOL . "Listo. Duplicados eliminados correctamente." . PHP_EOL;
} catch (PDOException $e) {
    $pdo->rollBack();
    echo "ERROR durante el borrado: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
