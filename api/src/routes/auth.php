<?php
namespace App\Routes;

use App\Router;
use App\Database;
use App\Jwt;
use App\Request;
use App\Response;

use function App\Middleware\authMiddleware;

require_once __DIR__ . '/../middleware/auth.php';

function registerAuthRoutes(Router $router): void {
    $router->group('/auth', function (Router $r) {

        $r->post('/login', function (Request $req, Response $res) {
            $email = $req->body['email'] ?? '';
            $password = $req->body['password'] ?? '';
            if (!$email || !$password) {
                return $res->status(400)->json(['success' => false, 'message' => 'Email y password requeridos']);
            }

            $config = require __DIR__ . '/../config.php';
            $jwtSecret = $config['jwt_secret'];
            $adminEmail = $config['admin_email'];
            $adminPassword = $config['admin_password'];
            if (!$jwtSecret) {
                return $res->status(500)->json(['success' => false, 'message' => 'Error de configuración del servidor']);
            }

            try {
                ensureAdminAccount();
                $account = fetchAdminAccountByEmail($email);

                if (!$account) {
                    return $res->status(401)->json(['success' => false, 'message' => 'Credenciales inválidas']);
                }

                if (password_verify($password, $account['password'])) {
                    $token = createAuthToken($account['email'], $jwtSecret, normalizeRoleFromLabel((string) ($account['role_label'] ?? 'Editor')));
                    return $res->json(['success' => true, 'token' => $token, 'account' => sanitizeAccount($account)]);
                }

                // Fallback de recuperación: permite re-sincronizar la clave bootstrap del .env solo para la cuenta principal.
                if ((int) ($account['id'] ?? 0) === 1 && $adminEmail && $adminPassword && $email === $adminEmail && $password === $adminPassword) {
                    $hashedPassword = password_hash($adminPassword, PASSWORD_BCRYPT, ['cost' => 12]);
                    Database::execute(
                        "UPDATE admin_account SET password = ?, updated_at = NOW() WHERE id = ?",
                        [$hashedPassword, $account['id']]
                    );
                    $account['password'] = $hashedPassword;
                    $token = createAuthToken($account['email'], $jwtSecret, normalizeRoleFromLabel((string) ($account['role_label'] ?? 'Editor')));
                    return $res->json(['success' => true, 'token' => $token, 'account' => sanitizeAccount($account)]);
                }

                return $res->status(401)->json(['success' => false, 'message' => 'Credenciales inválidas']);
            } catch (\Exception $e) {
                error_log('Error en /auth/login: ' . $e->getMessage());
                return $res->status(500)->json([
                    'success' => false,
                    'message' => 'Error al iniciar sesión',
                    'debug' => $e->getMessage(),
                    'trace' => $e->getFile() . ':' . $e->getLine(),
                ]);
            }
        });

        $r->get('/account', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            try {
                $account = fetchAuthenticatedAdminAccount($req);
                return $res->json(['success' => true, 'account' => sanitizeAccount($account)]);
            } catch (\Exception $e) {
                error_log('Error en GET /auth/account: ' . $e->getMessage());
                return $res->status(500)->json(['success' => false, 'message' => 'No se pudo cargar la cuenta']);
            }
        });

        $r->put('/account', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $fullName = $req->body['fullName'] ?? '';
            $email = $req->body['email'] ?? '';
            $phone = $req->body['phone'] ?? '';
            $bio = $req->body['bio'] ?? '';
            $avatarUrl = $req->body['avatarUrl'] ?? '/assets/logo.png';
            $preferences = $req->body['preferences'] ?? [];

            if (!$fullName || !$email) {
                return $res->status(400)->json(['success' => false, 'message' => 'Nombre y email son obligatorios']);
            }

            try {
                $currentAccount = fetchAuthenticatedAdminAccount($req);
                $config = require __DIR__ . '/../config.php';

                Database::execute(
                    "UPDATE admin_account
                     SET full_name = ?, email = ?, phone = ?, bio = ?, avatar_url = ?,
                         email_notifications = ?, live_notifications = ?, developer_mode = ?,
                         updated_at = NOW()
                     WHERE id = ?",
                    [
                        trim($fullName), trim($email), trim($phone), trim($bio),
                        trim($avatarUrl) ?: '/assets/logo.png',
                        !empty($preferences['emailNotifications']), !empty($preferences['liveNotifications']),
                        !empty($preferences['developerMode']),
                        (int) $currentAccount['id'],
                    ]
                );

                $row = fetchAdminAccountById((int) $currentAccount['id']);

                $token = createAuthToken($row['email'], $config['jwt_secret'], normalizeRoleFromLabel((string) ($row['role_label'] ?? 'Editor')));
                return $res->json(['success' => true, 'message' => 'Cuenta actualizada', 'token' => $token, 'account' => sanitizeAccount($row)]);
            } catch (\Exception $e) {
                error_log('Error en PUT /auth/account: ' . $e->getMessage());
                return $res->status(500)->json(['success' => false, 'message' => 'No se pudo guardar la cuenta']);
            }
        });

        $r->post('/account/password', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $currentPassword = $req->body['currentPassword'] ?? '';
            $newPassword = $req->body['newPassword'] ?? '';
            $confirmPassword = $req->body['confirmPassword'] ?? '';

            if (!$currentPassword || !$newPassword || !$confirmPassword) {
                return $res->status(400)->json(['success' => false, 'message' => 'Completá todos los campos de contraseña']);
            }
            if (strlen($newPassword) < 8) {
                return $res->status(400)->json(['success' => false, 'message' => 'La nueva contraseña debe tener al menos 8 caracteres']);
            }
            if ($newPassword !== $confirmPassword) {
                return $res->status(400)->json(['success' => false, 'message' => 'La confirmación no coincide']);
            }

            try {
                $account = fetchAuthenticatedAdminAccount($req);

                if (!password_verify($currentPassword, $account['password'])) {
                    return $res->status(401)->json(['success' => false, 'message' => 'La contraseña actual es incorrecta']);
                }

                $hashed = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
                Database::execute("UPDATE admin_account SET password = ?, updated_at = NOW() WHERE id = ?", [$hashed, (int) $account['id']]);

                return $res->json(['success' => true, 'message' => 'Contraseña actualizada']);
            } catch (\Exception $e) {
                error_log('Error en POST /auth/account/password: ' . $e->getMessage());
                return $res->status(500)->json(['success' => false, 'message' => 'No se pudo actualizar la contraseña']);
            }
        });

        $r->get('/users', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            try {
                $storage = resolveUsersStorage();
                if ($storage === 'users') {
                    ensureUsersTable();
                    $users = Database::fetchAll("SELECT id, full_name, email, role, created_at FROM users ORDER BY created_at DESC");
                    $sanitized = array_map(fn($u) => [
                        'id'        => $u['id'],
                        'fullName'  => $u['full_name'],
                        'email'     => $u['email'],
                        'role'      => $u['role'],
                        'createdAt' => $u['created_at'],
                    ], $users);
                } else {
                    $users = Database::fetchAll(
                        "SELECT id, full_name, email, role_label, updated_at FROM admin_account WHERE id <> 1 ORDER BY id DESC"
                    );
                    $sanitized = array_map(fn($u) => [
                        'id'        => $u['id'],
                        'fullName'  => $u['full_name'],
                        'email'     => $u['email'],
                        'role'      => normalizeRoleFromLabel((string) ($u['role_label'] ?? 'Editor')),
                        'createdAt' => $u['updated_at'],
                    ], $users);
                }
                return $res->json(['success' => true, 'users' => $sanitized]);
            } catch (\Exception $e) {
                error_log('Error en GET /auth/users: ' . $e->getMessage());
                return $res->status(500)->json(['success' => false, 'message' => 'No se pudo obtener los usuarios: ' . $e->getMessage()]);
            }
        });

        $r->post('/users', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $fullName = trim($req->body['fullName'] ?? '');
            $email    = trim($req->body['email'] ?? '');
            $password = $req->body['password'] ?? '';
            $role     = $req->body['role'] ?? 'editor';

            if (!$fullName || !$email || !$password) {
                return $res->status(400)->json(['success' => false, 'message' => 'Nombre, email y contraseña son obligatorios']);
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $res->status(400)->json(['success' => false, 'message' => 'Email inválido']);
            }
            if (strlen($password) < 8) {
                return $res->status(400)->json(['success' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres']);
            }
            if (!in_array($role, ['editor', 'admin'], true)) {
                $role = 'editor';
            }

            try {
                $storage = resolveUsersStorage();

                if ($storage === 'users') {
                    ensureUsersTable();
                    $existing = Database::fetchOne("SELECT id FROM users WHERE email = ?", [$email]);
                    if ($existing) {
                        return $res->status(409)->json(['success' => false, 'message' => 'Ya existe un usuario con ese email']);
                    }
                    $hashed = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                    Database::execute(
                        "INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)",
                        [$fullName, $email, $hashed, $role]
                    );
                    return $res->status(201)->json(['success' => true, 'message' => 'Usuario creado']);
                }

                $existing = Database::fetchOne("SELECT id FROM admin_account WHERE email = ?", [$email]);
                if ($existing) {
                    return $res->status(409)->json(['success' => false, 'message' => 'Ya existe un usuario con ese email']);
                }

                $nextIdRow = Database::fetchOne("SELECT COALESCE(MAX(id), 1) + 1 AS next_id FROM admin_account");
                $nextId = (int) ($nextIdRow['next_id'] ?? 2);
                if ($nextId > 255) {
                    return $res->status(400)->json(['success' => false, 'message' => 'No hay más cupo de usuarios en la tabla actual']);
                }

                $hashed = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                $roleLabel = roleLabelForRole($role);
                $adminCode = 'ADMIN-' . str_pad((string) $nextId, 3, '0', STR_PAD_LEFT);
                Database::execute(
                    "INSERT INTO admin_account
                        (id, full_name, email, password, phone, bio, role_label, admin_code, avatar_url, email_notifications, live_notifications, developer_mode, member_since, verified)
                     VALUES (?, ?, ?, ?, '', '', ?, ?, '/assets/logo.png', 1, 1, 0, ?, 1)",
                    [$nextId, $fullName, $email, $hashed, $roleLabel, $adminCode, date('Y')]
                );
                return $res->status(201)->json(['success' => true, 'message' => 'Usuario creado']);
            } catch (\Exception $e) {
                error_log('Error en POST /auth/users: ' . $e->getMessage());
                return $res->status(500)->json(['success' => false, 'message' => 'No se pudo crear el usuario: ' . $e->getMessage()]);
            }
        });

        $r->delete('/users/:id', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            $id = (int)($req->params['id'] ?? 0);
            if (!$id) {
                return $res->status(400)->json(['success' => false, 'message' => 'ID inválido']);
            }
            try {
                $storage = resolveUsersStorage();
                if ($storage === 'users') {
                    ensureUsersTable();
                    Database::execute("DELETE FROM users WHERE id = ?", [$id]);
                } else {
                    if ($id === 1) {
                        return $res->status(400)->json(['success' => false, 'message' => 'No se puede eliminar la cuenta principal']);
                    }
                    Database::execute("DELETE FROM admin_account WHERE id = ?", [$id]);
                }
                return $res->json(['success' => true, 'message' => 'Usuario eliminado']);
            } catch (\Exception $e) {
                error_log('Error en DELETE /auth/users/:id: ' . $e->getMessage());
                return $res->status(500)->json(['success' => false, 'message' => 'No se pudo eliminar el usuario']);
            }
        });

        $r->get('/profiles', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            try {
                $storage = resolveUsersStorage();
                if ($storage === 'users') {
                    ensureUsersTable();
                    $rows = Database::fetchAll("SELECT id, full_name FROM users ORDER BY full_name ASC");
                } else {
                    $rows = Database::fetchAll("SELECT id, full_name FROM admin_account ORDER BY id ASC");
                }
                $profiles = array_map(fn($u) => ['id' => $u['id'], 'fullName' => $u['full_name']], $rows);
                return $res->json(['success' => true, 'profiles' => $profiles]);
            } catch (\Exception $e) {
                error_log('Error en GET /auth/profiles: ' . $e->getMessage());
                return $res->status(500)->json(['success' => false, 'message' => 'No se pudo obtener los perfiles']);
            }
        });
    });
}

function createAuthToken(string $email, string $jwtSecret, string $role = 'admin'): string {
    $payload = [
        'email' => $email,
        'role'  => $role,
        'iat'   => time(),
        'exp'   => time() + (8 * 3600),
    ];
    return Jwt::encode($payload, $jwtSecret);
}

function sanitizeAccount(array $row): array {
    return [
        'id'          => $row['id'],
        'fullName'    => $row['full_name'],
        'email'       => $row['email'],
        'phone'       => $row['phone'],
        'bio'         => $row['bio'],
        'roleLabel'   => $row['role_label'],
        'adminCode'   => $row['admin_code'],
        'avatarUrl'   => $row['avatar_url'],
        'memberSince' => $row['member_since'],
        'verified'    => $row['verified'],
        'preferences' => [
            'emailNotifications' => (bool)$row['email_notifications'],
            'liveNotifications'  => (bool)$row['live_notifications'],
            'developerMode'      => (bool)$row['developer_mode'],
        ],
    ];
}

function ensureAdminAccountTable(): void {
    Database::execute("
        CREATE TABLE IF NOT EXISTS admin_account (
            id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
            full_name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            phone VARCHAR(60) NOT NULL DEFAULT '',
            bio VARCHAR(1000) NOT NULL DEFAULT '',
            role_label VARCHAR(120) NOT NULL DEFAULT 'Administrador Principal',
            admin_code VARCHAR(32) NOT NULL DEFAULT 'ADMIN-001',
            avatar_url VARCHAR(255) NOT NULL DEFAULT '/assets/logo.png',
            email_notifications TINYINT(1) NOT NULL DEFAULT 1,
            live_notifications TINYINT(1) NOT NULL DEFAULT 1,
            developer_mode TINYINT(1) NOT NULL DEFAULT 0,
            member_since VARCHAR(16) NOT NULL DEFAULT '2023',
            verified TINYINT(1) NOT NULL DEFAULT 1,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
}

function ensureAdminAccount(): array {
    ensureAdminAccountTable();
    $config = require __DIR__ . '/../config.php';

    $adminEmail = $config['admin_email'];
    $adminPassword = $config['admin_password'];
    if (!$adminEmail || !$adminPassword) {
        throw new \RuntimeException('Faltan variables de entorno: ADMIN_EMAIL o ADMIN_PASSWORD');
    }

    $row = Database::fetchOne("SELECT * FROM admin_account WHERE id = 1");
    if ($row) {
        // Auto-migrate plaintext passwords to bcrypt
        if ($row['password'] && !str_starts_with($row['password'], '$2')) {
            $hashed = password_hash($row['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            Database::execute("UPDATE admin_account SET password = ? WHERE id = 1", [$hashed]);
            $row['password'] = $hashed;
        }
        return $row;
    }

    $hashedPassword = password_hash($adminPassword, PASSWORD_BCRYPT, ['cost' => 12]);
    Database::execute(
        "INSERT INTO admin_account (id, full_name, email, password, phone, bio, role_label, admin_code, avatar_url, email_notifications, live_notifications, developer_mode, member_since, verified)
         VALUES (1, ?, ?, ?, '', 'Gestión integral de la plataforma administrativa y supervisión de ligas regionales.', 'Administrador Principal', 'ADMIN-001', '/assets/logo.png', TRUE, TRUE, FALSE, '2023', TRUE)
         ON DUPLICATE KEY UPDATE email = VALUES(email), password = VALUES(password), updated_at = NOW()",
        ['Administrador Juego de Equipo', $adminEmail, $hashedPassword]
    );
    return Database::fetchOne("SELECT * FROM admin_account WHERE id = 1");
}

function fetchAdminAccountById(int $id): array|false {
    ensureAdminAccountTable();
    return Database::fetchOne("SELECT * FROM admin_account WHERE id = ?", [$id]);
}

function fetchAdminAccountByEmail(string $email): array|false {
    ensureAdminAccountTable();
    $row = Database::fetchOne("SELECT * FROM admin_account WHERE LOWER(email) = LOWER(?) LIMIT 1", [trim($email)]);
    if ($row && !str_starts_with((string) ($row['password'] ?? ''), '$2')) {
        $hashed = password_hash((string) $row['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        Database::execute("UPDATE admin_account SET password = ?, updated_at = NOW() WHERE id = ?", [$hashed, (int) $row['id']]);
        $row['password'] = $hashed;
    }

    return $row;
}

function fetchAuthenticatedAdminAccount(Request $req): array {
    $email = trim((string) ($req->user->email ?? ''));
    if ($email === '') {
        throw new \RuntimeException('No se encontró el email del usuario autenticado');
    }

    $account = fetchAdminAccountByEmail($email);
    if (!$account) {
        throw new \RuntimeException('La cuenta autenticada no existe en admin_account');
    }

    return $account;
}

function ensureUsersTable(): void {
    $tableExists = (bool) Database::fetchOne(
        'SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ? LIMIT 1',
        ['users']
    );

    if (!$tableExists) {
        Database::execute(" 
            CREATE TABLE users (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                full_name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                role VARCHAR(50) NOT NULL DEFAULT 'editor',
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    // Compatibilidad con instalaciones previas: migrar esquema legacy si faltan columnas.
    if (!Database::columnExists('users', 'full_name')) {
        if (Database::columnExists('users', 'name')) {
            Database::execute("ALTER TABLE users ADD COLUMN full_name VARCHAR(255) NULL AFTER id");
            Database::execute("UPDATE users SET full_name = name WHERE (full_name IS NULL OR full_name = '')");
            Database::execute("ALTER TABLE users MODIFY COLUMN full_name VARCHAR(255) NOT NULL");
        } else {
            Database::execute("ALTER TABLE users ADD COLUMN full_name VARCHAR(255) NOT NULL DEFAULT '' AFTER id");
        }
    }

    if (!Database::columnExists('users', 'email')) {
        Database::execute("ALTER TABLE users ADD COLUMN email VARCHAR(255) NOT NULL DEFAULT '' AFTER full_name");
    }

    if (!Database::columnExists('users', 'password')) {
        Database::execute("ALTER TABLE users ADD COLUMN password VARCHAR(255) NOT NULL DEFAULT '' AFTER email");
    }

    if (!Database::columnExists('users', 'role')) {
        Database::execute("ALTER TABLE users ADD COLUMN role VARCHAR(50) NOT NULL DEFAULT 'editor' AFTER password");
    }

    if (!Database::columnExists('users', 'created_at')) {
        Database::execute("ALTER TABLE users ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER role");
    }

    if (!Database::indexExists('users', 'users_email_unique')) {
        try {
            Database::ensureUniqueIndex('users', 'users_email_unique', 'email');
        } catch (\Throwable $e) {
            // Si hay duplicados históricos no frenamos la carga/listado.
            error_log('No se pudo crear índice único users_email_unique: ' . $e->getMessage());
        }
    }
}

function tableExists(string $table): bool {
    return (bool) Database::fetchOne(
        'SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ? LIMIT 1',
        [$table]
    );
}

function resolveUsersStorage(): string {
    if (tableExists('users')) {
        return 'users';
    }

    if (tableExists('admin_account')) {
        return 'admin_account';
    }

    // Entornos nuevos: si no existe ninguna de las dos, intentamos crear users.
    ensureUsersTable();
    if (tableExists('users')) {
        return 'users';
    }

    throw new \RuntimeException('No se encontró almacenamiento para usuarios');
}

function normalizeRoleFromLabel(string $roleLabel): string {
    $label = mb_strtolower(trim($roleLabel), 'UTF-8');
    if (str_contains($label, 'admin')) {
        return 'admin';
    }

    return 'editor';
}

function roleLabelForRole(string $role): string {
    return $role === 'admin' ? 'Administrador' : 'Editor';
}
