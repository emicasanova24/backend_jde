require("dotenv").config({ path: require("path").join(__dirname, "..", ".env") });
const db = require("../src/db/postgres");
(async () => {
  const r = await db.query("UPDATE equipos SET escudo = '/logos/villa.png' WHERE nombre = 'Villa del Parque'");
  console.log("Villa del Parque:", r.rowCount, "filas actualizadas");
  await db.pool.end();
})();
