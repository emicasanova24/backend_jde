const express = require("express");
const router = express.Router();

const jwt = require("jsonwebtoken");

router.post("/login", (req, res) => {
  const { email, password } = req.body;
  if (!email || !password) return res.status(400).json({ success: false, message: "Email y password requeridos" });

  const adminEmail = process.env.ADMIN_EMAIL;
  const adminPassword = process.env.ADMIN_PASSWORD;
  const jwtSecret = process.env.JWT_SECRET;

  if (!adminEmail || !adminPassword || !jwtSecret) {
    console.error("Faltan variables de entorno: ADMIN_EMAIL, ADMIN_PASSWORD o JWT_SECRET");
    return res.status(500).json({ success: false, message: "Error de configuración del servidor" });
  }

  if (email === adminEmail && password === adminPassword) {
    const token = jwt.sign({ email, role: "admin" }, jwtSecret, { expiresIn: "8h" });
    return res.json({ success: true, token });
  }
  res.status(401).json({ success: false, message: "Credenciales inválidas" });
});

module.exports = router;
