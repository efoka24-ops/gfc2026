<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\MatchController;
use App\Http\Controllers\Api\CompetitionController;
use App\Http\Controllers\Api\StandingsController;
use App\Http\Controllers\Api\PlayerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes API — Garoua Football Challenge
|--------------------------------------------------------------------------
|
| Lecture : PUBLIQUE (l'application mobile affiche calendrier, scores,
|           classement et effectifs sans compte).
| Ecriture : jeton Sanctum + role.
|
| NOTE HEBERGEMENT : le serveur bloque les methodes HTTP PUT et DELETE
| (403 Apache/ModSecurity). Les clients doivent donc envoyer un POST en
| ajoutant `_method=PUT` ou `_method=DELETE` (ou l'en-tete
| X-HTTP-Method-Override) — Laravel route alors vers la methode voulue.
| Les routes restent declarees en PUT/DELETE.
|
| Aucune route ne delivre de jeton sans identifiants (pas de quick-token).
|
*/

// ── Authentification ──────────────────────────────────────────
Route::post('/auth/login', [AuthController::class, 'login']);

// ── Lecture publique (sans authentification) ──────────────────
Route::get('/teams',                      [TeamController::class, 'index']);
Route::get('/teams/{team}',               [TeamController::class, 'show']);
Route::get('/matches',                    [MatchController::class, 'index']);
Route::get('/matches/{match}',            [MatchController::class, 'show']);
Route::get('/competitions',               [CompetitionController::class, 'index']);
Route::get('/competitions/{competition}', [CompetitionController::class, 'show']);
Route::get('/matchdays',                  [MatchController::class, 'matchdays']);
Route::get('/standings',                  [StandingsController::class, 'index']);
Route::get('/standings/{competition}',    [StandingsController::class, 'show']);
Route::get('/players',                    [PlayerController::class, 'index']);
Route::get('/players/{player}',           [PlayerController::class, 'show']);

// ── Ecriture (jeton requis) ───────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);

    // ── Secretaire / Admin : saisie terrain ───────────────────
    Route::middleware('role:admin,secretary')->group(function () {
        Route::post('/matches/{match}/start',     [MatchController::class, 'start']);
        Route::post('/matches/{match}/half-time',  [MatchController::class, 'halfTime']);
        Route::post('/matches/{match}/resume',     [MatchController::class, 'resume']);
        Route::post('/matches/{match}/finish',     [MatchController::class, 'finish']);
        Route::patch('/matches/{match}/minute',    [MatchController::class, 'updateMinute']);

        Route::post('/matches/{match}/events',            [MatchController::class, 'addEvent']);
        Route::delete('/matches/{match}/events/{event}',  [MatchController::class, 'deleteEvent']);
    });

    // ── Admin : CRUD complet ──────────────────────────────────
    Route::middleware('role:admin')->group(function () {
        // Equipes
        Route::post('/teams',          [TeamController::class, 'store']);
        Route::put('/teams/{team}',    [TeamController::class, 'update']);
        Route::delete('/teams/{team}', [TeamController::class, 'destroy']);

        // Joueurs
        Route::post('/players',            [PlayerController::class, 'store']);
        Route::put('/players/{player}',    [PlayerController::class, 'update']);
        Route::delete('/players/{player}', [PlayerController::class, 'destroy']);

        // Competitions et journees (montage du calendrier)
        Route::post('/competitions',  [CompetitionController::class, 'store']);
        Route::post('/matchdays',     [MatchController::class, 'storeMatchday']);

        // Matchs
        Route::post('/matches',           [MatchController::class, 'store']);
        Route::delete('/matches/{match}', [MatchController::class, 'destroy']);
    });
});
