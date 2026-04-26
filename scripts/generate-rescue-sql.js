const fs = require('fs');
const path = require('path');
const vm = require('vm');

const ROOT = path.resolve(__dirname, '..');
const API_DIR = path.join(ROOT, 'api');
const OUTPUT_DIR = path.join(ROOT, 'exports');
const OUTPUT_FILE = path.join(OUTPUT_DIR, 'rescate-mysql.sql');

const TEAM_KEY_TO_NAME = {
  'cecil a roberts': 'Cecil A. Roberts',
  'union deportiva': 'Unión Deportiva',
  'union de bonifacio': 'Unión de Bonifacio',
  'deportivo garre': 'Deportivo Garré',
  'deportivo maza': 'Deportivo Maza',
  'deportivo argentino': 'Deportivo Argentino',
  'atletico quenuma': 'Atlético Quenumá',
  'atletico argentino': 'Atlético Argentino',
  'salazar fc': 'Salazar FC',
  'jorge newbery': 'Jorge Newbery',
  'el ceibo': 'El Ceibo',
  'la gloria': 'La Gloria',
  'deportivo 17': 'Deportivo 17',
  'juventud unida': 'Juventud Unida',
  'ferro': 'Ferro',
  'villa del parque': 'Villa del Parque',
};

const TEAM_ALIASES = {
  'roberts': 'cecil a roberts',
  'newbery': 'jorge newbery',
  'maza deportivo': 'deportivo maza',
  'deportivo maza': 'deportivo maza',
  'dep garre': 'deportivo garre',
  'deportivo garre': 'deportivo garre',
  'dep argentino': 'deportivo argentino',
  'deportivo argentino': 'deportivo argentino',
  'atl quenuma': 'atletico quenuma',
  'atletico quenuma': 'atletico quenuma',
  'atl qunema': 'atletico quenuma',
  'atletico quenu ma': 'atletico quenuma',
  'atl argentino': 'atletico argentino',
  'atletico argentino': 'atletico argentino',
  'union tres lomas': 'union deportiva',
  'union deportiva tres lomas': 'union deportiva',
  'union de bonifacio': 'union de bonifacio',
  'union deportiva bonifacio': 'union de bonifacio',
  'villa del parque': 'villa del parque',
  'juventud': 'juventud unida',
  'juventud unida': 'juventud unida',
  'salazar fc': 'salazar fc',
  'el ceibo': 'el ceibo',
  'la gloria': 'la gloria',
  'ferro': 'ferro',
  'cecil a roberts': 'cecil a roberts',
  'union deportiva': 'union deportiva',
  'deportivo 17': 'deportivo 17',
};

const MANUAL_PLAYER_ALIASES = {
  'julian bertoloti': ['Bertolotti Julian'],
  'geronimo culaso': ['Culasso Gerónimo', 'Culasso Geronimo'],
  'lautaro sousa': ['Souza Lautaro'],
  'dardo gonzales': ['González Dardo', 'Gonzalez Dardo'],
  'benjamin guiyello': ['Guiyelo Benjamin'],
  'agustin barrio': ['Barrio Agustin'],
  'alana farias': ['Farias Alan'],
  'juan shaffer': ['Achaval Juan'],
};

const FALLBACK_TEAMS = {
  'Primera': {
    'Villa del Parque': { estadio: 'Estadio Local', escudo: '/assets-equipos/logos/villa.png' },
  },
  'Tercera': {
    'Villa del Parque': { estadio: 'Estadio Local', escudo: '/assets-equipos/logos/villa.png' },
  },
  'Senior': {
    'Villa del Parque': { estadio: 'Estadio Local', escudo: '/assets-equipos/logos/villa.png' },
  },
};

function normalize(text) {
  return String(text || '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[^a-zA-Z0-9\s]/g, ' ')
    .replace(/\s+/g, ' ')
    .trim()
    .toLowerCase();
}

function canonicalTeamKey(name) {
  const key = normalize(name).replace(/\s+-\s+/g, ' ');
  return TEAM_ALIASES[key] || key;
}

function canonicalTeamName(name) {
  const key = canonicalTeamKey(name);
  return TEAM_KEY_TO_NAME[key] || name;
}

function sqlString(value) {
  if (value === null || value === undefined) {
    return 'NULL';
  }
  return `'${String(value).replace(/\\/g, '\\\\').replace(/'/g, "''")}'`;
}

function sqlNumber(value, fallback = 0) {
  const number = Number(value);
  return Number.isFinite(number) ? String(number) : String(fallback);
}

function readUtf8(filePath) {
  return fs.readFileSync(filePath, 'utf8');
}

function extractConstArray(filePath, constName) {
  const source = readUtf8(filePath);
  const match = source.match(new RegExp(`const\\s+${constName}\\s*=\\s*(\\[[\\s\\S]*?\\]);`));
  if (!match) {
    throw new Error(`No se encontró const ${constName} en ${filePath}`);
  }
  return vm.runInNewContext(match[1]);
}

function extractConstObject(filePath, constName) {
  const source = readUtf8(filePath);
  const match = source.match(new RegExp(`const\\s+${constName}\\s*=\\s*(\\{[\\s\\S]*?\\});`));
  if (!match) {
    throw new Error(`No se encontró const ${constName} en ${filePath}`);
  }
  return vm.runInNewContext(`(${match[1]})`);
}

function fechaByJornada(jornada, mapping) {
  return mapping[jornada] || mapping.default;
}

function loadTeamMetadata() {
  const dataPath = path.join(API_DIR, 'src', 'data', 'equipos.json');
  const rows = JSON.parse(readUtf8(dataPath));
  const metadata = new Map();

  for (const row of rows) {
    const category = row.categoria || 'Primera';
    const key = `${category}|${canonicalTeamName(row.nombre)}`;
    metadata.set(key, {
      nombre: canonicalTeamName(row.nombre),
      estadio: row.estadio || 'Estadio Local',
      escudo: row.escudo || null,
      categoria: category,
    });
  }

  for (const [category, teams] of Object.entries(FALLBACK_TEAMS)) {
    for (const [name, values] of Object.entries(teams)) {
      const key = `${category}|${name}`;
      if (!metadata.has(key)) {
        metadata.set(key, { nombre: name, categoria: category, ...values });
      }
    }
  }

  return metadata;
}

function collectLegacyTeams(teamMetadata, matches) {
  const needed = new Map();

  for (const match of matches) {
    for (const side of ['localName', 'visitanteName']) {
      const key = `${match.categoria}|${match[side]}`;
      if (!needed.has(key)) {
        const meta = teamMetadata.get(key) || {
          nombre: match[side],
          categoria: match.categoria,
          estadio: 'Estadio Local',
          escudo: null,
        };
        needed.set(key, meta);
      }
    }
  }

  return [...needed.values()].filter((team) => team.nombre === 'Villa del Parque');
}

function buildPlayers() {
  const teams = extractConstArray(path.join(ROOT, 'scripts', 'import-jugadores-direct.js'), 'teams');
  const players = [];

  for (const team of teams) {
    const teamName = canonicalTeamName(team.name);
    for (const player of team.players) {
      players.push({
        nombre: player,
        equipo: teamName,
        categoria: 'Primera',
        posicion: 'PEND',
      });
    }
  }

  return players;
}

function buildMatches() {
  const all = [];

  const primeraPlayed = extractConstArray(path.join(ROOT, 'scripts', 'load-fechas-jugadas.js'), 'partidos');
  for (const item of primeraPlayed) {
    all.push({
      torneo: 'Apertura',
      categoria: 'Primera',
      jornada: item.jornada,
      fecha: fechaByJornada(item.jornada, { 1: '2026-03-09', 2: '2026-03-16', 3: '2026-03-23', default: '2026-03-23' }),
      hora: '15:00:00',
      localName: canonicalTeamName(item.local),
      visitanteName: canonicalTeamName(item.visitante),
      estado: 'jugado',
      golesLocal: item.gl,
      golesVisitante: item.gv,
    });
  }

  const primeraF4 = extractConstArray(path.join(ROOT, 'scripts', 'load-apertura-primera-f4.js'), 'partidos');
  for (const item of primeraF4) {
    all.push({
      torneo: 'Apertura',
      categoria: 'Primera',
      jornada: 4,
      fecha: '2026-03-30',
      hora: '15:00:00',
      localName: canonicalTeamName(item.local),
      visitanteName: canonicalTeamName(item.visitante),
      estado: 'pendiente',
      golesLocal: 0,
      golesVisitante: 0,
    });
  }

  const terceraF23 = extractConstArray(path.join(ROOT, 'scripts', 'load-apertura-tercera-f2-f3.js'), 'partidos');
  for (const item of terceraF23) {
    all.push({
      torneo: 'Apertura',
      categoria: 'Tercera',
      jornada: item.jornada,
      fecha: item.jornada === 2 ? '2026-03-16' : '2026-03-23',
      hora: '15:00:00',
      localName: canonicalTeamName(item.local),
      visitanteName: canonicalTeamName(item.visitante),
      estado: 'jugado',
      golesLocal: item.gl,
      golesVisitante: item.gv,
    });
  }

  const terceraF4 = extractConstArray(path.join(ROOT, 'scripts', 'load-apertura-tercera-f4.js'), 'partidos');
  for (const item of terceraF4) {
    all.push({
      torneo: 'Apertura',
      categoria: 'Tercera',
      jornada: 4,
      fecha: '2026-03-30',
      hora: '15:00:00',
      localName: canonicalTeamName(item.local),
      visitanteName: canonicalTeamName(item.visitante),
      estado: item.estado || 'pendiente',
      golesLocal: item.gl ?? 0,
      golesVisitante: item.gv ?? 0,
    });
  }

  const deduped = new Map();
  for (const match of all) {
    const key = [match.categoria, match.torneo, match.jornada, match.localName, match.visitanteName].join('|');
    deduped.set(key, match);
  }
  return [...deduped.values()];
}

function buildScorers() {
  const scorers = extractConstArray(path.join(ROOT, 'scripts', 'force-goleadores.js'), 'scorers');
  return scorers.map((row) => ({
    jugadorNombre: row.name,
    equipoNombre: canonicalTeamName(row.team),
    equipoRaw: row.team,
    goles: row.goals,
    aliases: MANUAL_PLAYER_ALIASES[normalize(row.name)] || [],
  }));
}

const ESCUDO_FIXES = [
  ['Cecil A. Roberts',    '/assets-equipos/logos/roberts.png'],
  ['Unión Deportiva',     '/assets-equipos/logos/uniontl.png'],
  ['Unión de Bonifacio', '/assets-equipos/logos/bonifacio.png'],
  ['Deportivo Garré',    '/assets-equipos/logos/garre.png'],
  ['Deportivo Maza',     '/assets-equipos/logos/maza.png'],
  ['Deportivo Argentino','/assets-equipos/logos/deportivoarg.png'],
  ['Atlético Quenumá',   '/assets-equipos/logos/quenuma.png'],
  ['Atlético Argentino', '/assets-equipos/logos/argentinos.png'],
  ['Salazar FC',         '/assets-equipos/logos/salazar.png'],
  ['Jorge Newbery',      '/assets-equipos/logos/newbery.png'],
  ['El Ceibo',           '/assets-equipos/logos/ceibo.png'],
  ['La Gloria',          '/assets-equipos/logos/lagloria.png'],
  ['Deportivo 17',       '/assets-equipos/logos/17.png'],
  ['Juventud Unida',     '/assets-equipos/logos/juventud.png'],
  ['Ferro',              '/assets-equipos/logos/ferro.png'],
];

function renderEscudoFixes() {
  const lines = ['-- Corrección de rutas de escudo (producción usa /logos/, app espera /assets-equipos/logos/)', ''];
  for (const [nombre, escudo] of ESCUDO_FIXES) {
    lines.push(`UPDATE equipos SET escudo = ${sqlString(escudo)} WHERE nombre = ${sqlString(nombre)};`);
  }
  return lines.join('\n') + '\n';
}

function renderMissingTeamsSection(teams) {
  if (!teams.length) {
    return '-- No se detectaron equipos faltantes para rescatar.\n';
  }

  const lines = ['-- Equipos faltantes detectados en scripts legados', ''];
  for (const team of teams) {
    lines.push(
      `INSERT INTO equipos (nombre, estadio, escudo, categoria) VALUES (${sqlString(team.nombre)}, ${sqlString(team.estadio || 'Estadio Local')}, ${sqlString(team.escudo || '')}, ${sqlString(team.categoria)})`,
      'ON DUPLICATE KEY UPDATE',
      '    estadio = VALUES(estadio),',
      '    escudo = VALUES(escudo);',
      ''
    );
  }
  return lines.join('\n') + '\n';
}

function renderPlayersSection(players) {
  const lines = ['-- Jugadores recuperados desde scripts/import-jugadores-direct.js', ''];
  for (const player of players) {
    lines.push(
      `INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)`,
      `SELECT ${sqlString(player.nombre)}, ${sqlString(player.posicion)}, e.id, 0, 0, 0`,
      `FROM equipos e`,
      `WHERE e.nombre = ${sqlString(player.equipo)} AND e.categoria = ${sqlString(player.categoria)}`,
      `ON DUPLICATE KEY UPDATE posicion = VALUES(posicion);`,
      ''
    );
  }
  return lines.join('\n') + '\n';
}

function renderMatchesSection(matches) {
  const lines = ['-- Partidos recuperados desde scripts legados', ''];
  for (const match of matches) {
    lines.push(
      `INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)`,
      `SELECT ${sqlString(match.fecha)}, ${sqlString(match.hora)}, COALESCE(NULLIF(el.estadio, ''), 'Estadio Local'), el.id, ev.id, ${sqlString(match.estado)}, ${sqlNumber(match.golesLocal)}, ${sqlNumber(match.golesVisitante)}, 0, 0, ${sqlString(match.torneo)}, ${sqlString(match.categoria)}, ${sqlNumber(match.jornada)}, NULL`,
      `FROM equipos el JOIN equipos ev ON 1 = 1`,
      `WHERE el.nombre = ${sqlString(match.localName)} AND el.categoria = ${sqlString(match.categoria)}`,
      `  AND ev.nombre = ${sqlString(match.visitanteName)} AND ev.categoria = ${sqlString(match.categoria)}`,
      `ON DUPLICATE KEY UPDATE estado = VALUES(estado), goleslocal = VALUES(goleslocal), golesvisitante = VALUES(golesvisitante), jornada = VALUES(jornada);`,
      ''
    );
  }
  return lines.join('\n') + '\n';
}

function renderGolesSection(scorers) {
  const lines = [
    '-- Goles por jugador: UPDATE directo en jugadores.goles',
    '-- Busca por nombre del goleador O sus aliases conocidos, dentro del equipo correcto',
    '',
  ];

  for (const scorer of scorers) {
    const nameConditions = [sqlString(scorer.jugadorNombre), ...scorer.aliases.map(sqlString)];
    const nameClause = nameConditions.length === 1
      ? `j.nombre = ${nameConditions[0]}`
      : `j.nombre IN (${nameConditions.join(', ')})`;
    lines.push(
      `UPDATE jugadores j JOIN equipos e ON j.equipo = e.id`,
      `SET j.goles = ${sqlNumber(scorer.goles)}`,
      `WHERE e.nombre = ${sqlString(scorer.equipoNombre)} AND e.categoria = 'Primera'`,
      `  AND (${nameClause});`,
      ''
    );
  }

  return lines.join('\n') + '\n';
}

function main() {
  const teamMetadata = loadTeamMetadata();
  const matches = buildMatches();
  const players = buildPlayers();
  const scorers = buildScorers();
  const missingTeams = collectLegacyTeams(teamMetadata, matches);

  const preamble = [
    'SET NAMES utf8mb4;',
    'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";',
    'SET AUTOCOMMIT = 0;',
    'START TRANSACTION;',
    'SET time_zone = "+00:00";',
    '',
    '-- ============================================================',
    '-- RESCATE DE DATOS PARA DB DE PRODUCCIÓN juegpdee_base',
    '-- Generado automáticamente por scripts/generate-rescue-sql.js',
    '-- Compatible con schema MySQL 5.6 exportado el 09/04/2026',
    '-- ============================================================',
    '',
    '-- Eliminar datos de ejemplo iniciales',
    'DELETE FROM jugadores WHERE nombre IN (\'Juan Pérez\', \'Luis Gómez\', \'Pablo Campos\');',
    'DELETE FROM partidos WHERE id IN (1, 2);',
    '',
  ].join('\n');

  const sections = [
    preamble,
    renderEscudoFixes().trimEnd(),
    '',
    renderMissingTeamsSection(missingTeams).trimEnd(),
    '',
    renderPlayersSection(players).trimEnd(),
    '',
    renderMatchesSection(matches).trimEnd(),
    '',
    renderGolesSection(scorers).trimEnd(),
    '',
    'COMMIT;',
    '',
  ];

  fs.mkdirSync(OUTPUT_DIR, { recursive: true });
  fs.writeFileSync(OUTPUT_FILE, sections.join('\n'), 'utf8');

  console.log(`SQL generado en: ${OUTPUT_FILE}`);
  console.log(`Jugadores: ${players.length}`);
  console.log(`Partidos: ${matches.length}`);
  console.log(`Goleadores (UPDATEs): ${scorers.length}`);
  console.log(`Equipos faltantes: ${missingTeams.length}`);
}

main();