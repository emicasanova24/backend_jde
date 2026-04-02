const express = require("express");
const router = express.Router();
const db = require("../db/postgres");
const authMiddleware = require("../middlewares/auth.middleware");

router.get("/", async (req, res) => {
  try {
    const params = [];
    let where = "";
    if (req.query.equipo) {
      params.push(Number(req.query.equipo));
      where = ` WHERE j.equipo = $${params.length}`;
    }
    const { rows } = await db.query(
      `SELECT j.id, j.nombre, j.posicion, j.equipo, e.nombre AS "equipoNombre", e.categoria AS "equipoCategoria", j.goles, j.amarillas, j.rojas
       FROM jugadores j
       LEFT JOIN equipos e ON j.equipo = e.id${where}
       ORDER BY j.nombre`,
      params
    );
    res.json(rows);
  } catch (error) {
    console.error("Error GET /jugadores:", error);
    res.status(500).json({ error: "Error al listar jugadores" });
  }
});

router.post("/", authMiddleware, async (req, res) => {
  const { nombre, posicion, equipo } = req.body;
  if (!nombre || !posicion || !equipo) return res.status(400).json({ error: "Falta campo requerido" });
  const equipoId = Number(equipo);
  try {
    const { rowCount: equipoCount } = await db.query("SELECT 1 FROM equipos WHERE id = $1", [equipoId]);
    if (!equipoCount) return res.status(400).json({ error: "Equipo no válido" });

    const { rows } = await db.query(
      `INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas)
       VALUES ($1, $2, $3, 0, 0, 0) RETURNING id, nombre, posicion, equipo, goles, amarillas, rojas`,
      [nombre, posicion, equipoId]
    );
    res.status(201).json(rows[0]);
  } catch (error) {
    console.error("Error POST /jugadores:", error);
    res.status(500).json({ error: "Error al crear jugador" });
  }
});

router.put("/:id", authMiddleware, async (req, res) => {
  const id = Number(req.params.id);
  const { nombre, posicion, equipo } = req.body;
  try {
    let equipoId = equipo !== undefined ? Number(equipo) : undefined;
    if (equipoId !== undefined) {
      const { rowCount } = await db.query("SELECT 1 FROM equipos WHERE id = $1", [equipoId]);
      if (!rowCount) return res.status(400).json({ error: "Equipo no válido" });
    }
    const { rows } = await db.query(
      `UPDATE jugadores
       SET nombre = COALESCE($1, nombre), posicion = COALESCE($2, posicion), equipo = COALESCE($3, equipo)
       WHERE id = $4 RETURNING id, nombre, posicion, equipo, goles, amarillas, rojas`,
      [nombre, posicion, equipoId, id]
    );
    if (!rows.length) return res.status(404).json({ error: "Jugador no encontrado" });
    res.json(rows[0]);
  } catch (error) {
    console.error("Error PUT /jugadores/:id:", error);
    res.status(500).json({ error: "Error al actualizar jugador" });
  }
});

router.delete("/:id", authMiddleware, async (req, res) => {
  const id = Number(req.params.id);
  try {
    const { rowCount } = await db.query("DELETE FROM jugadores WHERE id = $1", [id]);
    if (!rowCount) return res.status(404).json({ error: "Jugador no encontrado" });
    res.json({ ok: true });
  } catch (error) {
    console.error("Error DELETE /jugadores/:id:", error);
    res.status(500).json({ error: "Error al eliminar jugador" });
  }
});

router.delete("/", authMiddleware, async (req, res) => {
  try {
    await db.query("DELETE FROM jugadores");
    res.json({ ok: true, message: "Todos los jugadores eliminados" });
  } catch (error) {
    console.error("Error DELETE /jugadores:", error);
    res.status(500).json({ error: "Error al eliminar todos los jugadores" });
  }
});

module.exports = router;