require("dotenv").config({ path: require("path").join(__dirname, "..", ".env") });

const db = require("../src/db/postgres");

const TORNEO = "Apertura";
const CATEGORIA = "Tercera";
const FECHA2 = "2026-03-16";
const FECHA3 = "2026-03-23";
const HORA = "15:00";

const partidos = [
  { jornada: 2, local: "La Gloria", visitante: "Newbery", gl: 1, gv: 0 },
  { jornada: 2, local: "Maza Deportivo", visitante: "Atl. Quenuma", gl: 1, gv: 0 },
  { jornada: 2, local: "Unión Tres Lomas", visitante: "Atl. Argentino", gl: 2, gv: 0 },
  { jornada: 2, local: "Unión de Bonifacio", visitante: "Deportivo 17", gl: 1, gv: 3 },
  { jornada: 2, local: "Dep. Argentino", visitante: "Juventud", gl: 1, gv: 2 },
  { jornada: 2, local: "Villa del Parque", visitante: "El Ceibo", gl: 1, gv: 4 },
  { jornada: 2, local: "Dep. Garré", visitante: "Salazar FC", gl: 4, gv: 3 },

  { jornada: 3, local: "Newbery", visitante: "Maza Deportivo", gl: 1, gv: 1 },
  { jornada: 3, local: "Deportivo 17", visitante: "Roberts", gl: 1, gv: 0 },
  { jornada: 3, local: "Atl. Argentino", visitante: "Villa del Parque", gl: 2, gv: 0 },
  { jornada: 3, local: "Atl. Quenuma", visitante: "Unión Tres Lomas", gl: 0, gv: 0 },
  { jornada: 3, local: "Juventud", visitante: "Unión de Bonifacio", gl: 2, gv: 1 },
  { jornada: 3, local: "Salazar FC", visitante: "Dep. Argentino", gl: 1, gv: 3 },
  { jornada: 3, local: "El Ceibo", visitante: "Dep. Garré", gl: 2, gv: 3 },
];

const teamAliases = {
  "newbery": "jorge newbery",
  "jorge newbery": "jorge newbery",
  "roberts": "cecil a roberts",
  "cecil a roberts": "cecil a roberts",
  "maza deportivo": "deportivo maza",
  "deportivo maza": "deportivo maza",
  "union tres lomas": "union deportiva",
  "union deportiva": "union deportiva",
  "union de bonifacio": "union de bonifacio",
  "dep argentino": "deportivo argentino",
  "deportivo argentino": "deportivo argentino",
  "juventud": "juventud unida",
  "juventud unida": "juventud unida",
  "dep garre": "deportivo garre",
  "deportivo garre": "deportivo garre",
  "atl qunema": "atletico quenuma",
  "atl quenuma": "atletico quenuma",
  "atletico quenuma": "atletico quenuma",
  "atl argentino": "atletico argentino",
  "atletico argentino": "atletico argentino",
  "deportivo 17": "deportivo 17",
  "villa del parque": "villa del parque",
  "salazar fc": "salazar fc",
  "el ceibo": "el ceibo",
  "la gloria": "la gloria",
};

function normalize(text) {
  return String(text || "")
    .normalize("NFD")
    .replace(/\p{Diacritic}/gu, "")
    .replace(/[()]/g, " ")
    .replace(/[^a-zA-Z0-9\s]/g, " ")
    .replace(/\s+/g, " ")
    .trim()
    .toLowerCase();
}

function canonicalTeamName(name) {
  const normalized = normalize(name);
  return teamAliases[normalized] || normalized;
}

function fechaByJornada(jornada) {
  return jornada === 2 ? FECHA2 : FECHA3;
}

function partidoKey(jornada, localId, visitanteId) {
  return `${jornada}:${localId}:${visitanteId}`;
}

async function fetchEquipos(client) {
  const { rows } = await client.query(
    `SELECT id, nombre, estadio
     FROM equipos
     WHERE LOWER(categoria) = LOWER($1)`,
    [CATEGORIA]
  );

  const equipos = new Map();
  for (const equipo of rows) {
    equipos.set(canonicalTeamName(equipo.nombre), equipo);
  }
  return equipos;
}

async function findExistingMatch(client, jornada, localId, visitanteId) {
  const { rows } = await client.query(
    `SELECT id, fecha, hora, estadio, goleslocal, golesvisitante, estado
     FROM partidos
     WHERE LOWER(torneo) = LOWER($1)
       AND LOWER(categoria) = LOWER($2)
       AND jornada = $3
       AND local = $4
       AND visitante = $5`,
    [TORNEO, CATEGORIA, jornada, localId, visitanteId]
  );

  if (rows.length > 1) {
    throw new Error(`Hay ${rows.length} partidos duplicados para jornada ${jornada}, local ${localId}, visitante ${visitanteId}`);
  }

  return rows[0] || null;
}

async function findReversedMatch(client, jornada, localId, visitanteId) {
  const { rows } = await client.query(
    `SELECT id
     FROM partidos
     WHERE LOWER(torneo) = LOWER($1)
       AND LOWER(categoria) = LOWER($2)
       AND jornada = $3
       AND local = $4
       AND visitante = $5`,
    [TORNEO, CATEGORIA, jornada, visitanteId, localId]
  );
  return rows[0] || null;
}

function resolvePartidos(equiposByName) {
  const resolved = partidos.map((partido) => {
    const local = equiposByName.get(canonicalTeamName(partido.local));
    const visitante = equiposByName.get(canonicalTeamName(partido.visitante));

    if (!local || !visitante) {
      throw new Error(`No se pudo resolver el cruce ${partido.local} vs ${partido.visitante}`);
    }

    return {
      ...partido,
      localId: local.id,
      visitanteId: visitante.id,
      localNombre: local.nombre,
      visitanteNombre: visitante.nombre,
      estadio: local.estadio || "Estadio Local",
      fecha: fechaByJornada(partido.jornada),
      key: partidoKey(partido.jornada, local.id, visitante.id),
    };
  });

  const duplicates = resolved.filter(
    (partido, index) => resolved.findIndex((candidate) => candidate.key === partido.key) !== index
  );

  if (duplicates.length) {
    throw new Error(`El dataset contiene cruces duplicados: ${duplicates.map((partido) => partido.key).join(", ")}`);
  }

  return resolved;
}

async function removeUnexpectedMatches(client, expectedMatches) {
  const expectedKeys = new Set(expectedMatches.map((partido) => partido.key));
  const { rows } = await client.query(
    `SELECT id, jornada, local, visitante
     FROM partidos
     WHERE LOWER(torneo) = LOWER($1)
       AND LOWER(categoria) = LOWER($2)
       AND jornada IN (2, 3)`,
    [TORNEO, CATEGORIA]
  );

  const staleIds = rows
    .filter((row) => !expectedKeys.has(partidoKey(row.jornada, row.local, row.visitante)))
    .map((row) => row.id);

  if (!staleIds.length) {
    return [];
  }

  await client.query("DELETE FROM eventos WHERE partido_id = ANY($1)", [staleIds]);
  await client.query("DELETE FROM partidos WHERE id = ANY($1)", [staleIds]);
  return staleIds;
}

async function upsertPartido(client, partido) {
  const existing = await findExistingMatch(client, partido.jornada, partido.localId, partido.visitanteId);
  const reversed = existing ? null : await findReversedMatch(client, partido.jornada, partido.localId, partido.visitanteId);
  if (reversed) {
    throw new Error(`Existe el cruce invertido para ${partido.local} vs ${partido.visitante} en jornada ${partido.jornada} (id ${reversed.id})`);
  }

  const values = [
    partido.fecha,
    HORA,
    partido.estadio,
    partido.localId,
    partido.visitanteId,
    partido.gl,
    partido.gv,
    TORNEO,
    CATEGORIA,
    partido.jornada,
  ];

  if (existing) {
    await client.query(
      `UPDATE partidos
       SET fecha = $1,
           hora = $2,
           estadio = $3,
           local = $4,
           visitante = $5,
           goleslocal = $6,
           golesvisitante = $7,
           estado = 'jugado',
           amarillas = 0,
           rojas = 0,
           torneo = $8,
           categoria = $9,
           jornada = $10
       WHERE id = $11`,
      [...values, existing.id]
    );

    await client.query("DELETE FROM eventos WHERE partido_id = $1", [existing.id]);
    return { action: "updated", id: existing.id, local: partido.localNombre, visitante: partido.visitanteNombre };
  }

  const { rows } = await client.query(
    `INSERT INTO partidos (
      fecha, hora, estadio, local, visitante, estado,
      goleslocal, golesvisitante, amarillas, rojas,
      torneo, categoria, jornada
     )
     VALUES ($1, $2, $3, $4, $5, 'jugado', $6, $7, 0, 0, $8, $9, $10)
     RETURNING id`,
    values
  );

  return { action: "inserted", id: rows[0].id, local: partido.localNombre, visitante: partido.visitanteNombre };
}

async function verify(client, expectedMatches) {
  const { rows: resumen } = await client.query(
    `SELECT jornada,
            COUNT(*) AS total,
            COUNT(*) FILTER (WHERE estado = 'jugado') AS jugados
     FROM partidos
     WHERE LOWER(torneo) = LOWER($1)
       AND LOWER(categoria) = LOWER($2)
       AND jornada IN (2, 3)
     GROUP BY jornada
     ORDER BY jornada`,
    [TORNEO, CATEGORIA]
  );

  const { rows: partidosCargados } = await client.query(
    `SELECT p.jornada,
            p.local AS "localId",
            p.visitante AS "visitanteId",
            el.nombre AS local,
            ev.nombre AS visitante,
            p.goleslocal AS gl,
            p.golesvisitante AS gv,
            p.estado,
            COUNT(e.id) AS eventos
     FROM partidos p
     JOIN equipos el ON el.id = p.local
     JOIN equipos ev ON ev.id = p.visitante
     LEFT JOIN eventos e ON e.partido_id = p.id
     WHERE LOWER(p.torneo) = LOWER($1)
       AND LOWER(p.categoria) = LOWER($2)
       AND p.jornada IN (2, 3)
     GROUP BY p.id, p.jornada, p.local, p.visitante, el.nombre, ev.nombre, p.goleslocal, p.golesvisitante, p.estado
     ORDER BY p.jornada, el.nombre, ev.nombre`,
    [TORNEO, CATEGORIA]
  );

  if (resumen.length !== 2 || resumen.some((row) => Number(row.total) !== 7 || Number(row.jugados) !== 7)) {
    throw new Error(`La verificación de jornadas falló: ${JSON.stringify(resumen)}`);
  }

  if (partidosCargados.length !== expectedMatches.length) {
    throw new Error(`Se esperaban ${expectedMatches.length} partidos y se encontraron ${partidosCargados.length}`);
  }

  const expectedByKey = new Map(expectedMatches.map((partido) => [partidoKey(partido.jornada, partido.localId, partido.visitanteId), partido]));
  for (const partido of partidosCargados) {
    const expected = expectedByKey.get(partidoKey(partido.jornada, partido.localId, partido.visitanteId));
    if (!expected) {
      throw new Error(`Se encontró un partido inesperado en verificación: ${partido.local} vs ${partido.visitante}`);
    }
    if (Number(partido.gl) !== expected.gl || Number(partido.gv) !== expected.gv || partido.estado !== "jugado" || Number(partido.eventos) !== 0) {
      throw new Error(`El partido ${partido.local} vs ${partido.visitante} no quedó con el estado esperado`);
    }
  }

  return { resumen, partidosCargados };
}

async function main() {
  const client = await db.pool.connect();

  try {
    await client.query("BEGIN");

    const equiposByName = await fetchEquipos(client);
    const expectedMatches = resolvePartidos(equiposByName);
    const removedIds = await removeUnexpectedMatches(client, expectedMatches);
    const changes = [];

    for (const partido of expectedMatches) {
      changes.push(await upsertPartido(client, partido));
    }

    const verification = await verify(client, expectedMatches);
    await client.query("COMMIT");

    console.log("Carga Apertura/Tercera Fechas 2 y 3 completada.");
    if (removedIds.length) {
      console.log(`Se eliminaron ${removedIds.length} partidos sobrantes de las jornadas 2 y 3.`);
    }
    console.table(changes);
    console.table(verification.resumen);
    console.table(
      verification.partidosCargados.map((partido) => ({
        jornada: partido.jornada,
        local: partido.local,
        visitante: partido.visitante,
        marcador: `${partido.gl}-${partido.gv}`,
        estado: partido.estado,
        eventos: Number(partido.eventos),
      }))
    );
  } catch (error) {
    await client.query("ROLLBACK");
    console.error("Error cargando Apertura/Tercera Fechas 2 y 3:", error);
    process.exitCode = 1;
  } finally {
    client.release();
    await db.pool.end();
  }
}

main();