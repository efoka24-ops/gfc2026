<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\Season;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CompetitionController extends Controller
{
    /**
     * Liste des competitions.
     */
    public function index(): JsonResponse
    {
        return response()->json(
            Competition::orderBy('name')->get()
        );
    }

    /**
     * Fiche d'une competition.
     */
    public function show(Competition $competition): JsonResponse
    {
        return response()->json($competition);
    }

    /**
     * Cree une competition dans la saison active (ou celle precisee).
     *
     * Types : league (championnat), knockout (Grand Prix Gabriel MBAIROBE a
     * elimination directe), single_match (Super Coupe).
     * Reserve a l'administrateur.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'      => 'required|string|max:150',
            'slug'      => 'nullable|string|max:50|unique:competitions,slug',
            'type'      => ['required', Rule::in(['league', 'knockout', 'single_match'])],
            'season_id' => 'nullable|exists:seasons,id',
            'active'    => 'sometimes|boolean',
        ]);

        $data['slug']      ??= Str::slug($data['name']);
        $data['season_id'] ??= optional(Season::where('active', true)->first())->id;

        if (! $data['season_id']) {
            return response()->json(
                ['message' => 'Aucune saison active. Precisez season_id.'],
                422
            );
        }

        $competition = Competition::create($data);

        return response()->json($competition, 201);
    }
}
