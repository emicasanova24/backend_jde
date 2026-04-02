const express = require("express");
const router = express.Router();
const db = require("../db/postgres");
const authMiddleware = require("../middlewares/auth.middleware");

// GET /eventos?partido_id=X
router.get("/", async (req, res) => {
  try {
    const params = [];
    let where = "";
    if (req.query.partido_id) {
      params.push(Number(req.query.partido_id));
      where = " WHERE ev.partido_id = $1";
    }
    const { rows } = await db.query(
      `SELECT ev.id, ev.partido_id, ev.jugador_id, ev.equipo_id, ev.tipo, ev.minuto,
              j.nombre AS jugador_nombre, e.nombre AS equipo_nombre
       FROM eventos ev
       LEFT JOIN jugadores j ON ev.jugador_id = j.id
       LEFT JOIN equipos e ON ev.equipo_id = e.id${where}
       ORDER BY ev.minuto NULLS LAST, ev.id`,
      params
    );
    res.json(rows);
  } catch (error) {
    console.error("Error GET /eventos:", error);
    res.status(500).json({ error: "Error al listar eventos" });
  }
});

// POST /eventos/bulk — crear múltiples eventos de golpe
router.post("/bulk", authMiddleware, async (req, res) => {
  const { eventos } = req.body;
  if (!Array.isArray(eventos) || !eventos.length) {
    return res.status(400).json({ error: "Se requiere un array de eventos" });
  }

  const client = await db.pool.connect();
  try {
    await client.query("BEGIN");
    const created = [];
    for (const ev of eventos) {
      const { partido_id, jugador_id, equipo_id, tipo, minuto } = ev;
      if (!partido_id || !tipo) {
        await client.query("ROLLBACK");
        return res.status(400).json({ error: "Cada evento requiere partido_id y tipo" });
      }
      const { rows } = await client.query(
        `INSERT INTO eventos (partido_id, jugador_id, equipo_id, tipo, minuto)
         VALUES ($1, $2, $3, $4, $5) RETURNING *`,
        [partido_id, jugador_id || null, equipo_id || null, tipo, minuto || null]
      );
      created.push(rows[0]);
    }
    await client.query("COMMIT");
    res.status(201).json(created);
  } catch (error) {
    await client.query("ROLLBACK");
    console.error("Error POST /eventos/bulk:", error);
    res.status(500).json({ error: "Error al crear eventos" });
  } finally {
    client.release();
  }
});

// POST /eventos — crear un evento individual
router.post("/", authMiddleware, async (req, res) => {
  const { partido_id, jugador_id, equipo_id, tipo, minuto } = req.body;
  if (!partido_id || !tipo) {
    return res.status(400).json({ error: "partido_id y tipo son requeridos" });
  }
  try {
    const { rows } = await db.query(
      `INSERT INTO eventos (partido_id, jugador_id, equipo_id, tipo, minuto)
       VALUES ($1, $2, $3, $4, $5) RETURNING *`,
      [partido_id, jugador_id || null, equipo_id || null, tipo, minuto || null]
    );
    res.status(201).json(rows[0]);
  } catch (error) {
    console.error("Error POST /eventos:", error);
    res.status(500).json({ error: "Error al crear evento" });
  }
});

// DELETE /eventos/:id
router.delete("/:id", authMiddleware, async (req, res) => {
  const id = Number(req.params.id);
  try {
    const { rowCount } = await db.query("DELETE FROM eventos WHERE id = $1", [id]);
    if (!rowCount) return res.status(404).json({ error: "Evento no encontrado" });
    res.json({ ok: true });
  } catch (error) {
    console.error("Error DELETE /eventos/:id:", error);
    res.status(500).json({ error: "Error al eliminar evento" });
  }
});

// DELETE /eventos/partido/:partidoId — eliminar todos los eventos de un partido
router.delete("/partido/:partidoId", authMiddleware, async (req, res) => {
  const partidoId = Number(req.params.partidoId);
  try {
    const { rowCount } = await db.query("DELETE FROM eventos WHERE partido_id = $1", [partidoId]);
    res.json({ ok: true, deleted: rowCount });
  } catch (error) {
    console.error("Error DELETE /eventos/partido/:partidoId:", error);
    res.status(500).json({ error: "Error al eliminar eventos del partido" });
  }
});

module.exports = router;
