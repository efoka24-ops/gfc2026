<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\MatchController;
use Illuminate\Support\Facades\Route;

// ── Authentification ──────────────────────────────────────────
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',     [AuthController::class, 'me']);

    // ── Public (lecture) ──────────────────────────────────────
    Route::get('/teams',             [TeamController::class, 'index']);
    Route::get('/teams/{team}',      [TeamController::class, 'show']);
    Route::get('/matches',           [MatchController::class, 'index']);
    Route::get('/matches/{match}',   [MatchController::class, 'show']);

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

