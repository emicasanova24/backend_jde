require('dotenv').config();
const db = require('../src/db/postgres');

(async () => {
  try {
    const { rows } = await db.query(
      `SELECT fecha, COUNT(*)::int AS cantidad
       FROM partidos
       WHERE torneo = 'Apertura'
         AND categoria = 'Primera'
         AND fecha IN ('2026-03-09', '2026-03-16', '2026-03-23')
       GROUP BY fecha
       ORDER BY fecha`
    );
    console.log(rows);
  } catch (error) {
    console.error(error);
    process.exit(1);
  } finally {
    await db.pool.end();
  }
})();
