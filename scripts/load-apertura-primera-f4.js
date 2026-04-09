/**
 * Carga manual – Apertura / Primera / Fecha 4 (solo fixture, sin resultados)
 * Script legado: no forma parte del runtime PHP actual.
 */
require("dotenv").config({ path: require("path").join(__dirname, "..", ".env") });
const db = require("../src/db/postgres");

const TORNEO    = "Apertura";
const CATEGORIA = "Primera";
const JORNADA   = 4;
const FECHA     = "2026-03-30";
const HORA      = "15:00";

/* ── Normalización de nombres ─────────────────────────────── */
const normalize = (s) =>
  s.normalize("NFD").replace(/[\u0300-\u036f]/g, "")
    .replace(/[^a-z0-9 ]/gi, "").trim().toLowerCase().replace(/\s+/g, " ");

const teamAliases = {
  "roberts":            "cecil a roberts",
  "newbery":            "jorge newbery",
  "maza deportivo":     "deportivo maza",
  "dep garre":          "deportivo garre",
  "dep argentino":      "deportivo argentino",
  "atl quenuma":        "atletico quenuma",
  "atl argentino":      "atletico argentino",
  "union tres lomas":   "union deportiva",
  "union de bonifacio": "union de bonifacio",
  "villa del parque":   "villa del parque",
  "juventud":           "juventud unida",
  "salazar fc":         "salazar fc",
  "el ceibo":           "el ceibo",
  "la gloria":          "la gloria",
};

function canonicalTeamName(raw) {
  const n = normalize(raw);
  return teamAliases[n] || n;
}

/* ── Dataset Fecha 4 (sin resultados) ─────────────────────── */
const partidos = [
  { local: "Roberts",            visitante: "Juventud" },
  { local: "Unión Tres Lomas",   visitante: "Newbery" },
  { local: "Villa del Parque",   visitante: "Atl. Quenumá" },
  { local: "Dep. Garré",         visitante: "Atl. Argentino" },
  { local: "Dep. Argentino",     visitante: "El Ceibo" },
  { local: "Maza Deportivo",     visitante: "La Gloria" },
  { local: "Unión de Bonifacio", visitante: "Salazar FC" },
];

/* ── Helpers ──────────────────────────────────────────────── */
let equiposCache = null;

async function fetchEquipos() {
  if (equiposCache) return equiposCache;
  const { rows } = await db.query(
    "SELECT id, nombre, estadio FROM equipos WHERE LOWER(categoria) = LOWER($1)",
    [CATEGORIA]
  );
  equiposCache = new Map();
  for (const eq of rows) {
    equiposCache.set(normalize(eq.nombre), eq);
  }
  return equiposCache;
}

async function resolveTeam(rawName) {
  const equipos = await fetchEquipos();
  const canonical = canonicalTeamName(rawName);
  const team = equipos.get(canonical);
  if (!team) throw new Error(`Equipo no encontrado en ${CATEGORIA}: "${rawName}" → "${canonical}"`);
  return team;
}

async function upsertPartido(p) {
  const localTeam     = await resolveTeam(p.local);
  const visitanteTeam = await resolveTeam(p.visitante);

  const { rows: existing } = await db.query(
    `SELECT id FROM partidos
     WHERE torneo=$1 AND categoria=$2 AND jornada=$3
       AND ((local=$4 AND visitante=$5) OR (local=$5 AND visitante=$4))`,
    [TORNEO, CATEGORIA, JORNADA, localTeam.id, visitanteTeam.id]
  );

  if (existing.length > 0) {
    const id = existing[0].id;
    await db.query(
      `UPDATE partidos
       SET local=$1, visitante=$2, estado='pendiente', fecha=$3, hora=$4
       WHERE id=$5`,
      [localTeam.id, visitanteTeam.id, FECHA, HORA, id]
    );
    console.log(`  ✏️  Actualizado ID ${id}: ${localTeam.nombre} vs ${visitanteTeam.nombre} [pendiente]`);
    return { action: "updated", id };
  }

  const { rows: ins } = await db.query(
    `INSERT INTO partidos
       (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante,
        amarillas, rojas, torneo, categoria, jornada)
     VALUES ($1,$2,$3,$4,$5,'pendiente',0,0,0,0,$6,$7,$8)
     RETURNING id`,
    [FECHA, HORA, "", localTeam.id, visitanteTeam.id, TORNEO, CATEGORIA, JORNADA]
  );
  console.log(`  ✅ Insertado ID ${ins[0].id}: ${localTeam.nombre} vs ${visitanteTeam.nombre} [pendiente]`);
  return { action: "inserted", id: ins[0].id };
}

/* ── Verificación ─────────────────────────────────────────── */
async function verify() {
  const { rows } = await db.query(
    `SELECT id, local, visitante, estado
     FROM partidos
     WHERE torneo=$1 AND categoria=$2 AND jornada=$3
     ORDER BY id`,
    [TORNEO, CATEGORIA, JORNADA]
  );

  console.log(`\n📊 Verificación jornada ${JORNADA}:`);
  console.log(`   Partidos: ${rows.length} (esperados: ${partidos.length})`);
  const pendientes = rows.filter((r) => r.estado === "pendiente").length;
  console.log(`   Pendientes: ${pendientes}`);

  if (rows.length !== partidos.length) {
    console.error("❌ Cantidad de partidos incorrecta");
    process.exit(1);
  }
  console.log("✅ Todo OK");
}

/* ── Main ─────────────────────────────────────────────────── */
(async () => {
  try {
    console.log(`\n🏟️  Cargando fixture ${TORNEO} / ${CATEGORIA} / Fecha ${JORNADA} (sin resultados)\n`);

    for (const p of partidos) {
      await upsertPartido(p);
    }

    await verify();
  } catch (err) {
    console.error("❌ Error:", err.message);
    process.exit(1);
  } finally {
    await db.pool.end();
  }
})();
