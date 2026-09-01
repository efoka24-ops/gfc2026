<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GameMatch;
use App\Models\MatchEvent;
use App\Models\Standing;
use App\Services\StandingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MatchController extends Controller
{
    public function __construct(private readonly StandingService $standingService) {}

    public function index(Request $request): JsonResponse
    {
        $query = GameMatch::with(['homeTeam', 'awayTeam', 'matchday'])
            ->orderBy('scheduled_at');

        if ($request->has('matchday_id')) {
            $query->where('matchday_id', $request->matchday_id);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->get());
    }

    public function show(GameMatch $match): JsonResponse
    {
        return response()->json(
            $match->load(['homeTeam', 'awayTeam', 'matchday', 'events.player', 'lineups.player'])
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'matchday_id'  => 'required|exists:matchdays,id',
            'home_team_id' => 'required|exists:teams,id|different:away_team_id',
            'away_team_id' => 'required|exists:teams,id',
            'scheduled_at' => 'required|date',
            'venue'        => 'nullable|string|max:200',
        ]);

        return response()->json(GameMatch::create($data), 201);
    }

    // ── Contrôle du match (LIVE) ──────────────────────────────

    public function start(GameMatch $match): JsonResponse
    {
        $match->update(['status' => 'live', 'minute' => 1]);
        broadcast(new \App\Events\MatchStatusChanged($match))->toOthers();

        return response()->json($match);
    }

    public function halfTime(GameMatch $match): JsonResponse
    {
        $match->update(['status' => 'half_time', 'minute' => 45]);
        broadcast(new \App\Events\MatchStatusChanged($match))->toOthers();

        return response()->json($match);
    }

    public function resume(GameMatch $match): JsonResponse
    {
        $match->update(['status' => 'live', 'minute' => 46]);
        broadcast(new \App\Events\MatchStatusChanged($match))->toOthers();

        return response()->json($match);
    }

    public function finish(GameMatch $match): JsonResponse
    {
        $match->update(['status' => 'finished', 'minute' => 90]);
        broadcast(new \App\Events\MatchStatusChanged($match))->toOthers();

        $this->standingService->recalculate($match->matchday->season_id);

        return response()->json($match);
    }

    public function updateMinute(Request $request, GameMatch $match): JsonResponse
    {
        $request->validate(['minute' => 'required|integer|min:1|max:120']);
        $match->update(['minute' => $request->minute]);
        broadcast(new \App\Events\MatchMinuteUpdated($match))->toOthers();

        return response()->json($match);
    }

    // ── Événements ────────────────────────────────────────────

    public function addEvent(Request $request, GameMatch $match): JsonResponse
    {
        $data = $request->validate([
            'type'            => 'required|in:goal,own_goal,yellow_card,red_card,yellow_red_card,substitution_in,substitution_out,penalty_scored,penalty_missed',
            'minute'          => 'required|integer|min:1|max:120',
            'extra_minute'    => 'nullable|integer|min:1|max:15',
            'player_id'       => 'nullable|exists:players,id',
            'assist_player_id'=> 'nullable|exists:players,id',
            'team_id'         => 'required|exists:teams,id',
            'description'     => 'nullable|string|max:500',
        ]);

        $data['match_id']       = $match->id;
        $data['recorded_by_id'] = $request->user()->id;

        $event = MatchEvent::create($data);

        // Mise à jour du score si but
        if (in_array($data['type'], ['goal', 'penalty_scored'])) {
            $this->updateScore($match, $data['team_id'], $data['type'] === 'own_goal');
        } elseif ($data['type'] === 'own_goal') {
            $oppositeTeamId = $match->home_team_id === $data['team_id']
                ? $match->away_team_id
                : $match->home_team_id;
            $this->updateScore($match, $oppositeTeamId);
        }

        broadcast(new \App\Events\MatchEventCreated($match, $event))->toOthers();

        return response()->json($event->load('player', 'assistPlayer'), 201);
    }

    public function deleteEvent(GameMatch $match, MatchEvent $event): JsonResponse
    {
        abort_if($event->match_id !== $match->id, 404);
        $event->delete();

        return response()->json(null, 204);
    }

    private function updateScore(GameMatch $match, int $scoringTeamId, bool $ownGoal = false): void
    {
        if ($match->home_team_id === $scoringTeamId) {
            $match->increment('home_score');
        } else {
            $match->increment('away_score');
        }
        $match->refresh();
        broadcast(new \App\Events\ScoreUpdated($match))->toOthers();
    }
}
