require('dotenv').config();
const db = require('../src/db/postgres');

(async () => {
  try {
    await db.query("DELETE FROM equipos WHERE nombre IN ('Dragones','Tigres United','Águilas FC')");
    const r = await db.query('SELECT nombre,categoria FROM equipos ORDER BY nombre');
    console.log('equipos restantes:', r.rows);
  } catch (err) {
    console.error(err);
  } finally {
    db.pool.end();
  }
})();