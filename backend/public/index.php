<?php
/**
 * Garoua Football Challenge — API REST (PHP 8.1+)
 * Toutes les routes de lecture sont publiques, les écritures exigent un token.
 * php -S localhost:8000 -t public
 */
declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $path = __DIR__ . '/../src/' . str_replace(['Gfc\\', '\\'], ['', '/'], $class) . '.php';
    if (is_file($path)) { require $path; }
});

use Gfc\Auth;
use Gfc\Database;
use Gfc\Repo;
use Gfc\Response;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE');
    exit;
}

$cfg    = require __DIR__ . '/../config/config.php';
$season = (int) $cfg['app']['current_season'];
$method = $_SERVER['REQUEST_METHOD'];
$path   = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '', '/');
$path   = preg_replace('#^api/?#', '', $path);
$seg    = $path === '' ? [] : explode('/', $path);
$q      = $_GET;
$body   = json_decode(file_get_contents('php://input') ?: '[]', true) ?: [];

try {
    switch (true) {

        case $seg === [] :
            Response::json(['name' => $cfg['app']['name'], 'api' => 'v1', 'season' => $season]);

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
            $m = Repo::match((int) $seg[1]) ?? Response::error('Match introuvable', 404);
            Response::json($m);

        case $seg === ['standings'] && $method === 'GET':
            Response::json(Repo::standings($q['competition'] ?? 'championnat'));

        case $seg === ['teams'] && $method === 'GET':
            Response::json(Repo::teams($season));

        case count($seg) === 2 && $seg[0] === 'teams' && ctype_digit($seg[1]) && $method === 'GET':
            $t = Repo::team((int) $seg[1]) ?? Response::error('Équipe introuvable', 404);
            Response::json($t);

        case count($seg) === 2 && $seg[0] === 'players' && ctype_digit($seg[1]) && $method === 'GET':
            $p = Repo::player((int) $seg[1]) ?? Response::error('Joueur introuvable', 404);
            Response::json($p);

        case $seg === ['stats', 'players'] && $method === 'GET':
            Response::json(Repo::playerRankings(
                $q['competition'] ?? 'championnat',
                $q['metric'] ?? 'goals'
            ));

        case $seg === ['stats', 'teams'] && $method === 'GET':
            Response::json(Repo::teamRankings($q['competition'] ?? 'championnat'));

        case $seg === ['news'] && $method === 'GET':
            Response::json(Repo::news((int) ($q['limit'] ?? 20)));

        case $seg === ['media'] && $method === 'GET':
            Response::json(Repo::media($q['type'] ?? null));

        case $seg === ['devices'] && $method === 'POST':
            Database::run(
                'INSERT INTO device_tokens (expo_token, platform, favourite_team_id) VALUES (?,?,?)
                 ON DUPLICATE KEY UPDATE favourite_team_id = VALUES(favourite_team_id)',
                [$body['token'] ?? '', $body['platform'] ?? 'android', $body['team_id'] ?? null]
            );
            Response::json(['ok' => true], 201);

        case $seg === ['auth', 'login'] && $method === 'POST':
            $r = Auth::login($body['email'] ?? '', $body['password'] ?? '');
            $r ? Response::json($r) : Response::error('Identifiants invalides', 401);

        /* ---------- écritures (back-office / arbitre) ---------- */

        case count($seg) === 3 && $seg[0] === 'matches' && $seg[2] === 'events' && $method === 'POST':
            $user = Auth::requireUser(['admin', 'secretaire', 'arbitre']);
            $id   = (int) $seg[1];
            Database::run(
                'INSERT INTO match_events (match_id, team_id, player_id, related_player_id, minute, type, detail, is_published, created_by)
                 VALUES (?,?,?,?,?,?,?,?,?)',
                [$id, $body['team_id'] ?? null, $body['player_id'] ?? null, $body['related_player_id'] ?? null,
                 (int) ($body['minute'] ?? 0), $body['type'] ?? 'goal', $body['detail'] ?? null,
                 (int) ($body['publish'] ?? 1), $user['id']]
            );
            require __DIR__ . '/../src/Score.php';
            \Gfc\Score::recompute($id);
            Response::json(Repo::match($id), 201);

        case count($seg) === 2 && $seg[0] === 'matches' && $method === 'PATCH':
            Auth::requireUser(['admin', 'secretaire', 'arbitre']);
            $id = (int) $seg[1];
            $allowed = ['status', 'minute', 'attendance', 'referee', 'venue', 'kickoff_at'];
            $set = []; $params = [];
            foreach ($allowed as $col) {
                if (array_key_exists($col, $body)) { $set[] = "$col = ?"; $params[] = $body[$col]; }
            }
            if (!$set) { Response::error('Aucun champ à mettre à jour'); }
            $params[] = $id;
            Database::run('UPDATE matches SET ' . implode(', ', $set) . ' WHERE id = ?', $params);
            Response::json(Repo::match($id));

        default:
            Response::error('Route inconnue: ' . $path, 404);
    }
} catch (\Throwable $e) {
    Response::error('Erreur serveur : ' . $e->getMessage(), 500);
}
