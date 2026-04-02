require("dotenv").config();
const db = require("../src/db/postgres");

(async () => {
  const { rows: cols } = await db.query(
    "SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'eventos' ORDER BY ordinal_position"
  );
  console.table(cols);

  const { rows: count } = await db.query("SELECT COUNT(*) FROM eventos");
  console.log("Total rows:", count[0].count);

  // Check constraints
  const { rows: constraints } = await db.query(
    "SELECT conname, pg_get_constraintdef(oid) AS def FROM pg_constraint WHERE conrelid = 'eventos'::regclass"
  );
  console.table(constraints);

  await db.pool.end();
})();
