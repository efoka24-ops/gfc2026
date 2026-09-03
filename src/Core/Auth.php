<?php
declare(strict_types=1);

namespace Gfc\Core;

final class Auth
{
    /**
     * Matrice des permissions : la même que celle affichée dans le back office.
     * '*' = toutes les équipes, 'own' = uniquement son équipe.
     */
    private const PERMISSIONS = [
        'admin' => [
            'team.write' => '*', 'squad.write' => '*', 'match.schedule' => '*',
            'sheet.write' => '*', 'sheet.validate' => '*', 'news.write' => '*',
            'tickets.write' => '*', 'sanction.write' => '*', 'users.write' => '*',
            'standings.recompute' => '*', 'sponsors.write' => '*',
        ],
        'delegate' => ['team.write' => 'own', 'squad.write' => 'own'],
        'referee'  => ['sheet.write' => 'assigned'],
        'editor'   => ['news.write' => '*'],
    ];

    public function __construct(private Database $db, private array $config)
    {
    }

    public function startSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        $c = $this->config['session'];
        session_name($c['name']);
        session_set_cookie_params([
            'lifetime' => $c['lifetime'],
            'path'     => '/',
            'secure'   => (bool) $c['secure'],
            'httponly' => true,
            'samesite' => $c['samesite'],
        ]);
        session_start();
    }

    public function attempt(string $phone, string $password): ?array
    {
        $user = $this->db->one(
            'SELECT * FROM users WHERE phone = ? AND status = "active"',
            [$phone]
        );
        if ($user === null || !password_verify($password, $user['password_hash'])) {
            return null;
        }

        $this->startSession();
        $_SESSION['user_id'] = (int) $user['id'];
        $this->db->run('UPDATE users SET last_login_at = NOW() WHERE id = ?', [$user['id']]);
        $this->log((int) $user['id'], 'auth.login');

        return $user;
    }

    public function logout(): void
    {
        $this->startSession();
        $_SESSION = [];
        session_destroy();
    }

    /** Utilisateur staff courant (session back office ou token API staff). */
    public function user(?Request $req = null): ?array
    {
        $this->startSession();
        if (!empty($_SESSION['user_id'])) {
            return $this->db->one('SELECT * FROM users WHERE id = ?', [$_SESSION['user_id']]);
        }
        if ($req !== null && ($token = $req->bearer()) !== null) {
            return $this->db->one(
                'SELECT u.* FROM api_tokens t
                   JOIN users u ON u.id = t.user_id
                  WHERE t.token_hash = ? AND t.expires_at > NOW()',
                [hash('sha256', $token)]
            );
        }
        return null;
    }

    /** Supporter connecté par code SMS. */
    public function appUser(Request $req): ?array
    {
        $token = $req->bearer();
        if ($token === null) {
            return null;
        }
        return $this->db->one(
            'SELECT a.* FROM api_tokens t
               JOIN app_users a ON a.id = t.app_user_id
              WHERE t.token_hash = ? AND t.expires_at > NOW()',
            [hash('sha256', $token)]
        );
    }

    public function can(array $user, string $ability, ?int $teamId = null, ?int $matchId = null): bool
    {
        $scope = self::PERMISSIONS[$user['role']][$ability] ?? null;
        if ($scope === null) {
            return false;
        }
        if ($scope === '*') {
            return true;
        }
        if ($scope === 'own') {
            return $teamId !== null && (int) $user['team_id'] === $teamId;
        }
        if ($scope === 'assigned') {
            return $matchId !== null && (bool) $this->db->value(
                'SELECT 1 FROM matches WHERE id = ? AND referee_id = ?',
                [$matchId, $user['id']]
            );
        }
        return false;
    }

    public function requireStaff(Request $req, string $ability = null, ?int $teamId = null, ?int $matchId = null): array
    {
        $user = $this->user($req);
        if ($user === null) {
            $req->wantsJson()
                ? Response::error('unauthenticated', 'Connexion requise.', 401)
                : Response::redirect('/admin/login');
        }
        if ($ability !== null && !$this->can($user, $ability, $teamId, $matchId)) {
            $req->wantsJson()
                ? Response::error('forbidden', 'Action non autorisée pour ce rôle.', 403)
                : Response::html('<h1>403</h1><p>Action non autorisée.</p>', 403);
        }
        return $user;
    }

    public function issueToken(?int $userId, ?int $appUserId, int $days = 90): string
    {
        $token = bin2hex(random_bytes(24));
        $this->db->insert('api_tokens', [
            'user_id'     => $userId,
            'app_user_id' => $appUserId,
            'token_hash'  => hash('sha256', $token),
            'expires_at'  => (new \DateTimeImmutable("+$days days"))->format('Y-m-d H:i:s'),
        ]);
        return $token;
    }

    public function log(?int $userId, string $action, ?string $entity = null, ?int $entityId = null, array $meta = []): void
    {
        $this->db->insert('audit_log', [
            'user_id'   => $userId,
            'action'    => $action,
            'entity'    => $entity,
            'entity_id' => $entityId,
            'meta'      => $meta === [] ? null : json_encode($meta, JSON_UNESCAPED_UNICODE),
        ]);
    }
}
