<?php
/**
 * Garoua Football Challenge — API REST (PHP 8.1+)
 *
 * Contrat : specs/001-plateforme-gfc/contracts/api.md (branche main).
 * Les lectures sont publiques, les ecritures exigent un jeton Bearer.
 *
 * En developpement :  php -S localhost:8000 -t public
 */
declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $path = __DIR__ . '/../src/' . str_replace(['Gfc\\', '\\'], ['', '/'], $class) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

use Gfc\Auth;
use Gfc\Database;
use Gfc\Repo;
use Gfc\Response;
use Gfc\Score;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE');
    exit;
}

$cfg     = require __DIR__ . '/../config/config.php';
$season  = (int) $cfg['app']['current_season'];
$places  = (int) ($cfg['app']['qualification_places'] ?? 0);
$method  = $_SERVER['REQUEST_METHOD'];
$path    = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '', '/');
$path    = preg_replace('#^api/?#', '', $path);
$seg     = $path === '' ? [] : explode('/', $path);
$q       = $_GET;
$body    = json_decode(file_get_contents('php://input') ?: '[]', true) ?: [];

/** Renvoie une fiche de match avec la politique de cache qui convient. */
$repondreMatch = static function (int $id): never {
    $match = Repo::match($id);
    if (!$match) {
        Response::error('Match introuvable.', 404);
    }
    // Un match en cours ne doit jamais etre servi depuis un cache : c'est la
    // route interrogee toutes les 15 s pendant le direct (principe I).
    Repo::estEnDirect($match) ? Response::live($match) : Response::json($match);
};

try {
    switch (true) {

        // ================================================ LECTURE PUBLIQUE

        case $seg === []:
            Response::json([
                'name'   => $cfg['app']['name'],
                'api'    => 'v1',
                'season' => $season,
            ]);

        case $seg === ['competitions'] && $method === 'GET':
            Response::json(Repo::competitions($season));

        case $seg === ['matches'] && $method === 'GET':
            Response::json(Repo::matches([
                'season'      => $season,
                'competition' => $q['competition'] ?? null,
                'team'        => isset($q['team']) ? (int) $q['team'] : null,
                'scope'       => $q['scope'] ?? null,
                'limit'       => $q['limit'] ?? 100,
            ]));

        case count($seg) === 2 && $seg[0] === 'matches' && ctype_digit($seg[1]) && $method === 'GET':
            $repondreMatch((int) $seg[1]);

        case $seg === ['standings'] && $method === 'GET':
            Response::json(Repo::standings($q['competition'] ?? 'championnat', $places));

        case $seg === ['teams'] && $method === 'GET':
            Response::json(Repo::teams($season));

        case count($seg) === 2 && $seg[0] === 'teams' && ctype_digit($seg[1]) && $method === 'GET':
            $team = Repo::team((int) $seg[1], $places);
            if (!$team) {
                Response::error('Equipe introuvable.', 404);
            }
            Response::json($team);

        case count($seg) === 2 && $seg[0] === 'players' && ctype_digit($seg[1]) && $method === 'GET':
            $player = Repo::player((int) $seg[1]);
            if (!$player) {
                Response::error('Joueur introuvable.', 404);
            }
            Response::json($player);

        case $seg === ['stats', 'players'] && $method === 'GET':
            Response::json(Repo::playerRankings(
                $q['competition'] ?? 'championnat',
                $q['metric'] ?? 'goals',
                (int) ($q['limit'] ?? 20)
            ));

        case $seg === ['stats', 'teams'] && $method === 'GET':
            Response::json(Repo::teamRankings($q['competition'] ?? 'championnat'));

        case $seg === ['news'] && $method === 'GET':
            Response::json(Repo::news((int) ($q['limit'] ?? 20)));

        case $seg === ['media'] && $method === 'GET':
            Response::json(Repo::media($q['type'] ?? null));

        // ================================================== ENREGISTREMENT

        case $seg === ['devices'] && $method === 'POST':
            // Route publique : un appareil n'est pas un compte.
            $expoToken = (string) ($body['expo_token'] ?? $body['token'] ?? '');
            if ($expoToken === '') {
                Response::error('Jeton d\'appareil manquant.', 400);
            }
            $existe = Database::one('SELECT id FROM device_tokens WHERE expo_token = ?', [$expoToken]);
            Database::run(
                'INSERT INTO device_tokens (expo_token, platform, favourite_team_id)
                 VALUES (?,?,?)
                 ON DUPLICATE KEY UPDATE favourite_team_id = VALUES(favourite_team_id)',
                [
                    $expoToken,
                    ($body['platform'] ?? 'android') === 'ios' ? 'ios' : 'android',
                    isset($body['favourite_team_id']) ? (int) $body['favourite_team_id'] : null,
                ]
            );
            Response::json(['ok' => true], $existe ? 200 : 201);

        // ================================================ AUTHENTIFICATION

        case $seg === ['auth', 'login'] && $method === 'POST':
            $session = Auth::login((string) ($body['email'] ?? ''), (string) ($body['password'] ?? ''));
            if (!$session) {
                Response::error('Identifiants invalides.', 401);
            }
            Response::json($session);

        // ============================================== ESPACE OPERATEUR

        case $seg === ['me', 'matches'] && $method === 'GET':
            $user = Auth::requireUser(Auth::SAISIE);
            Response::live(Repo::matchsOperateur($user));

        case count($seg) === 3 && $seg[0] === 'matches' && $seg[2] === 'squads' && $method === 'GET':
            Auth::requireUser(Auth::SAISIE);
            $squads = Repo::effectifsDuMatch((int) $seg[1]);
            if ($squads === null) {
                Response::error('Match introuvable.', 404);
            }
            Response::json($squads);

        case count($seg) === 3 && $seg[0] === 'matches' && $seg[2] === 'lineups' && $method === 'PUT':
            Auth::requireUser(Auth::SAISIE);
            $teamId = (int) ($body['team_id'] ?? 0);
            if ($teamId <= 0 || !is_array($body['players'] ?? null)) {
                Response::error('Equipe ou liste de joueurs manquante.', 400);
            }
            try {
                Repo::enregistrerComposition((int) $seg[1], $teamId, $body['players']);
            } catch (\RuntimeException $e) {
                Response::error($e->getMessage(), 422);
            }
            $repondreMatch((int) $seg[1]);

        case count($seg) === 3 && $seg[0] === 'matches' && $seg[2] === 'stats' && $method === 'PUT':
            Auth::requireUser([Auth::ROLE_ADMIN, Auth::ROLE_SECRETAIRE]);
            $teamId = (int) ($body['team_id'] ?? 0);
            if ($teamId <= 0) {
                Response::error('Equipe manquante.', 400);
            }
            Repo::enregistrerStatsMatch((int) $seg[1], $teamId, $body);
            $repondreMatch((int) $seg[1]);

        // ================================================== SAISIE LIVE

        case count($seg) === 3 && $seg[0] === 'matches' && $seg[2] === 'events' && $method === 'POST':
            $user = Auth::requireUser(Auth::SAISIE);
            $id   = (int) $seg[1];
            try {
                $resultat = Repo::ajouterEvenement($id, $body, (int) $user['id']);
            } catch (\RuntimeException $e) {
                Response::error($e->getMessage(), 422);
            }
            // 201 si l'evenement vient d'etre cree, 200 si le client_ref etait
            // deja connu — une saisie rejouee apres une coupure reseau.
            Response::live(
                ['event' => $resultat['event'], 'match' => Repo::match($id)],
                $resultat['cree'] ? 201 : 200
            );

        case count($seg) === 4 && $seg[0] === 'matches' && $seg[2] === 'events'
             && ctype_digit($seg[3]) && $method === 'DELETE':
            $user = Auth::requireUser([Auth::ROLE_ADMIN, Auth::ROLE_SECRETAIRE]);
            $ok = Repo::supprimerEvenement((int) $seg[1], (int) $seg[3], (int) $user['id']);
            if (!$ok) {
                Response::error('Evenement introuvable.', 404);
            }
            $repondreMatch((int) $seg[1]);

        case count($seg) === 2 && $seg[0] === 'matches' && ctype_digit($seg[1]) && $method === 'PATCH':
            $user = Auth::requireUser(Auth::SAISIE);
            if (!Repo::mettreAJourMatch((int) $seg[1], $body, (string) $user['role'])) {
                Response::error('Aucun champ modifiable dans cette requete.', 400);
            }
            $repondreMatch((int) $seg[1]);

        // ==================================================== MAINTENANCE

        case $seg === ['admin', 'audit-scores'] && $method === 'GET':
            // Filet de l'invariant I1 : signale tout match dont le score
            // enregistre ne correspond pas a ses evenements.
            Auth::requireUser([Auth::ROLE_ADMIN]);
            Response::json(['ecarts' => Score::audit()], 200, 0);

        default:
            Response::error('Route inconnue.', 404);
    }
} catch (\Throwable $e) {
    // Le detail de l'exception ne doit jamais sortir : il revele la structure
    // de la base et les chemins du serveur. Il part dans le journal PHP.
    error_log('[GFC] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    Response::error('Une erreur est survenue. Reessayez dans un instant.', 500);
}
