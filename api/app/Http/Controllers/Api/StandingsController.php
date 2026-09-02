<?php

namespace App\Http\Controllers\Api;

use App\Models\Competition;
use App\Models\Standing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StandingsController
{
    /**
     * Classement, filtrable par competition (?competition=slug).
     *
     * Charge l'equipe (nom, sigle, couleur) : le classement doit afficher les
     * noms, pas seulement des identifiants. Le rang est recalcule a la lecture
     * dans l'ordre points / difference / buts pour, afin d'etre coherent meme
     * si le rang stocke n'a pas encore ete recalcule.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Standing::query()->with('team:id,name,short_name,primary_color,logo_url');

        $slug = $request->query('competition');
        if ($slug) {
            $comp = Competition::where('slug', $slug)->first();
            if (! $comp) {
                return response()->json(['error' => 'Competition introuvable'], 404);
            }
            $query->where('competition_id', $comp->id);
        }

        $standings = $query
            ->orderByDesc('points')
            ->orderByDesc('goal_difference')
            ->orderByDesc('goals_for')
            ->get()
            ->values();

        // Rang effectif = position dans le tri (le rang stocke peut valoir 0
        // tant qu'aucun match n'a ete joue).
        $standings->transform(function ($row, $i) {
            $row->rank = $i + 1;
            return $row;
        });

        return response()->json($standings);
    }

    public function show(Competition $competition): JsonResponse
    {
        $standings = Standing::where('competition_id', $competition->id)
            ->with('team:id,name,short_name,primary_color,logo_url')
            ->orderByDesc('points')
            ->orderByDesc('goal_difference')
            ->orderByDesc('goals_for')
            ->get()
            ->values();

        $standings->transform(function ($row, $i) {
            $row->rank = $i + 1;
            return $row;
        });

        return response()->json([
            'competition' => $competition,
            'standings'   => $standings,
        ]);
    }
}
