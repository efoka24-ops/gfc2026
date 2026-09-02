<?php

namespace App\Http\Controllers\Api;

use App\Models\Competition;
use Illuminate\Http\JsonResponse;

class StandingsController
{
    /**
     * Get standings for all competitions or a specific one
     */
    public function index(?string $competition = null): JsonResponse
    {
        $query = \App\Models\Standing::query();

        if ($competition) {
            $comp = Competition::where('slug', $competition)->first();
            if (!$comp) {
                return response()->json(['error' => 'Competition not found'], 404);
            }
            $query->where('competition_id', $comp->id);
        }

        $standings = $query->orderBy('competition_id')
            ->orderByDesc('points')
            ->orderByDesc('goal_difference')
            ->orderByDesc('goals_for')
            ->get();

        return response()->json($standings);
    }

    /**
     * Get standings for a specific competition
     */
    public function show(Competition $competition): JsonResponse
    {
        $standings = \App\Models\Standing::where('competition_id', $competition->id)
            ->orderByDesc('points')
            ->orderByDesc('goal_difference')
            ->orderByDesc('goals_for')
            ->get();

        return response()->json([
            'competition' => $competition,
            'standings' => $standings
        ]);
    }
}
