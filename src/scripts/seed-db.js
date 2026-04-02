require("dotenv").config();
const db = require("../db/postgres");
const store = require("../data/store");

async function setup() {
  try {
    await db.query(`
      CREATE TABLE IF NOT EXISTS equipos (
        id SERIAL PRIMARY KEY,
        nombre TEXT UNIQUE NOT NULL,
        estadio TEXT NOT NULL,
        escudo TEXT
      );
    `);

    await db.query(`
      CREATE TABLE IF NOT EXISTS jugadores (
        id SERIAL PRIMARY KEY,
        nombre TEXT NOT NULL,
        posicion TEXT NOT NULL,
        equipo INTEGER REFERENCES equipos(id) ON DELETE SET NULL,
        goles INTEGER DEFAULT 0,
        amarillas INTEGER DEFAULT 0,
        rojas INTEGER DEFAULT 0
      );
    `);

    await db.query(`
      CREATE TABLE IF NOT EXISTS partidos (
        id SERIAL PRIMARY KEY,
        fecha DATE NOT NULL,
        hora TIME NOT NULL,
        estadio TEXT NOT NULL,
        local INTEGER REFERENCES equipos(id) ON DELETE CASCADE,
        visitante INTEGER REFERENCES equipos(id) ON DELETE CASCADE,
        estado TEXT DEFAULT 'pendiente',
        goleslocal INTEGER DEFAULT 0,
        golesvisitante INTEGER DEFAULT 0,
        amarillas INTEGER DEFAULT 0,
        rojas INTEGER DEFAULT 0
      );
    `);

    console.log('Tablas creadas o verificadas.');

    await db.query(`ALTER TABLE equipos ADD COLUMN IF NOT EXISTS categoria TEXT NOT NULL DEFAULT 'Primera'`);
    await db.query(`ALTER TABLE equipos ADD COLUMN IF NOT EXISTS estadio TEXT NOT NULL DEFAULT ''`);
    await db.query(`ALTER TABLE equipos ADD COLUMN IF NOT EXISTS escudo TEXT DEFAULT ''`);
    // Si existe índice legado solo por nombre, elimínalo para usar la nueva unicidad sobre nombre+categoria.
    await db.query(`DROP INDEX IF EXISTS equipos_nombre_unique`);
    await db.query(`CREATE UNIQUE INDEX IF NOT EXISTS equipos_nombre_categoria_unique ON equipos(nombre, categoria)`);

    for (const equipo of store.equipos) {
      await db.query(
        `INSERT INTO equipos (nombre, estadio, escudo, categoria) VALUES ($1, $2, $3, $4)
          ON CONFLICT (nombre, categoria) DO UPDATE SET estadio = EXCLUDED.estadio, escudo = EXCLUDED.escudo;`,
        [equipo.nombre, equipo.estadio, equipo.escudo, equipo.categoria || 'Primera']
      );
    }
    console.log('Equipos cargados.');

    await db.query(`ALTER TABLE jugadores ADD COLUMN IF NOT EXISTS equipo INTEGER REFERENCES equipos(id) ON DELETE SET NULL`);
    await db.query(`ALTER TABLE jugadores ADD COLUMN IF NOT EXISTS goles INTEGER DEFAULT 0`);
    await db.query(`ALTER TABLE jugadores ADD COLUMN IF NOT EXISTS amarillas INTEGER DEFAULT 0`);
    await db.query(`ALTER TABLE jugadores ADD COLUMN IF NOT EXISTS rojas INTEGER DEFAULT 0`);
    await db.query(`CREATE UNIQUE INDEX IF NOT EXISTS jugadores_nombre_equipo_unique ON jugadores(nombre, equipo)`);

    for (const jugador of store.jugadores) {
      await db.query(
        `INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas) VALUES ($1, $2, $3, $4, $5, $6)
          ON CONFLICT (nombre, equipo) DO UPDATE SET posicion = EXCLUDED.posicion, goles=EXCLUDED.goles, amarillas=EXCLUDED.amarillas, rojas=EXCLUDED.rojas;`,
        [jugador.nombre, jugador.posicion, jugador.equipo, jugador.goles, jugador.amarillas, jugador.rojas]
      );
    }
    console.log('Jugadores cargados.');

    await db.query(`ALTER TABLE partidos ADD COLUMN IF NOT EXISTS fecha DATE NOT NULL DEFAULT CURRENT_DATE`);
    await db.query(`ALTER TABLE partidos ADD COLUMN IF NOT EXISTS hora TIME NOT NULL DEFAULT '00:00'`);
    await db.query(`ALTER TABLE partidos ADD COLUMN IF NOT EXISTS estadio TEXT NOT NULL DEFAULT ''`);
    await db.query(`ALTER TABLE partidos ADD COLUMN IF NOT EXISTS local INTEGER REFERENCES equipos(id) ON DELETE CASCADE`);
    await db.query(`ALTER TABLE partidos ADD COLUMN IF NOT EXISTS visitante INTEGER REFERENCES equipos(id) ON DELETE CASCADE`);
    await db.query(`ALTER TABLE partidos ADD COLUMN IF NOT EXISTS estado TEXT DEFAULT 'pendiente'`);
    await db.query(`ALTER TABLE partidos ADD COLUMN IF NOT EXISTS goleslocal INTEGER DEFAULT 0`);
    await db.query(`ALTER TABLE partidos ADD COLUMN IF NOT EXISTS golesvisitante INTEGER DEFAULT 0`);
    await db.query(`ALTER TABLE partidos ADD COLUMN IF NOT EXISTS amarillas INTEGER DEFAULT 0`);
    await db.query(`ALTER TABLE partidos ADD COLUMN IF NOT EXISTS rojas INTEGER DEFAULT 0`);
    await db.query(`ALTER TABLE partidos ADD COLUMN IF NOT EXISTS torneo TEXT DEFAULT 'Apertura'`);
    await db.query(`ALTER TABLE partidos ADD COLUMN IF NOT EXISTS categoria TEXT DEFAULT 'Primera'`);

    await db.query(`CREATE UNIQUE INDEX IF NOT EXISTS partidos_unico ON partidos(fecha, hora, local, visitante, torneo)`);

    for (const partido of store.partidos) {
      await db.query(
        `INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria)
          VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12)
          ON CONFLICT (fecha, hora, local, visitante, torneo) DO UPDATE SET estadio=EXCLUDED.estadio, estado=EXCLUDED.estado, goleslocal=EXCLUDED.goleslocal, golesvisitante=EXCLUDED.golesvisitante, amarillas=EXCLUDED.amarillas, rojas=EXCLUDED.rojas, categoria=EXCLUDED.categoria;`,
        [partido.fecha, partido.hora, partido.estadio, partido.localId, partido.visitanteId, partido.estado, partido.golesLocal, partido.golesVisitante, partido.amarillas, partido.rojas, partido.torneo || 'Apertura', partido.categoria || 'Primera']
      );
    }
    console.log('Partidos cargados.');

    // Agregar columna jornada a partidos
    await db.query(`ALTER TABLE partidos ADD COLUMN IF NOT EXISTS jornada INTEGER`);
    console.log('Columna jornada agregada a partidos.');

    // Crear tabla eventos
    await db.query(`
      CREATE TABLE IF NOT EXISTS eventos (
        id SERIAL PRIMARY KEY,
        partido_id INTEGER REFERENCES partidos(id) ON DELETE CASCADE,
        jugador_id INTEGER REFERENCES jugadores(id) ON DELETE SET NULL,
        equipo_id INTEGER REFERENCES equipos(id) ON DELETE SET NULL,
        tipo TEXT NOT NULL CHECK (tipo IN ('gol', 'amarilla', 'roja', 'gol_en_contra')),
        minuto INTEGER
      );
    `);
    console.log('Tabla eventos creada o verificada.');

    console.log('Seed finalizado con éxito.');
  } catch (error) {
    console.error('Error en seed:', error);
    throw error;
  }
}

if (require.main === module) {
  setup()
    .catch((e) => {
      console.error('Error en seed:', e);
      process.exit(1);
    })
    .finally(() => {
      db.pool.end();
    });
}

module.exports = setup;