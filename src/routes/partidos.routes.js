const express = require("express");
const router = express.Router();
const db = require("../db/postgres");
const authMiddleware = require("../middlewares/auth.middleware");

// GET /partidos/live — partidos del día con broadcast URL
router.get("/live", async (req, res) => {
  try {
    const { rows } = await db.query(
      `SELECT p.id, p.fecha, p.hora, p.estadio, p.estado, p.broadcast,
        p.goleslocal AS "golesLocal", p.golesvisitante AS "golesVisitante",
        el.nombre AS local, ev.nombre AS visitante,
        el.escudo AS "localEscudo", ev.escudo AS "visitanteEscudo"
      FROM partidos p
      LEFT JOIN equipos el ON p.local = el.id
      LEFT JOIN equipos ev ON p.visitante = ev.id
      WHERE p.fecha = CURRENT_DATE AND p.broadcast IS NOT NULL AND p.broadcast != ''
      ORDER BY p.hora`
    );
    res.json(rows);
  } catch (error) {
    console.error("Error GET /partidos/live:", error);
    res.status(500).json({ error: "Error al obtener partidos en vivo" });
  }
});

// GET /partidos/jornadas?categoria=X&torneo=Y — listar jornadas con estado
router.get("/jornadas", async (req, res) => {
  try {
    const params = [];
    const where = [];
    if (req.query.categoria) {
      params.push(req.query.categoria);
      where.push(`LOWER(categoria) = LOWER($${params.length})`);
    }
    if (req.query.torneo) {
      params.push(req.query.torneo);
      where.push(`LOWER(torneo) = LOWER($${params.length})`);
    }
    where.push("jornada IS NOT NULL");
    let query = `SELECT jornada, COUNT(*) AS total, COUNT(*) FILTER (WHERE estado = 'jugado') AS jugados
      FROM partidos`;
    if (where.length) query += " WHERE " + where.join(" AND ");
    query += " GROUP BY jornada ORDER BY jornada";
    const { rows } = await db.query(query, params);
    res.json(rows);
  } catch (error) {
    console.error("Error GET /partidos/jornadas:", error);
    res.status(500).json({ error: "Error al listar jornadas" });
  }
});

// GET /partidos/h2h?team1=ID&team2=ID&categoria=X — historial head-to-head
router.get("/h2h", async (req, res) => {
  try {
    const t1 = Number(req.query.team1);
    const t2 = Number(req.query.team2);
    if (!t1 || !t2 || t1 === t2) return res.status(400).json({ error: "Se requieren dos equipos distintos (team1, team2)" });

    const params = [t1, t2];
    const where = [`((p.local = $1 AND p.visitante = $2) OR (p.local = $2 AND p.visitante = $1))`, `p.estado = 'jugado'`];
    if (req.query.categoria) {
      params.push(req.query.categoria);
      where.push(`LOWER(p.categoria) = LOWER($${params.length})`);
    }

    const query = `
      SELECT p.id, p.fecha, p.hora, p.estadio, p.jornada, p.torneo, p.categoria,
        p.local AS "localId", p.visitante AS "visitanteId",
        p.goleslocal AS "golesLocal", p.golesvisitante AS "golesVisitante",
        el.nombre AS local, ev.nombre AS visitante,
        el.escudo AS "localEscudo", ev.escudo AS "visitanteEscudo"
      FROM partidos p
      LEFT JOIN equipos el ON p.local = el.id
      LEFT JOIN equipos ev ON p.visitante = ev.id
      WHERE ${where.join(" AND ")}
      ORDER BY p.fecha DESC, p.hora DESC`;

    const { rows: partidos } = await db.query(query, params);

    // Fetch goleadores for all matches in one query
    let goleadoresByPartido = {};
    if (partidos.length) {
      const ids = partidos.map((p) => p.id);
      const { rows: eventos } = await db.query(
        `SELECT e.partido_id, e.minuto, e.equipo_id, j.nombre AS jugador
         FROM eventos e LEFT JOIN jugadores j ON e.jugador_id = j.id
         WHERE e.partido_id = ANY($1) AND e.tipo = 'gol'
         ORDER BY e.minuto NULLS LAST`,
        [ids]
      );
      eventos.forEach((ev) => {
        if (!goleadoresByPartido[ev.partido_id]) goleadoresByPartido[ev.partido_id] = [];
        goleadoresByPartido[ev.partido_id].push(ev);
      });
    }

    // Build summary
    let team1Wins = 0, team2Wins = 0, draws = 0, team1Goals = 0, team2Goals = 0;
    const enriched = partidos.map((p) => {
      const g1 = p.localId === t1 ? p.golesLocal : p.golesVisitante;
      const g2 = p.localId === t1 ? p.golesVisitante : p.golesLocal;
      team1Goals += g1;
      team2Goals += g2;
      if (g1 > g2) team1Wins++;
      else if (g2 > g1) team2Wins++;
      else draws++;
      return { ...p, goleadores: goleadoresByPartido[p.id] || [] };
    });

    res.json({
      resumen: { team1Wins, team2Wins, draws, team1Goals, team2Goals, totalMatches: partidos.length },
      partidos: enriched,
    });
  } catch (error) {
    console.error("Error GET /partidos/h2h:", error);
    res.status(500).json({ error: "Error al obtener historial H2H" });
  }
});

router.get("/", async (req, res) => {
  try {
    const valores = [];
    let where = [];
    let consulta = `SELECT p.id, p.fecha, p.hora, p.estadio, p.local AS "localId", p.visitante AS "visitanteId", p.estado, p.goleslocal AS "golesLocal", p.golesvisitante AS "golesVisitante", p.amarillas, p.rojas, p.torneo, p.categoria, p.jornada, p.broadcast,
      el.nombre AS local, ev.nombre AS visitante,
      el.escudo AS "localEscudo", ev.escudo AS "visitanteEscudo"
      FROM partidos p
      LEFT JOIN equipos el ON p.local = el.id
      LEFT JOIN equipos ev ON p.visitante = ev.id`;
    if (req.query.estado) {
      valores.push(req.query.estado);
      where.push(`LOWER(p.estado) = LOWER($${valores.length})`);
    }
    if (req.query.torneo) {
      valores.push(req.query.torneo);
      where.push(`LOWER(p.torneo) = LOWER($${valores.length})`);
    }
    if (req.query.categoria) {
      valores.push(req.query.categoria);
      where.push(`LOWER(p.categoria) = LOWER($${valores.length})`);
    }
    if (req.query.jornada) {
      valores.push(Number(req.query.jornada));
      where.push(`p.jornada = $${valores.length}`);
    }
    if (where.length) {
      consulta += " WHERE " + where.join(" AND ");
    }
    consulta += " ORDER BY p.jornada, p.fecha, p.hora";

    const { rows } = await db.query(consulta, valores);
    res.json(rows);
  } catch (error) {
    console.error("Error GET /partidos:", error);
    res.status(500).json({ error: "Error al listar partidos" });
  }
});

router.post("/", authMiddleware, async (req, res) => {
  const { fecha, hora, estadio, local, visitante, estado, torneo, categoria, jornada, broadcast } = req.body;
  if (!fecha || !hora || !estadio || !local || !visitante || !torneo || !categoria) {
    return res.status(400).json({ error: "Faltan campos requeridos" });
  }
  const localId = Number(local);
  const visitanteId = Number(visitante);

  try {
    const { rowCount: localExiste } = await db.query("SELECT 1 FROM equipos WHERE id = $1", [localId]);
    const { rowCount: visitanteExiste } = await db.query("SELECT 1 FROM equipos WHERE id = $1", [visitanteId]);
    if (!localExiste || !visitanteExiste) return res.status(400).json({ error: "Equipo local o visitante inválido" });

    const { rows } = await db.query(
      `INSERT INTO partidos (fecha, hora, estadio, local, visitante, estado, goleslocal, golesvisitante, amarillas, rojas, torneo, categoria, jornada, broadcast)
       VALUES ($1, $2, $3, $4, $5, $6, 0, 0, 0, 0, $7, $8, $9, $10)
       RETURNING id, fecha, hora, estadio, local AS "localId", visitante AS "visitanteId", estado, goleslocal AS "golesLocal", golesvisitante AS "golesVisitante", amarillas, rojas, torneo, categoria, jornada, broadcast`,
      [fecha, hora, estadio, localId, visitanteId, estado || "pendiente", torneo, categoria, jornada || null, broadcast || null]
    );
    res.status(201).json(rows[0]);
  } catch (error) {
    console.error("Error POST /partidos:", error);
    res.status(500).json({ error: "Error al crear partido" });
  }
});

router.put("/:id", authMiddleware, async (req, res) => {
  const id = Number(req.params.id);
  const { fecha, hora, estadio, local, visitante, estado, golesLocal, golesVisitante, amarillas, rojas, broadcast } = req.body;

  try {
    let localId = local !== undefined ? Number(local) : undefined;
    let visitanteId = visitante !== undefined ? Number(visitante) : undefined;

    if (localId !== undefined) {
      const { rowCount } = await db.query("SELECT 1 FROM equipos WHERE id = $1", [localId]);
      if (!rowCount) return res.status(400).json({ error: "Equipo local inválido" });
    }
    if (visitanteId !== undefined) {
      const { rowCount } = await db.query("SELECT 1 FROM equipos WHERE id = $1", [visitanteId]);
      if (!rowCount) return res.status(400).json({ error: "Equipo visitante inválido" });
    }

    const { rows } = await db.query(
      `UPDATE partidos
        SET fecha = COALESCE($1, fecha),
            hora = COALESCE($2, hora),
            estadio = COALESCE($3, estadio),
            local = COALESCE($4, local),
            visitante = COALESCE($5, visitante),
            estado = COALESCE($6, estado),
            goleslocal = COALESCE($7, goleslocal),
            golesvisitante = COALESCE($8, golesvisitante),
            amarillas = COALESCE($9, amarillas),
            rojas = COALESCE($10, rojas),
            torneo = COALESCE($11, torneo),
            categoria = COALESCE($12, categoria),
            jornada = COALESCE($13, jornada),
            broadcast = COALESCE($14, broadcast)
        WHERE id = $15
        RETURNING id, fecha, hora, estadio, local AS "localId", visitante AS "visitanteId", estado, goleslocal AS "golesLocal", golesvisitante AS "golesVisitante", amarillas, rojas, torneo, categoria, jornada, broadcast`,
      [fecha, hora, estadio, localId, visitanteId, estado, golesLocal, golesVisitante, amarillas, rojas, req.body.torneo, req.body.categoria, req.body.jornada, broadcast, id]
    );

    if (!rows.length) return res.status(404).json({ error: "Partido no encontrado" });
    res.json(rows[0]);
  } catch (error) {
    console.error("Error PUT /partidos/:id:", error);
    res.status(500).json({ error: "Error al actualizar partido" });
  }
});

router.delete("/:id", authMiddleware, async (req, res) => {
  const id = Number(req.params.id);
  try {
    const { rowCount } = await db.query("DELETE FROM partidos WHERE id = $1", [id]);
    if (!rowCount) return res.status(404).json({ error: "Partido no encontrado" });
    res.json({ ok: true });
  } catch (error) {
    console.error("Error DELETE /partidos/:id:", error);
    res.status(500).json({ error: "Error al eliminar partido" });
  }
});

module.exports = router;
