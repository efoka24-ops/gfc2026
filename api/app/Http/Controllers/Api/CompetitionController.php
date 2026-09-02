<?php

namespace App\Http\Controllers\Api;

use App\Models\Competition;
use Illuminate\Http\JsonResponse;

class CompetitionController
{
    /**
     * Get all competitions
     */
    public function index(): JsonResponse
    {
        $competitions = Competition::orderBy('name')->get();

        return response()->json($competitions);
    }

    /**
     * Get single competition
     */
    public function show(Competition $competition): JsonResponse
    {
        return response()->json($competition);
    }
}
