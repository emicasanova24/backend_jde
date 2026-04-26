/**
 * fix-data.js — Migración de datos existentes
 * 1. Asigna jornadas a partidos por fecha (2026-03-09→1, 2026-03-16→2, 2026-03-23→3)
 * 2. Actualiza escudos de equipos con rutas reales de logos
 * 3. Elimina partidos huérfanos del seed sin jornada válida
 */
require("dotenv").config({ path: require("path").join(__dirname, "..", ".env") });
const db = require("../src/db/postgres");

const logoMap = {
  "Cecil A. Roberts": "/assets-equipos/logos/roberts.png",
  "Unión Deportiva": "/assets-equipos/logos/uniontl.png",
  "Unión de Bonifacio": "/assets-equipos/logos/bonifacio.png",
  "Deportivo Garré": "/assets-equipos/logos/garre.png",
  "Deportivo Maza": "/assets-equipos/logos/maza.png",
  "Deportivo Argentino": "/assets-equipos/logos/deportivoarg.png",
  "Atlético Quenumá": "/assets-equipos/logos/quenuma.png",
  "Atlético Argentino": "/assets-equipos/logos/argentinos.png",
  "Salazar FC": "/assets-equipos/logos/salazar.png",
  "Jorge Newbery": "/assets-equipos/logos/newbery.png",
  "El Ceibo": "/assets-equipos/logos/ceibo.png",
  "La Gloria": "/assets-equipos/logos/lagloria.png",
  "Deportivo 17": "/assets-equipos/logos/17.png",
  "Juventud Unida": "/assets-equipos/logos/juventud.png",
  "Ferro": "/assets-equipos/logos/ferro.png",
  "Villa del Parque": "/assets-equipos/logos/villa.png",
};

async function run() {
  try {
    // 1. Asignar jornadas por fecha
    const fechaToJornada = {
      "2026-03-09": 1,
      "2026-03-16": 2,
      "2026-03-23": 3,
    };

    for (const [fecha, jornada] of Object.entries(fechaToJornada)) {
      const { rowCount } = await db.query(
        "UPDATE partidos SET jornada = $1 WHERE fecha::text LIKE $2 AND jornada IS NULL",
        [jornada, `${fecha}%`]
      );
      console.log(`Fecha ${fecha} → Jornada ${jornada}: ${rowCount} partidos actualizados`);
    }

    // 2. Actualizar escudos de equipos
    for (const [nombre, escudo] of Object.entries(logoMap)) {
      const { rowCount } = await db.query(
        "UPDATE equipos SET escudo = $1 WHERE nombre = $2",
        [escudo, nombre]
      );
      if (rowCount > 0) console.log(`Escudo actualizado: ${nombre} → ${escudo} (${rowCount} filas)`);
    }

    // 3. Eliminar partidos sin jornada (huérfanos del seed)
    const { rowCount: deleted } = await db.query(
      "DELETE FROM partidos WHERE jornada IS NULL"
    );
    console.log(`Partidos huérfanos eliminados (sin jornada): ${deleted}`);

    // 4. Verificación
    const { rows: jornadas } = await db.query(
      "SELECT jornada, COUNT(*) AS total, COUNT(*) FILTER (WHERE estado = 'jugado') AS jugados FROM partidos WHERE jornada IS NOT NULL GROUP BY jornada ORDER BY jornada"
    );
    console.log("\nEstado final de jornadas:");
    jornadas.forEach((j) => console.log(`  Fecha ${j.jornada}: ${j.total} partidos (${j.jugados} jugados)`));

    const { rows: equipoCheck } = await db.query(
      "SELECT nombre, escudo FROM equipos WHERE categoria = 'Primera' ORDER BY nombre LIMIT 5"
    );
    console.log("\nMuestra de escudos:");
    equipoCheck.forEach((e) => console.log(`  ${e.nombre}: ${e.escudo}`));

    console.log("\n✅ Migración completada");
  } catch (err) {
    console.error("Error en migración:", err);
  } finally {
    await db.pool.end();
  }
}

run();
