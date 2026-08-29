<?php
namespace Gfc;

final class Auth
{
    /** Connexion back-office / API d'écriture. */
    public static function login(string $email, string $password): ?array
    {
        $user = Database::one('SELECT * FROM users WHERE email = ? AND is_active = 1', [$email]);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return null;
        }
        $cfg   = require __DIR__ . '/../config/config.php';
        $token = bin2hex(random_bytes(32));
        Database::run(
            'INSERT INTO api_tokens (user_id, token, expires_at) VALUES (?,?, DATE_ADD(NOW(), INTERVAL ? HOUR))',
            [$user['id'], $token, $cfg['app']['token_ttl_hours']]
        );
        unset($user['password_hash']);
        return ['token' => $token, 'user' => $user];
    }

    /** Vérifie le header Authorization: Bearer <token>. */
    public static function requireUser(array $roles = []): array
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/Bearer\s+([a-f0-9]{64})/i', $header, $m)) {
            Response::error('Token manquant', 401);
        }
        $user = Database::one(
            'SELECT u.* FROM api_tokens t JOIN users u ON u.id = t.user_id
             WHERE t.token = ? AND t.expires_at > NOW() AND u.is_active = 1',
            [$m[1]]
        );
        if (!$user) {
            Response::error('Session expirée', 401);
        }
        if ($roles && !in_array($user['role'], $roles, true)) {
            Response::error('Accès refusé', 403);
        }
        return $user;
    }

    /** Session PHP pour les pages du back-office. */
    public static function startSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public static function requireSession(array $roles = []): array
    {
        self::startSession();
        $user = $_SESSION['gfc_user'] ?? null;
        if (!$user) {
            header('Location: login.php');
            exit;
        }
        if ($roles && !in_array($user['role'], $roles, true)) {
            http_response_code(403);
            exit('Accès refusé');
        }
        return $user;
    }
}
