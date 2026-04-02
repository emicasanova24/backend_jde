require('dotenv').config();
const db = require('../src/db/postgres');

const TORNEO = process.env.TORNEO || 'Apertura';
const FECHA1 = process.env.FECHA1 || null;
const FECHA2 = process.env.FECHA2 || null;
const FECHA3 = process.env.FECHA3 || null;
const HORA = process.env.HORA || '15:00';

const formatDate = (date) => date.toISOString().slice(0, 10);
const addDays = (date, days) => { const d = new Date(date); d.setDate(d.getDate() + days); return d; };

const getFechas = () => {
  if (FECHA1 && FECHA2 && FECHA3) return [FECHA1, FECHA2, FECHA3];
  const now = new Date();
  const d1 = addDays(now, 7);
  const d2 = addDays(now, 14);
  const d3 = addDays(now, 21);
  return [formatDate(d1), formatDate(d2), formatDate(d3)];
};

const roundRobin = (teams) => {
  const t = teams.slice();
  const isOdd = t.length % 2 === 1;
  if (isOdd) t.push({ bye: true });
  const n = t.length;
  const rounds = [];

  for (let r = 0; r < n - 1; r++) {
    const pairs = [];
    for (let i = 0; i < n / 2; i++) {
      const home = t[i];
      const away = t[n - 1 - i];
      if (!home.bye && !away.bye) {
        pairs.push({ home, away });
      }
    }
    rounds.push(pairs);
    // rotate (except first element)
    const last = t.pop();
    t.splice(1, 0, last);
  }

  return rounds;
};

(async () => {
  try {
    const [fecha1, fecha2, fecha3] = getFechas();

    await db.query("ALTER TABLE partidos ADD COLUMN IF NOT EXISTS torneo TEXT DEFAULT 'Apertura'");
    await db.query("ALTER TABLE partidos ADD COLUMN IF NOT EXISTS categoria TEXT DEFAULT 'Primera'");

    // Carga todas las categorías existentes de equipos
    const { rows: categorias } = await db.query('SELECT DISTINCT categoria FROM equipos');
    if (!categorias.length) {
      throw new Error('No hay categorías de equipos en la BD');
    }

    console.log('Fechas objetivo:', fecha1, fecha2);
    console.log('Torneo:', TORNEO);

    for (const catRow of categorias) {
      const categoria = catRow.categoria;
      const { rows: equipos } = await db.query('SELECT id, nombre, estadio FROM equipos WHERE categoria = $1 ORDER BY nombre', [categoria]);

      if (equipos.length < 2) {
        console.log(`Categoría ${categoria} tiene menos de 2 equipos, se salta.`);
        continue;
      }

      const rounds = roundRobin(equipos);
      // Tomamos solo primeras 2 fechas
      const selectedRounds = rounds.slice(0, 2);

      // Borrar partidos antiguos para estas 2 fechas en categoría+torneo
      await db.query('DELETE FROM partidos WHERE torneo = $1 AND categoria = $2 AND fecha IN ($3, $4, $5)', [TORNEO, categoria, fecha1, fecha2, fecha3]);

      for (let ri = 0; ri < selectedRounds.length; ri++) {
        const fecha = ri === 0 ? fecha1 : fecha2;
        const matchups = selectedRounds[ri];

        for (const match of matchups) {
          const estadio = match.home.estadio || 'Estadio Local';
          await db.query(
            `INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria)
             VALUES ($1, $2, $3, $4, $5, 'pendiente', 0, 0, 0, 0, $6, $7)`,
            [fecha, HORA, estadio, match.home.id, match.away.id, TORNEO, categoria]
          );
        }

        console.log(`Categoria ${categoria} - fecha ${ri + 1} (${fecha}) cargada con ${matchups.length} partidos`);
      }
    }

    // Aplicar resultados concretos de la fecha 3 (fecha 1/2 ya quedan como fixture sin puntaje exacto)
    const fecha3results = [
      { local: 'Atlético Quenumá', visitante: 'Unión Deportiva', golesLocal: 0, golesVisitante: 5 },
      { local: 'El Ceibo', visitante: 'Deportivo Garré', golesLocal: 1, golesVisitante: 1 },
      { local: 'Deportivo 17', visitante: 'Cecil A. Roberts', golesLocal: 0, golesVisitante: 3 },
      { local: 'Jorge Newbery', visitante: 'Deportivo Maza', golesLocal: 5, golesVisitante: 2 },
      { local: 'Atlético Argentino', visitante: 'Villa Del Parque', golesLocal: 0, golesVisitante: 2 },
      { local: 'Salazar FC', visitante: 'Deportivo Argentino', golesLocal: 2, golesVisitante: 2 },
      { local: 'Juventud Unida', visitante: 'Unión de Bonifacio', golesLocal: 2, golesVisitante: 0 },
    ];

    for (const r of fecha3results) {
      const localObj = await db.query('SELECT id FROM equipos WHERE LOWER(nombre) = LOWER($1) AND categoria = $2 LIMIT 1', [r.local, 'Primera']);
      const visitaObj = await db.query('SELECT id FROM equipos WHERE LOWER(nombre) = LOWER($1) AND categoria = $2 LIMIT 1', [r.visitante, 'Primera']);
      if (!localObj.rows.length || !visitaObj.rows.length) {
        console.warn('Equipo no encontrado para fecha 3:', r.local, r.visitante);
        continue;
      }
      const localId = localObj.rows[0].id;
      const visitanteId = visitaObj.rows[0].id;

      await db.query(
        `UPDATE partidos SET estado='jugado', goleslocal=$1, golesvisitante=$2
         WHERE fecha=$3 AND torneo=$4 AND categoria='Primera' AND local=$5 AND visitante=$6`,
        [r.golesLocal, r.golesVisitante, fecha3, TORNEO, localId, visitanteId]
      );
    }

    console.log('Resultados de la fecha 3 inyectados con puntajes exactos.');

    console.log('Se cargaron forzadamente primera y segunda fecha para todas las categorías.');
    process.exit(0);
  } catch (error) {
    console.error('Error al forzar fechas:', error);
    process.exit(1);
  }
})();
