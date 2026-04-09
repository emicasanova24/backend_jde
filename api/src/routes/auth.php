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
                $account = ensureAdminAccount();

                if ($email !== $account['email']) {
                    return $res->status(401)->json(['success' => false, 'message' => 'Credenciales inválidas']);
                }

                if (password_verify($password, $account['password'])) {
                    $token = createAuthToken($account['email'], $jwtSecret);
                    return $res->json(['success' => true, 'token' => $token, 'account' => sanitizeAccount($account)]);
                }

                // Fallback de recuperación: permite re-sincronizar la clave bootstrap del .env
                if ($adminEmail && $adminPassword && $email === $adminEmail && $password === $adminPassword) {
                    $hashedPassword = password_hash($adminPassword, PASSWORD_BCRYPT, ['cost' => 12]);
                    Database::execute(
                        "UPDATE admin_account SET password = ?, updated_at = NOW() WHERE id = 1",
                        [$hashedPassword]
                    );
                    $account['password'] = $hashedPassword;
                    $token = createAuthToken($account['email'], $jwtSecret);
                    return $res->json(['success' => true, 'token' => $token, 'account' => sanitizeAccount($account)]);
                }

                return $res->status(401)->json(['success' => false, 'message' => 'Credenciales inválidas']);
            } catch (\Exception $e) {
                error_log('Error en /auth/login: ' . $e->getMessage());
                return $res->status(500)->json(['success' => false, 'message' => 'Error al iniciar sesión']);
            }
        });

        $r->get('/account', function (Request $req, Response $res) {
            return authMiddleware($req, $res);
        }, function (Request $req, Response $res) {
            try {
                $account = ensureAdminAccount();
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
                ensureAdminAccount();
                $config = require __DIR__ . '/../config.php';

                Database::execute(
                    "UPDATE admin_account
                     SET full_name = ?, email = ?, phone = ?, bio = ?, avatar_url = ?,
                         email_notifications = ?, live_notifications = ?, developer_mode = ?,
                         updated_at = NOW()
                     WHERE id = 1",
                    [
                        trim($fullName), trim($email), trim($phone), trim($bio),
                        trim($avatarUrl) ?: '/assets/logo.png',
                        !empty($preferences['emailNotifications']), !empty($preferences['liveNotifications']),
                        !empty($preferences['developerMode']),
                    ]
                );

                $row = ensureAdminAccount();

                $token = createAuthToken($row['email'], $config['jwt_secret']);
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
                $account = ensureAdminAccount();

                if (!password_verify($currentPassword, $account['password'])) {
                    return $res->status(401)->json(['success' => false, 'message' => 'La contraseña actual es incorrecta']);
                }

                $hashed = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
                Database::execute("UPDATE admin_account SET password = ?, updated_at = NOW() WHERE id = 1", [$hashed]);

                return $res->json(['success' => true, 'message' => 'Contraseña actualizada']);
            } catch (\Exception $e) {
                error_log('Error en POST /auth/account/password: ' . $e->getMessage());
                return $res->status(500)->json(['success' => false, 'message' => 'No se pudo actualizar la contraseña']);
            }
        });
    });
}

function createAuthToken(string $email, string $jwtSecret): string {
    $payload = [
        'email' => $email,
        'role'  => 'admin',
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
            email VARCHAR(191) NOT NULL UNIQUE,
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
