<?php
namespace Gfc;

/**
 * Authentification et autorisation.
 *
 * Deux canaux distincts partagent la meme table users et les memes roles :
 *   - jetons Bearer pour l'API d'ecriture, utilises par l'espace operateur
 *     mobile (US8) ;
 *   - sessions PHP pour les pages du back-office.
 *
 * Roles : admin (acces complet), secretaire (contenus et saisie),
 * arbitre (saisie live seule).
 */
final class Auth
{
    public const ROLE_ADMIN      = 'admin';
    public const ROLE_SECRETAIRE = 'secretaire';
    public const ROLE_ARBITRE    = 'arbitre';

    /** Roles autorises a saisir un match en direct. */
    public const SAISIE = [self::ROLE_ADMIN, self::ROLE_SECRETAIRE, self::ROLE_ARBITRE];

    // ---------------------------------------------------------------- jetons

    /**
     * Verifie les identifiants et delivre un jeton d'ecriture a duree limitee.
     *
     * @return array{token:string,expires_at:string,user:array}|null
     */
    public static function login(string $email, string $password): ?array
    {
        $user = Database::one(
            'SELECT * FROM users WHERE email = ? AND is_active = 1',
            [$email]
        );

        // password_verify sur un condensat factice quand le compte n'existe pas :
        // le temps de reponse ne doit pas reveler si l'adresse est connue.
        if (!$user) {
            password_verify($password, '$2y$10$' . str_repeat('0', 53));
            return null;
        }
        if (!password_verify($password, $user['password_hash'])) {
            return null;
        }

        $cfg   = require __DIR__ . '/../config/config.php';
        $ttl   = (int) $cfg['app']['token_ttl_hours'];
        $token = bin2hex(random_bytes(32));

        Database::run(
            'INSERT INTO api_tokens (user_id, token, expires_at)
             VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? HOUR))',
            [$user['id'], $token, $ttl]
        );

        // Menage : un jeton expire n'a plus aucune raison d'exister.
        Database::run('DELETE FROM api_tokens WHERE expires_at < NOW()');

        $expires = Database::one(
            'SELECT expires_at FROM api_tokens WHERE token = ?',
            [$token]
        );

        unset($user['password_hash']);

        return [
            'token'      => $token,
            'expires_at' => $expires['expires_at'] ?? null,
            'user'       => $user,
        ];
    }

    /**
     * Exige un jeton valide dans l'en-tete Authorization, et optionnellement
     * un role parmi ceux passes. Interrompt la requete si l'acces est refuse.
     *
     * @param string[] $roles roles autorises ; vide = tout utilisateur connecte
     */
    public static function requireUser(array $roles = []): array
    {
        $header = $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']  // Apache derriere .htaccess
            ?? '';

        if (!preg_match('/Bearer\s+([a-f0-9]{64})/i', $header, $m)) {
            Response::error('Authentification requise.', 401);
        }

        $user = Database::one(
            'SELECT u.* FROM api_tokens t
               JOIN users u ON u.id = t.user_id
              WHERE t.token = ? AND t.expires_at > NOW() AND u.is_active = 1',
            [$m[1]]
        );

        if (!$user) {
            Response::error('Session expiree, reconnectez-vous.', 401, 'session_expiree');
        }
        if ($roles && !in_array($user['role'], $roles, true)) {
            Response::error('Vous n\'avez pas les droits pour cette action.', 403);
        }

        unset($user['password_hash']);
        return $user;
    }

    // -------------------------------------------------------------- sessions

    public static function startSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_set_cookie_params([
                'httponly' => true,
                'samesite' => 'Lax',
                'secure'   => !empty($_SERVER['HTTPS']),
            ]);
            session_start();
        }
    }

    /**
     * Exige une session de back-office, et optionnellement un role.
     * Redirige vers la connexion si absente, refuse en 403 si le role manque.
     *
     * @param string[] $roles
     */
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
            exit('Acces refuse : cette page ne vous est pas ouverte.');
        }

        return $user;
    }

    public static function logout(): void
    {
        self::startSession();
        $_SESSION = [];
        session_destroy();
    }

    // ------------------------------------------------------------------ CSRF

    /**
     * Jeton anti-falsification de requete, a poser dans chaque formulaire du
     * back-office (FR-027). Le meme jeton vaut pour toute la session.
     */
    public static function csrfToken(): string
    {
        self::startSession();
        if (empty($_SESSION['gfc_csrf'])) {
            $_SESSION['gfc_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['gfc_csrf'];
    }

    /** Champ cache pret a inserer dans un formulaire. */
    public static function csrfField(): string
    {
        return '<input type="hidden" name="_csrf" value="'
            . htmlspecialchars(self::csrfToken(), ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Verifie le jeton CSRF d'une soumission. Interrompt la requete s'il est
     * absent ou incorrect — a appeler en tete de tout traitement POST.
     */
    public static function requireCsrf(): void
    {
        self::startSession();

        $recu   = $_POST['_csrf'] ?? '';
        $attendu = $_SESSION['gfc_csrf'] ?? '';

        if ($attendu === '' || !is_string($recu) || !hash_equals($attendu, $recu)) {
            http_response_code(403);
            exit('Formulaire expire ou invalide. Revenez en arriere et recommencez.');
        }
    }
}
