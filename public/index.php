<?php
declare(strict_types=1);

/**
 * Garoua Football Challenge — front controller unique.
 * Application web complète : API JSON + back office rendu serveur.
 */

define('BASE_PATH', dirname(__DIR__));

$config = require BASE_PATH . '/config/config.php';
date_default_timezone_set($config['app']['timezone']);

if (($config['app']['env'] ?? 'production') === 'local') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

spl_autoload_register(static function (string $class): void {
    if (!str_starts_with($class, 'Gfc\\')) {
        return;
    }
    $path = BASE_PATH . '/src/' . str_replace('\\', '/', substr($class, 4)) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

use Gfc\Core\Auth;
use Gfc\Core\Database;
use Gfc\Core\Request;
use Gfc\Core\Response;
use Gfc\Core\Router;

$db      = new Database($config['db']);
$request = Request::fromGlobals();
$auth    = new Auth($db, $config);
$router  = new Router();

// ── API publique ─────────────────────────────────────────────────────────────
$router->get('/api/edition',            ['Gfc\Controller\Api\EditionController', 'current']);
$router->get('/api/competitions',       ['Gfc\Controller\Api\CompetitionController', 'index']);
$router->get('/api/teams',              ['Gfc\Controller\Api\TeamController', 'index']);
$router->get('/api/teams/{id}',         ['Gfc\Controller\Api\TeamController', 'show']);
$router->get('/api/standings',          ['Gfc\Controller\Api\StandingController', 'index']);
$router->get('/api/matches',            ['Gfc\Controller\Api\MatchController', 'index']);
$router->get('/api/matches/{id}',       ['Gfc\Controller\Api\MatchController', 'show']);
$router->get('/api/players/top-scorers',['Gfc\Controller\Api\PlayerController', 'topScorers']);
$router->get('/api/news',               ['Gfc\Controller\Api\NewsController', 'index']);
$router->get('/api/media',              ['Gfc\Controller\Api\MediaController', 'index']);
$router->get('/api/palmares',           ['Gfc\Controller\Api\EditionController', 'honours']);
$router->get('/api/sponsors',           ['Gfc\Controller\Api\SponsorController', 'index']);
$router->post('/api/registrations',     ['Gfc\Controller\Api\RegistrationController', 'store']);

// ── Compte supporter ─────────────────────────────────────────────────────────
$router->post('/api/auth/otp',          ['Gfc\Controller\Api\AuthController', 'requestOtp']);
$router->post('/api/auth/verify',       ['Gfc\Controller\Api\AuthController', 'verifyOtp']);
$router->get('/api/me/favorites',       ['Gfc\Controller\Api\MeController', 'favorites']);
$router->post('/api/me/favorites',      ['Gfc\Controller\Api\MeController', 'toggleFavorite']);
$router->post('/api/me/devices',        ['Gfc\Controller\Api\MeController', 'registerDevice']);

// ── Écriture staff (feuille de match) ────────────────────────────────────────
$router->post('/api/matches/{id}/events',              ['Gfc\Controller\Api\MatchSheetController', 'addEvent']);
$router->delete('/api/matches/{id}/events/{eventId}',  ['Gfc\Controller\Api\MatchSheetController', 'deleteEvent']);
$router->post('/api/matches/{id}/status',              ['Gfc\Controller\Api\MatchSheetController', 'setStatus']);
$router->post('/api/matches/{id}/validate',            ['Gfc\Controller\Api\MatchSheetController', 'validateSheet']);

// ── Back office ──────────────────────────────────────────────────────────────
$router->get('/admin/login',   ['Gfc\Controller\Admin\AuthController', 'form']);
$router->post('/admin/login',  ['Gfc\Controller\Admin\AuthController', 'login']);
$router->post('/admin/logout', ['Gfc\Controller\Admin\AuthController', 'logout']);
$router->get('/admin/account',  ['Gfc\Controller\Admin\AccountController', 'form']);
$router->post('/admin/account', ['Gfc\Controller\Admin\AccountController', 'save']);
$router->get('/admin',              ['Gfc\Controller\Admin\DashboardController', 'index']);
$router->get('/admin/live',         ['Gfc\Controller\Admin\LiveController', 'index']);
$router->get('/admin/competitions', ['Gfc\Controller\Admin\CompetitionController', 'index']);
$router->post('/admin/competitions', ['Gfc\Controller\Admin\CompetitionController', 'save']);
$router->get('/admin/calendar',     ['Gfc\Controller\Admin\CalendarController', 'index']);
$router->post('/admin/calendar', ['Gfc\Controller\Admin\CalendarController', 'save']);
$router->get('/admin/standings',    ['Gfc\Controller\Admin\StandingController', 'index']);
$router->get('/admin/sanctions',    ['Gfc\Controller\Admin\SanctionController', 'index']);
$router->post('/admin/sanctions', ['Gfc\Controller\Admin\SanctionController', 'save']);
$router->get('/admin/teams',        ['Gfc\Controller\Admin\TeamController', 'index']);
$router->post('/admin/teams', ['Gfc\Controller\Admin\TeamController', 'save']);
$router->get('/admin/players',      ['Gfc\Controller\Admin\PlayerController', 'index']);
$router->post('/admin/players', ['Gfc\Controller\Admin\PlayerController', 'save']);
$router->get('/admin/users',        ['Gfc\Controller\Admin\UserController', 'index']);
$router->post('/admin/users', ['Gfc\Controller\Admin\UserController', 'save']);
$router->get('/admin/news',         ['Gfc\Controller\Admin\NewsController', 'index']);
$router->post('/admin/news', ['Gfc\Controller\Admin\NewsController', 'save']);
$router->get('/admin/tickets',      ['Gfc\Controller\Admin\TicketController', 'index']);
$router->get('/admin/sponsors',     ['Gfc\Controller\Admin\SponsorController', 'index']);
$router->post('/admin/sponsors', ['Gfc\Controller\Admin\SponsorController', 'save']);

// ── Application web publique (SPA légère servie par PHP) ─────────────────────
$router->get('/{any:.*}', ['Gfc\Controller\SiteController', 'index']);

try {
    $router->dispatch($request, $db, $auth, $config);
} catch (Throwable $e) {
    if (str_starts_with($request->path, '/api')) {
        Response::json(['error' => 'server_error', 'message' => $e->getMessage()], 500);
    }
    http_response_code(500);
    echo 'Erreur serveur.';
}
