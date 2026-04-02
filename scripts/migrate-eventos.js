require("dotenv").config();
const db = require("../src/db/postgres");

(async () => {
  try {
    await db.query("ALTER TABLE partidos ADD COLUMN IF NOT EXISTS jornada INTEGER");
    console.log("Columna jornada agregada a partidos.");

    await db.query(`
      CREATE TABLE IF NOT EXISTS eventos (
        id SERIAL PRIMARY KEY,
        partido_id INTEGER REFERENCES partidos(id) ON DELETE CASCADE,
        jugador_id INTEGER REFERENCES jugadores(id) ON DELETE SET NULL,
        equipo_id INTEGER REFERENCES equipos(id) ON DELETE SET NULL,
        tipo TEXT NOT NULL CHECK (tipo IN ('gol', 'amarilla', 'roja', 'gol_en_contra')),
        minuto INTEGER
      );
    `);
    console.log("Tabla eventos creada o verificada.");

    console.log("Migración completada.");
  } catch (error) {
    console.error("Error en migración:", error);
  } finally {
    await db.pool.end();
  }
})();
