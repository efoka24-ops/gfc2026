<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Team::where('active', true)->orderBy('name')->get()
        );
    }

    public function show(Team $team): JsonResponse
    {
        return response()->json($team->load('players'));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'          => 'required|string|max:150|unique:teams',
            'short_name'    => 'required|string|max:10',
            'logo_url'      => 'nullable|url|max:512',
            'city'          => 'nullable|string|max:100',
            'primary_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $team = Team::create($data);

        return response()->json($team, 201);
    }

    public function update(Request $request, Team $team): JsonResponse
    {
        $data = $request->validate([
            'name'          => 'sometimes|string|max:150|unique:teams,name,' . $team->id,
            'short_name'    => 'sometimes|string|max:10',
            'logo_url'      => 'nullable|url|max:512',
            'city'          => 'nullable|string|max:100',
            'primary_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'active'        => 'sometimes|boolean',
        ]);

        $team->update($data);

        return response()->json($team);
    }

    public function destroy(Team $team): JsonResponse
    {
        $team->update(['active' => false]);

        return response()->json(null, 204);
    }
}
