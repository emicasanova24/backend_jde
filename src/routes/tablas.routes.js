const express = require("express");
const router = express.Router();
const db = require("../db/postgres");

router.get("/", async (req, res) => {
  try {
    let query = "SELECT id, nombre, categoria FROM equipos";
    const params = [];
    if (req.query.categoria) {
      query += " WHERE LOWER(categoria) = LOWER($1)";
      params.push(req.query.categoria);
    }
    const { rows: equipos } = await db.query(query, params);
    const tabla = equipos.map((e) => ({
      equipo: e.nombre,
      id: e.id,
      pj: 0,
      pg: 0,
      pe: 0,
      pp: 0,
      gf: 0,
      gc: 0,
      dg: 0,
      puntos: 0,
    }));

    const lookup = Object.fromEntries(tabla.map((t) => [t.id, t]));

    const { rows: partidos } = await db.query(
      "SELECT local, visitante, goleslocal, golesvisitante FROM partidos WHERE estado = 'jugado'"
    );

    partidos.forEach((p) => {
      const local = lookup[p.local];
      const visitante = lookup[p.visitante];
      if (!local || !visitante) return;

      local.pj++;
      visitante.pj++;
      local.gf += Number(p.goleslocal || 0);
      local.gc += Number(p.golesvisitante || 0);
      visitante.gf += Number(p.golesvisitante || 0);
      visitante.gc += Number(p.goleslocal || 0);

      if (p.goleslocal > p.golesvisitante) {
        local.pg++;
        visitante.pp++;
      } else if (p.goleslocal < p.golesvisitante) {
        visitante.pg++;
        local.pp++;
      } else {
        local.pe++;
        visitante.pe++;
      }
    });

    tabla.forEach((e) => {
      e.dg = e.gf - e.gc;
      e.puntos = e.pg * 3 + e.pe;
    });

    tabla.sort((a, b) => b.puntos - a.puntos || b.dg - a.dg || b.gf - a.gf);
    res.json(tabla);
  } catch (error) {
    console.error("Error GET /tabla:", error);
    res.status(500).json({ error: "Error al calcular la tabla" });
  }
});

router.get("/goleadores", async (req, res) => {
  try {
    const params = [];
    let where = "WHERE ev.tipo = 'gol'";
    if (req.query.categoria) {
      params.push(req.query.categoria);
      where += ` AND LOWER(e.categoria) = LOWER($${params.length})`;
    }
    const query = `SELECT j.nombre, COALESCE(e.nombre, '-') AS equipo, COUNT(*) AS goles
       FROM eventos ev
       JOIN jugadores j ON ev.jugador_id = j.id
       LEFT JOIN equipos e ON ev.equipo_id = e.id
       ${where}
       GROUP BY j.id, j.nombre, e.nombre
       ORDER BY goles DESC`;
    const { rows } = await db.query(query, params);
    res.json(rows);
  } catch (error) {
    console.error("Error GET /tabla/goleadores:", error);
    res.status(500).json({ error: "Error al obtener goleadores" });
  }
});

router.get("/tarjetas", async (req, res) => {
  try {
    const params = [];
    let where = "";
    if (req.query.categoria) {
      params.push(req.query.categoria);
      where = ` AND LOWER(e.categoria) = LOWER($1)`;
    }
    const { rows: amarillas } = await db.query(
      `SELECT j.nombre, COALESCE(e.nombre, '-') AS equipo, COUNT(*) AS amarillas
       FROM eventos ev
       JOIN jugadores j ON ev.jugador_id = j.id
       LEFT JOIN equipos e ON ev.equipo_id = e.id
       WHERE ev.tipo = 'amarilla'${where}
       GROUP BY j.id, j.nombre, e.nombre
       ORDER BY amarillas DESC`,
      params
    );
    const { rows: rojas } = await db.query(
      `SELECT j.nombre, COALESCE(e.nombre, '-') AS equipo, COUNT(*) AS rojas
       FROM eventos ev
       JOIN jugadores j ON ev.jugador_id = j.id
       LEFT JOIN equipos e ON ev.equipo_id = e.id
       WHERE ev.tipo = 'roja'${where}
       GROUP BY j.id, j.nombre, e.nombre
       ORDER BY rojas DESC`,
      params
    );
    res.json({ amarillas, rojas });
  } catch (error) {
    console.error("Error GET /tabla/tarjetas:", error);
    res.status(500).json({ error: "Error al obtener tarjetas" });
  }
});

module.exports = router;
