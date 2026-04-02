/**
 * fix-data.js — Migración de datos existentes
 * 1. Asigna jornadas a partidos por fecha (2026-03-09→1, 2026-03-16→2, 2026-03-23→3)
 * 2. Actualiza escudos de equipos con rutas reales de logos
 * 3. Elimina partidos huérfanos del seed sin jornada válida
 */
require("dotenv").config({ path: require("path").join(__dirname, "..", ".env") });
const db = require("../src/db/postgres");

const logoMap = {
  "Cecil A. Roberts": "/logos/roberts.png",
  "Unión Deportiva": "/logos/uniontl.png",
  "Unión de Bonifacio": "/logos/bonifacio.png",
  "Deportivo Garré": "/logos/garre.png",
  "Deportivo Maza": "/logos/maza.png",
  "Deportivo Argentino": "/logos/deportivoarg.png",
  "Atlético Quenumá": "/logos/quenuma.png",
  "Atlético Argentino": "/logos/argentinos.png",
  "Salazar FC": "/logos/salazar.png",
  "Jorge Newbery": "/logos/newbery.png",
  "El Ceibo": "/logos/ceibo.png",
  "La Gloria": "/logos/lagloria.png",
  "Deportivo 17": "/logos/17.png",
  "Juventud Unida": "/logos/juventud.png",
  "Ferro": "/logos/ferro.png",
  "Villa del Parque": "/logos/villa.png",
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
