<?php

namespace App\Services;

use App\Models\GameMatch;
use App\Models\Season;
use App\Models\Standing;

class StandingService
{
    /**
     * Recalcule tout le classement de la saison à partir des matchs terminés.
     */
    public function recalculate(int $seasonId): void
    {
        $season = Season::with([
            'matchdays.matches' => fn ($q) => $q->where('status', 'finished')
                                                  ->with(['homeTeam', 'awayTeam']),
        ])->findOrFail($seasonId);

        // Agrégation par équipe
        $stats = [];
        foreach ($season->matchdays as $matchday) {
            foreach ($matchday->matches as $match) {
                $this->applyMatch($stats, $match);
            }
        }

        // Tri : points DESC, différence de buts DESC, buts marqués DESC
        uasort($stats, fn ($a, $b) =>
            $b['points'] <=> $a['points']
            ?: $b['goal_difference'] <=> $a['goal_difference']
            ?: $b['goals_for'] <=> $a['goals_for']
        );

        // Persist
        $rank = 1;
        foreach ($stats as $teamId => $data) {
            Standing::updateOrCreate(
                ['season_id' => $seasonId, 'team_id' => $teamId],
                array_merge($data, ['rank' => $rank++])
            );
        }
    }

    private function applyMatch(array &$stats, GameMatch $match): void
    {
        $home = $match->home_team_id;
        $away = $match->away_team_id;

        if (! isset($stats[$home])) $stats[$home] = $this->emptyRow();
        if (! isset($stats[$away])) $stats[$away] = $this->emptyRow();

        $hg = $match->home_score;
        $ag = $match->away_score;

        $stats[$home]['played']++;
        $stats[$home]['goals_for']      += $hg;
        $stats[$home]['goals_against']  += $ag;

        $stats[$away]['played']++;
        $stats[$away]['goals_for']      += $ag;
        $stats[$away]['goals_against']  += $hg;

        if ($hg > $ag) {
            $stats[$home]['won']++;    $stats[$home]['points'] += 3;
            $stats[$away]['lost']++;
        } elseif ($hg < $ag) {
            $stats[$away]['won']++;    $stats[$away]['points'] += 3;
            $stats[$home]['lost']++;
        } else {
            $stats[$home]['drawn']++;  $stats[$home]['points']++;
            $stats[$away]['drawn']++;  $stats[$away]['points']++;
        }

        $stats[$home]['goal_difference'] = $stats[$home]['goals_for'] - $stats[$home]['goals_against'];
        $stats[$away]['goal_difference'] = $stats[$away]['goals_for'] - $stats[$away]['goals_against'];
    }

    private function emptyRow(): array
    {
        return [
            'played' => 0, 'won' => 0, 'drawn' => 0, 'lost' => 0,
            'goals_for' => 0, 'goals_against' => 0, 'goal_difference' => 0, 'points' => 0,
        ];
    }
}
