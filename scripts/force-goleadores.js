require('dotenv').config();
const db = require('../src/db/postgres');

const teamAliases = {
  'roberts': 'cecil a roberts',
  'cecil a roberts': 'cecil a roberts',
  'union deportiva tres lomas': 'union deportiva',
  'union deportiva bonifacio': 'union de bonifacio',
  'union de bonifacio': 'union de bonifacio',
  'villa del parque': 'villa del parque',
  'atletico quenu ma': 'atletico quenuma'
};

const scorers = [
  { name: 'Agustin Barrio', team: 'Union Deportiva - Tres Lomas', goals: 4 },

  { name: 'Cesar Medina', team: 'Jorge Newbery', goals: 3 },
  { name: 'Tomas Rodriguez', team: 'Deportivo Garre', goals: 3 },
  { name: 'Lucas Vides', team: 'Salazar FC', goals: 3 },

  { name: 'Joan Benavidez', team: 'Deportivo 17', goals: 2 },
  { name: 'Alana Farias', team: 'Union Deportiva - Tres Lomas', goals: 2 },
  { name: 'Uriel Tomasello', team: 'Union Deportiva - Tres Lomas', goals: 2 },
  { name: 'Cruz Zarate', team: 'Union Deportiva - Tres Lomas', goals: 2 },
  { name: 'Lucas Rivas', team: 'Roberts', goals: 2 },
  { name: 'Maico Saucedo', team: 'Deportivo Argentino', goals: 2 },
  { name: 'Tobias Calvo', team: 'Deportivo Garre', goals: 2 },

  { name: 'Benjamin Guiyello', team: 'Deportivo 17', goals: 1 },
  { name: 'Nahuel Sandalich', team: 'Deportivo 17', goals: 1 },
  { name: 'Dardo Gonzales', team: 'Deportivo Argentino', goals: 1 },
  { name: 'Robert Campaz', team: 'Roberts', goals: 1 },
  { name: 'Tomas Altamiranda', team: 'Roberts', goals: 1 },
  { name: 'Diego Achaval', team: 'Union Deportiva - Bonifacio', goals: 1 },
  { name: 'Matias Rodi', team: 'Deportivo Maza', goals: 1 },
  { name: 'Matias Hoyos', team: 'Atletico Argentino', goals: 1 },
  { name: 'Daniel Flores', team: 'Juventud Unida', goals: 1 },
  { name: 'Rocco Monteventi', team: 'Juventud Unida', goals: 1 },
  { name: 'Lucas Cepeda', team: 'Atletico Quenuma', goals: 1 },
  { name: 'Tobias Garcia', team: 'Atletico Quenuma', goals: 1 },
  { name: 'Julian Sosa', team: 'Villa del Parque', goals: 1 },
  { name: 'Claudio Ometto', team: 'El Ceibo', goals: 1 },
  { name: 'Juan Shaffer', team: 'Union Deportiva - Bonifacio', goals: 1 },
  { name: 'Benicio Mascioli', team: 'Deportivo Garre', goals: 1 },
  { name: 'Marcelo Mansilla', team: 'Salazar FC', goals: 1 },
  { name: 'Hector Cardozo', team: 'Jorge Newbery', goals: 1 },
  { name: 'Ignacio Quiroga', team: 'Atletico Quenuma', goals: 1 },
  { name: 'Leo Aguirre', team: 'La Gloria', goals: 1 },
  { name: 'Maximo Romero', team: 'El Ceibo', goals: 1 },
  { name: 'Nicolas Ibanez', team: 'Jorge Newbery', goals: 1 },
  { name: 'Sebastian Duckardt', team: 'Deportivo Maza', goals: 1 },
  { name: 'Lucio Moggia', team: 'Deportivo Maza', goals: 1 },
  { name: 'Matias Horbal', team: 'Villa del Parque', goals: 1 },
  { name: 'Julian Bertoloti', team: 'Villa del Parque', goals: 1 },
  { name: 'Julian Cocimano', team: 'Roberts', goals: 1 },
  { name: 'Carlos Benito', team: 'Salazar FC', goals: 1 },
  { name: 'Lautaro Sousa', team: 'Juventud Unida', goals: 1 },
  { name: 'Geronimo Culaso', team: 'Juventud Unida', goals: 1 }
];

const manualNameAliases = {
  'julian bertoloti': ['bertolotti julian'],
  'geronimo culaso': ['culasso geronimo'],
  'lautaro sousa': ['souza lautaro'],
  'dardo gonzales': ['gonzalez dardo'],
  'benjamin guiyello': ['guiyelo benjamin'],
  'agustin barrio': ['barrio agustin'],
  'alana farias': ['farias alan'],
  'juan shaffer': ['achaval juan']
};

function normalize(text) {
  return String(text || '')
    .normalize('NFD')
    .replace(/\p{Diacritic}/gu, '')
    .replace(/[^a-zA-Z0-9\s]/g, ' ')
    .replace(/\s+/g, ' ')
    .trim()
    .toLowerCase();
}

function canonicalTeamName(input) {
  const n = normalize(input).replace(/\s+-\s+/g, ' ');
  return teamAliases[n] || n;
}

function tokenSet(name) {
  return new Set(normalize(name).split(' ').filter(Boolean));
}

function similarName(a, b) {
  const na = normalize(a);
  const nb = normalize(b);
  if (na === nb) return true;

  const aParts = na.split(' ').filter(Boolean);
  const bParts = nb.split(' ').filter(Boolean);

  if (aParts.length === 2 && bParts.length === 2) {
    if (aParts[0] === bParts[1] && aParts[1] === bParts[0]) return true;
  }

  const aSet = tokenSet(na);
  const bSet = tokenSet(nb);
  let common = 0;
  for (const t of aSet) {
    if (bSet.has(t)) common += 1;
  }
  return common >= Math.min(2, Math.min(aSet.size, bSet.size));
}

function toSurnameFirst(name) {
  const parts = normalize(name).split(' ').filter(Boolean);
  if (parts.length < 2) return name;
  const first = parts[0];
  const rest = parts.slice(1).join(' ');
  return `${rest} ${first}`;
}

async function main() {
  await db.query('BEGIN');
  try {
    const { rows } = await db.query(
      `SELECT j.id, j.nombre AS jugador_nombre, e.nombre AS equipo_nombre
       FROM jugadores j
       LEFT JOIN equipos e ON e.id = j.equipo`
    );
    const { rows: equipRows } = await db.query(
      `SELECT id, nombre, categoria FROM equipos ORDER BY id`
    );

    await db.query('UPDATE jugadores SET goles = 0');

    let applied = 0;
    const missing = [];

    for (const s of scorers) {
      const teamCanonical = canonicalTeamName(s.team);
      const nameCanonical = normalize(s.name);
      const aliases = manualNameAliases[nameCanonical] || [];

      const teamCandidates = rows.filter((r) => canonicalTeamName(r.equipo_nombre) === teamCanonical);

      let found = teamCandidates.find((r) => similarName(r.jugador_nombre, s.name));
      if (!found && aliases.length) {
        for (const alias of aliases) {
          found = teamCandidates.find((r) => similarName(r.jugador_nombre, alias));
          if (found) break;
        }
      }

      if (!found) {
        const teamIdRow = equipRows.find((e) => {
          const isTeam = canonicalTeamName(e.nombre) === teamCanonical;
          const isPrimera = normalize(e.categoria) === 'primera';
          return isTeam && isPrimera;
        }) || equipRows.find((e) => canonicalTeamName(e.nombre) === teamCanonical);

        if (!teamIdRow) {
          missing.push(`${s.name} (${s.team})`);
          continue;
        }

        const createdName = toSurnameFirst(s.name);
        const inserted = await db.query(
          `INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
           VALUES ($1, 'No asignada', $2, $3, 0, 0)
           RETURNING id`,
          [createdName, teamIdRow.id, s.goals]
        );

        rows.push({
          id: inserted.rows[0].id,
          jugador_nombre: createdName,
          equipo_nombre: teamIdRow.nombre,
        });

        applied += 1;
        continue;
      }

      await db.query('UPDATE jugadores SET goles = $1 WHERE id = $2', [s.goals, found.id]);
      applied += 1;
    }

    await db.query('COMMIT');

    console.log(`Goleadores forzados: ${applied}/${scorers.length}`);
    if (missing.length) {
      console.log('No encontrados:');
      missing.forEach((m) => console.log(`- ${m}`));
    }
  } catch (error) {
    await db.query('ROLLBACK');
    throw error;
  } finally {
    await db.pool.end();
  }
}

main().catch((err) => {
  console.error('Error al forzar goleadores:', err);
  process.exit(1);
});
