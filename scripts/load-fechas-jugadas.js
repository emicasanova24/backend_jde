require('dotenv').config();
const db = require('../src/db/postgres');

const TORNEO = process.env.TORNEO || 'Apertura';
const CATEGORIA = process.env.CATEGORIA || 'Primera';
const FECHA1 = process.env.FECHA1 || '2026-03-09';
const FECHA2 = process.env.FECHA2 || '2026-03-16';
const FECHA3 = process.env.FECHA3 || '2026-03-23';
const HORA = process.env.HORA || '15:00';

const teamAliases = {
  'roberts': 'cecil a roberts',
  'union deportiva tres lomas': 'union deportiva',
  'union deportiva bonifacio': 'union de bonifacio',
  'villa del parque': 'villa del parque',
};

const partidos = [
  // Fecha 1
  { jornada: 1, local: 'Deportivo 17', visitante: 'Deportivo Argentino', gl: 3, gv: 1 },
  { jornada: 1, local: 'Juventud Unida', visitante: 'Deportivo Garré', gl: 0, gv: 3 },
  { jornada: 1, local: 'Roberts', visitante: 'Unión Deportiva (Bonifacio)', gl: 2, gv: 1 },
  { jornada: 1, local: 'Atlético Argentino', visitante: 'Deportivo Maza', gl: 0, gv: 1 },
  { jornada: 1, local: 'El Ceibo', visitante: 'Unión Deportiva (Tres Lomas)', gl: 0, gv: 1 },
  { jornada: 1, local: 'Atlético Quenumá', visitante: 'La Gloria', gl: 1, gv: 1 },
  { jornada: 1, local: 'Salazar FC', visitante: 'Villa del Parque', gl: 0, gv: 0 },

  // Fecha 2
  { jornada: 2, local: 'Unión Deportiva (Tres Lomas)', visitante: 'Atlético Argentino', gl: 2, gv: 1 },
  { jornada: 2, local: 'Deportivo Argentino', visitante: 'Juventud Unida', gl: 0, gv: 2 },
  { jornada: 2, local: 'Deportivo Maza', visitante: 'Atlético Quenumá', gl: 0, gv: 2 },
  { jornada: 2, local: 'Villa del Parque', visitante: 'El Ceibo', gl: 1, gv: 1 },
  { jornada: 2, local: 'Unión Deportiva (Bonifacio)', visitante: 'Deportivo 17', gl: 1, gv: 1 },
  { jornada: 2, local: 'Deportivo Garré', visitante: 'Salazar FC', gl: 2, gv: 3 },
  { jornada: 2, local: 'La Gloria', visitante: 'Jorge Newbery', gl: 0, gv: 1 },

  // Fecha 3
  { jornada: 3, local: 'Atlético Quenumá', visitante: 'Unión Deportiva (Tres Lomas)', gl: 0, gv: 5 },
  { jornada: 3, local: 'El Ceibo', visitante: 'Deportivo Garré', gl: 1, gv: 1 },
  { jornada: 3, local: 'Deportivo 17', visitante: 'Roberts', gl: 0, gv: 3 },
  { jornada: 3, local: 'Jorge Newbery', visitante: 'Deportivo Maza', gl: 5, gv: 2 },
  { jornada: 3, local: 'Atlético Argentino', visitante: 'Villa del Parque', gl: 0, gv: 2 },
  { jornada: 3, local: 'Salazar FC', visitante: 'Deportivo Argentino', gl: 2, gv: 2 },
  { jornada: 3, local: 'Juventud Unida', visitante: 'Unión Deportiva (Bonifacio)', gl: 2, gv: 0 },
];

function normalize(text) {
  return String(text || '')
    .normalize('NFD')
    .replace(/\p{Diacritic}/gu, '')
    .replace(/[()]/g, ' ')
    .replace(/[^a-zA-Z0-9\s]/g, ' ')
    .replace(/\s+/g, ' ')
    .trim()
    .toLowerCase();
}

function canonicalTeamName(name) {
  const n = normalize(name);
  return teamAliases[n] || n;
}

function fechaByJornada(jornada) {
  if (jornada === 1) return FECHA1;
  if (jornada === 2) return FECHA2;
  return FECHA3;
}

(async () => {
  try {
    await db.query('BEGIN');

    await db.query("ALTER TABLE partidos ADD COLUMN IF NOT EXISTS torneo TEXT DEFAULT 'Apertura'");
    await db.query("ALTER TABLE partidos ADD COLUMN IF NOT EXISTS categoria TEXT DEFAULT 'Primera'");

    const { rows: equipos } = await db.query(
      'SELECT id, nombre, estadio, categoria FROM equipos WHERE LOWER(categoria) = LOWER($1)',
      [CATEGORIA]
    );

    const getEquipo = (nombre) => {
      const key = canonicalTeamName(nombre);
      return equipos.find((e) => canonicalTeamName(e.nombre) === key) || null;
    };

    await db.query(
      'DELETE FROM partidos WHERE torneo = $1 AND categoria = $2 AND fecha IN ($3, $4, $5)',
      [TORNEO, CATEGORIA, FECHA1, FECHA2, FECHA3]
    );

    for (const p of partidos) {
      const local = getEquipo(p.local);
      const visitante = getEquipo(p.visitante);

      if (!local || !visitante) {
        console.warn('No se encontró equipo para partido:', p.local, 'vs', p.visitante);
        continue;
      }

      const fecha = fechaByJornada(p.jornada);
      const estadio = local.estadio || 'Estadio Local';

      await db.query(
        `INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria)
         VALUES ($1, $2, $3, $4, $5, 'jugado', $6, $7, 0, 0, $8, $9)`,
        [fecha, HORA, estadio, local.id, visitante.id, p.gl, p.gv, TORNEO, CATEGORIA]
      );
    }

    await db.query('COMMIT');
    console.log('Fechas 1, 2 y 3 cargadas correctamente como jugadas.');
    console.log(`Torneo: ${TORNEO} | Categoria: ${CATEGORIA}`);
    console.log(`Fecha1: ${FECHA1} | Fecha2: ${FECHA2} | Fecha3: ${FECHA3}`);
  } catch (error) {
    await db.query('ROLLBACK');
    console.error('Error cargando fechas jugadas:', error);
    process.exit(1);
  } finally {
    await db.pool.end();
  }
})();
