<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\MatchController;
use App\Http\Controllers\Api\CompetitionController;
use App\Http\Controllers\Api\StandingsController;
use Illuminate\Support\Facades\Route;

// ── Authentification ──────────────────────────────────────────
Route::post('/auth/login', [AuthController::class, 'login']);
// Route /auth/quick-token RETIREE le 2026-09-02.
// Elle delivrait un jeton administrateur complet a toute requete non
// authentifiee : n'importe qui sur Internet devenait admin. Pour se
// connecter, utiliser POST /auth/login avec de vrais identifiants.

// ── Routes publiques (lecture, sans auth) ────────────────────────
Route::get('/teams',             [TeamController::class, 'index']);
Route::get('/teams/{team}',      [TeamController::class, 'show']);
Route::get('/matches',           [MatchController::class, 'index']);
Route::get('/matches/{match}',   [MatchController::class, 'show']);
Route::get('/competitions',      [CompetitionController::class, 'index']);
Route::get('/competitions/{competition}', [CompetitionController::class, 'show']);
Route::get('/standings',         [StandingsController::class, 'index']);
Route::get('/standings/{competition}', [StandingsController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',     [AuthController::class, 'me']);

    // ── Secrétaire / Admin (saisie terrain) ───────────────────
    Route::middleware('role:admin,secretary')->group(function () {
        // Matchs — contrôle live
        Route::post('/matches/{match}/start',       [MatchController::class, 'start']);
        Route::post('/matches/{match}/half-time',   [MatchController::class, 'halfTime']);
        Route::post('/matches/{match}/resume',      [MatchController::class, 'resume']);
        Route::post('/matches/{match}/finish',      [MatchController::class, 'finish']);
        Route::patch('/matches/{match}/minute',     [MatchController::class, 'updateMinute']);

        // Événements de match
        Route::post('/matches/{match}/events',              [MatchController::class, 'addEvent']);
        Route::delete('/matches/{match}/events/{event}',   [MatchController::class, 'deleteEvent']);
    });

    // ── Admin uniquement (CRUD complet) ───────────────────────
    Route::middleware('role:admin')->group(function () {
        Route::post('/teams',           [TeamController::class, 'store']);
        Route::put('/teams/{team}',     [TeamController::class, 'update']);
        Route::delete('/teams/{team}',  [TeamController::class, 'destroy']);

        Route::post('/matches',         [MatchController::class, 'store']);
    });
});

