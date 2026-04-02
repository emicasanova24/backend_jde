require("dotenv").config();
const express = require("express");
const cors = require("cors");
const path = require("path");

const authRoutes = require("./routes/auth.routes");
const equiposRoutes = require("./routes/equipos.routes");
const jugadoresRoutes = require("./routes/jugadores.routes");
const partidosRoutes = require("./routes/partidos.routes");
const tablasRoutes = require("./routes/tablas.routes");
const eventosRoutes = require("./routes/eventos.routes");
const authMiddleware = require("./middlewares/auth.middleware");

const app = express();

app.use(cors());
app.use(express.json());

// 🔐 Rutas
app.use("/auth", authRoutes);
app.use("/equipos", equiposRoutes);
app.use("/jugadores", jugadoresRoutes);
app.use("/partidos", partidosRoutes);
app.use("/tabla", tablasRoutes);
app.use("/eventos", eventosRoutes);

const seedDB = require("./scripts/seed-db");
const db = require("./db/postgres");

app.post("/setup/seed", authMiddleware, async (req, res) => {
  try {
    await seedDB();
    res.json({ ok: true, message: "Seed realizado" });
  } catch (err) {
    console.error(err);
    res.status(500).json({ ok: false, message: "Error en seed" });
  }
});

app.post("/setup/clear", authMiddleware, async (req, res) => {
  try {
    await db.query("DELETE FROM eventos");
    await db.query("DELETE FROM partidos");
    await db.query("DELETE FROM jugadores");
    await db.query("DELETE FROM equipos");

    // Resetear secuencias para IDs ordenados
    await db.query("ALTER SEQUENCE partidos_id_seq RESTART WITH 1");
    await db.query("ALTER SEQUENCE jugadores_id_seq RESTART WITH 1");
    await db.query("ALTER SEQUENCE equipos_id_seq RESTART WITH 1");

    res.json({ ok: true, message: "Base de datos limpiada" });
  } catch (error) {
    console.error("Error en /setup/clear", error);
    res.status(500).json({ ok: false, message: "Error al limpiar DB" });
  }
});

app.get("/health", (req, res) => {
  res.json({ ok: true });
});

// 🧪 Ruta protegida de prueba
app.get("/test-protegido", authMiddleware, (req, res) => {
  res.json({
    mensaje: "Acceso autorizado",
    usuario: req.user
  });
});

// 🌐 Frontend (hacia carpeta frontend)
app.use(express.static(path.join(__dirname, "..", "frontend")));
// 🖼 Assets estáticos
app.use("/assets", express.static(path.join(__dirname, "..", "assets")));
// 🏟 Logos de equipos
app.use("/logos", express.static(path.join(__dirname, "..", "equipos", "logos")));
// 🤝 Sponsors
app.use("/sponsors", express.static(path.join(__dirname, "..", "sponsors")));

// Raíz: sirve index.html del frontend (express.static ya lo hace)
// No redirect a login — la home es pública

const port = Number(process.env.PORT || 3000);

app.listen(port, () => {
  console.log(`Servidor corriendo en http://localhost:${port}`);
});
