const express = require("express");
const router = express.Router();

const jwt = require("jsonwebtoken");
const bcrypt = require("bcrypt");

const BCRYPT_ROUNDS = 12;

const db = require("../db/postgres");
const authMiddleware = require("../middlewares/auth.middleware");

const ADMIN_ACCOUNT_ID = 1;

const createAuthToken = (email, jwtSecret) => jwt.sign({ email, role: "admin" }, jwtSecret, { expiresIn: "8h" });

const sanitizeAccount = (row) => ({
  id: row.id,
  fullName: row.full_name,
  email: row.email,
  phone: row.phone,
  bio: row.bio,
  roleLabel: row.role_label,
  adminCode: row.admin_code,
  avatarUrl: row.avatar_url,
  memberSince: row.member_since,
  verified: row.verified,
  preferences: {
    emailNotifications: row.email_notifications,
    liveNotifications: row.live_notifications,
    developerMode: row.developer_mode,
  },
});

const ensureAdminAccountTable = async () => {
  await db.query(`
    CREATE TABLE IF NOT EXISTS admin_account (
      id SMALLINT PRIMARY KEY CHECK (id = 1),
      full_name TEXT NOT NULL,
      email TEXT NOT NULL UNIQUE,
      password TEXT NOT NULL,
      phone TEXT NOT NULL DEFAULT '',
      bio TEXT NOT NULL DEFAULT '',
      role_label TEXT NOT NULL DEFAULT 'Administrador Principal',
      admin_code TEXT NOT NULL DEFAULT 'ADMIN-001',
      avatar_url TEXT NOT NULL DEFAULT '/assets/logo.png',
      email_notifications BOOLEAN NOT NULL DEFAULT TRUE,
      live_notifications BOOLEAN NOT NULL DEFAULT TRUE,
      developer_mode BOOLEAN NOT NULL DEFAULT FALSE,
      member_since TEXT NOT NULL DEFAULT '2023',
      verified BOOLEAN NOT NULL DEFAULT TRUE,
      updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
    );
  `);
};

const ensureAdminAccount = async () => {
  await ensureAdminAccountTable();

  const adminEmail = process.env.ADMIN_EMAIL;
  const adminPassword = process.env.ADMIN_PASSWORD;

  if (!adminEmail || !adminPassword) {
    throw new Error("Faltan variables de entorno: ADMIN_EMAIL o ADMIN_PASSWORD");
  }

  const { rows } = await db.query("SELECT * FROM admin_account WHERE id = $1", [ADMIN_ACCOUNT_ID]);
  if (rows.length) {
    const row = rows[0];
    // Auto-migrate plaintext passwords to bcrypt
    if (row.password && !row.password.startsWith("$2b$")) {
      const hashed = await bcrypt.hash(row.password, BCRYPT_ROUNDS);
      await db.query("UPDATE admin_account SET password = $1 WHERE id = $2", [hashed, ADMIN_ACCOUNT_ID]);
      row.password = hashed;
    }
    return row;
  }

  const hashedPassword = await bcrypt.hash(adminPassword, BCRYPT_ROUNDS);
  const inserted = await db.query(
    `INSERT INTO admin_account (id, full_name, email, password, phone, bio, role_label, admin_code, avatar_url, email_notifications, live_notifications, developer_mode, member_since, verified)
     VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, TRUE, TRUE, FALSE, '2023', TRUE)
     RETURNING *`,
    [
      ADMIN_ACCOUNT_ID,
      "Administrador Juego de Equipo",
      adminEmail,
      hashedPassword,
      "",
      "Gestión integral de la plataforma administrativa y supervisión de ligas regionales.",
      "Administrador Principal",
      "ADMIN-001",
      "/assets/logo.png",
    ]
  );

  return inserted.rows[0];
};

router.post("/login", async (req, res) => {
  const { email, password } = req.body;
  if (!email || !password) return res.status(400).json({ success: false, message: "Email y password requeridos" });

  const jwtSecret = process.env.JWT_SECRET;

  if (!jwtSecret) {
    console.error("Falta variable de entorno: JWT_SECRET");
    return res.status(500).json({ success: false, message: "Error de configuración del servidor" });
  }

  try {
    const account = await ensureAdminAccount();

    if (email !== account.email) {
      return res.status(401).json({ success: false, message: "Credenciales inválidas" });
    }

    const passwordMatch = await bcrypt.compare(password, account.password);
    if (passwordMatch) {
      const token = createAuthToken(account.email, jwtSecret);
      return res.json({ success: true, token, account: sanitizeAccount(account) });
    }

    return res.status(401).json({ success: false, message: "Credenciales inválidas" });
  } catch (error) {
    console.error("Error en /auth/login", error);
    return res.status(500).json({ success: false, message: "Error al iniciar sesión" });
  }
});

router.get("/account", authMiddleware, async (req, res) => {
  try {
    const account = await ensureAdminAccount();
    return res.json({ success: true, account: sanitizeAccount(account) });
  } catch (error) {
    console.error("Error en GET /auth/account", error);
    return res.status(500).json({ success: false, message: "No se pudo cargar la cuenta" });
  }
});

router.put("/account", authMiddleware, async (req, res) => {
  const { fullName, email, phone, bio, avatarUrl, preferences = {} } = req.body;

  if (!fullName || !email) {
    return res.status(400).json({ success: false, message: "Nombre y email son obligatorios" });
  }

  try {
    await ensureAdminAccount();

    const { rows } = await db.query(
      `UPDATE admin_account
       SET full_name = $1,
           email = $2,
           phone = $3,
           bio = $4,
           avatar_url = $5,
           email_notifications = $6,
           live_notifications = $7,
           developer_mode = $8,
           updated_at = NOW()
       WHERE id = $9
       RETURNING *`,
      [
        fullName.trim(),
        email.trim(),
        (phone || "").trim(),
        (bio || "").trim(),
        (avatarUrl || "/assets/logo.png").trim() || "/assets/logo.png",
        Boolean(preferences.emailNotifications),
        Boolean(preferences.liveNotifications),
        Boolean(preferences.developerMode),
        ADMIN_ACCOUNT_ID,
      ]
    );

    const account = rows[0];
    const token = createAuthToken(account.email, process.env.JWT_SECRET);
    return res.json({ success: true, message: "Cuenta actualizada", token, account: sanitizeAccount(account) });
  } catch (error) {
    console.error("Error en PUT /auth/account", error);
    return res.status(500).json({ success: false, message: "No se pudo guardar la cuenta" });
  }
});

router.post("/account/password", authMiddleware, async (req, res) => {
  const { currentPassword, newPassword, confirmPassword } = req.body;

  if (!currentPassword || !newPassword || !confirmPassword) {
    return res.status(400).json({ success: false, message: "Completá todos los campos de contraseña" });
  }

  if (newPassword.length < 8) {
    return res.status(400).json({ success: false, message: "La nueva contraseña debe tener al menos 8 caracteres" });
  }

  if (newPassword !== confirmPassword) {
    return res.status(400).json({ success: false, message: "La confirmación no coincide" });
  }

  try {
    const account = await ensureAdminAccount();

    const currentMatch = await bcrypt.compare(currentPassword, account.password);
    if (!currentMatch) {
      return res.status(401).json({ success: false, message: "La contraseña actual es incorrecta" });
    }

    const hashedNew = await bcrypt.hash(newPassword, BCRYPT_ROUNDS);
    await db.query(
      `UPDATE admin_account
       SET password = $1,
           updated_at = NOW()
       WHERE id = $2`,
      [hashedNew, ADMIN_ACCOUNT_ID]
    );

    return res.json({ success: true, message: "Contraseña actualizada" });
  } catch (error) {
    console.error("Error en POST /auth/account/password", error);
    return res.status(500).json({ success: false, message: "No se pudo actualizar la contraseña" });
  }
});

module.exports = router;
