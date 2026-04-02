const express = require("express");
const router = express.Router();
const db = require("../db/postgres");
const authMiddleware = require("../middlewares/auth.middleware");

router.get("/", async (req, res) => {
  try {
    const params = [];
    let query = "SELECT id, nombre, estadio, escudo, categoria FROM equipos";
    if (req.query.categoria) {
      params.push(req.query.categoria);
      query += " WHERE LOWER(categoria) = LOWER($1)";
    }
    query += " ORDER BY categoria, nombre";

    const { rows } = await db.query(query, params);
    res.json(rows);
  } catch (error) {
    console.error("Error GET /equipos:", error);
    res.status(500).json({ error: "Error al listar equipos" });
  }
});

router.post("/", authMiddleware, async (req, res) => {
  const { nombre, estadio, escudo, categoria } = req.body;
  if (!nombre || !estadio || !categoria) return res.status(400).json({ error: "Falta nombre, estadio o categoría" });
  try {
    const { rows } = await db.query(
      "INSERT INTO equipos (nombre, estadio, escudo, categoria) VALUES ($1, $2, $3, $4) RETURNING id, nombre, estadio, escudo, categoria",
      [nombre, estadio, escudo || "", categoria]
    );
    res.status(201).json(rows[0]);
  } catch (error) {
    console.error("Error POST /equipos:", error);
    res.status(500).json({ error: "Error al crear equipo" });
  }
});

router.put("/:id", authMiddleware, async (req, res) => {
  const id = Number(req.params.id);
  const { nombre, estadio, escudo, categoria } = req.body;
  try {
    const { rows } = await db.query(
      "UPDATE equipos SET nombre = COALESCE($1, nombre), estadio = COALESCE($2, estadio), escudo = COALESCE($3, escudo), categoria = COALESCE($4, categoria) WHERE id = $5 RETURNING id, nombre, estadio, escudo, categoria",
      [nombre, estadio, escudo, categoria, id]
    );
    if (!rows.length) return res.status(404).json({ error: "Equipo no encontrado" });
    res.json(rows[0]);
  } catch (error) {
    console.error("Error PUT /equipos/:id:", error);
    res.status(500).json({ error: "Error al actualizar equipo" });
  }
});

router.delete("/:id", authMiddleware, async (req, res) => {
  const id = Number(req.params.id);
  try {
    const { rowCount } = await db.query("DELETE FROM equipos WHERE id = $1", [id]);
    if (!rowCount) return res.status(404).json({ error: "Equipo no encontrado" });
    res.json({ ok: true });
  } catch (error) {
    console.error("Error DELETE /equipos/:id:", error);
    res.status(500).json({ error: "Error al eliminar equipo" });
  }
});

// Sincroniza equipos: conserva solamente los enviados en payload
router.post("/sync", authMiddleware, async (req, res) => {
  const input = req.body.equipos;
  if (!Array.isArray(input)) {
    return res.status(400).json({ error: "Debe enviar { equipos: [...] }" });
  }
  const names = [...new Set(input.map((e) => (e.nombre || "").trim()).filter(Boolean))];

  try {
    if (names.length) {
      const params = names.map((_, idx) => `$${idx + 1}`).join(", ");
      await db.query(`DELETE FROM equipos WHERE nombre NOT IN (${params})`, names);
    } else {
      await db.query("DELETE FROM equipos");
    }

    for (const equipo of input) {
      const nombre = (equipo.nombre || "").trim();
      if (!nombre) continue;
      await db.query(
        `INSERT INTO equipos (nombre, estadio, escudo, categoria)
          VALUES ($1, $2, $3, $4)
          ON CONFLICT (nombre, categoria)
          DO UPDATE SET estadio = EXCLUDED.estadio, escudo = EXCLUDED.escudo`,
        [nombre, equipo.estadio || "", equipo.escudo || "", equipo.categoria || "Primera"]
      );
    }

    const { rows } = await db.query("SELECT id, nombre, estadio, escudo, categoria FROM equipos ORDER BY categoria, nombre");
    res.json({ ok: true, equipos: rows });
  } catch (error) {
    console.error("Error POST /equipos/sync:", error);
    res.status(500).json({ error: "Error al sincronizar equipos" });
  }
});

module.exports = router;
