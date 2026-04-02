require("dotenv").config();
const db = require("../src/db/postgres");

(async () => {
  try {
    // Check current columns
    const { rows } = await db.query(
      "SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'eventos' ORDER BY ordinal_position"
    );
    console.log("Columnas actuales de eventos:", rows.map(r => `${r.column_name} (${r.data_type})`).join(", "));

    // Add missing columns
    await db.query("ALTER TABLE eventos ADD COLUMN IF NOT EXISTS equipo_id INTEGER REFERENCES equipos(id) ON DELETE SET NULL");
    await db.query("ALTER TABLE eventos ADD COLUMN IF NOT EXISTS tipo TEXT NOT NULL DEFAULT 'gol'");
    await db.query("ALTER TABLE eventos ADD COLUMN IF NOT EXISTS minuto INTEGER");
    await db.query("ALTER TABLE eventos ADD COLUMN IF NOT EXISTS partido_id INTEGER REFERENCES partidos(id) ON DELETE CASCADE");

    // Add check constraint if not exists
    try {
      await db.query("ALTER TABLE eventos DROP CONSTRAINT IF EXISTS eventos_tipo_check");
      await db.query("ALTER TABLE eventos ADD CONSTRAINT eventos_tipo_check CHECK (tipo IN ('gol', 'amarilla', 'roja', 'gol_en_contra'))");
    } catch (e) {
      console.log("Check constraint note:", e.message);
    }

    // Re-check
    const { rows: rows2 } = await db.query(
      "SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'eventos' ORDER BY ordinal_position"
    );
    console.log("Columnas finales:", rows2.map(r => `${r.column_name} (${r.data_type})`).join(", "));

    console.log("Fix completado.");
  } catch (error) {
    console.error("Error:", error);
  } finally {
    await db.pool.end();
  }
})();
