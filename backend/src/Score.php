<?php
namespace Gfc;

/** Recalcule le score d'un match à partir de ses événements publiés. */
final class Score
{
    public static function recalculate(int $matchId): void
    {
        $m = Database::one('SELECT home_team_id, away_team_id FROM matches WHERE id = ?', [$matchId]);
        if (!$m) { return; }

        $rows = Database::all(
            "SELECT team_id, type, COUNT(*) AS n FROM match_events
             WHERE match_id = ? AND is_published = 1 AND type IN ('goal','penalty','own_goal')
             GROUP BY team_id, type",
            [$matchId]
        );

        $home = 0; $away = 0;
        foreach ($rows as $r) {
            $isHome = (int) $r['team_id'] === (int) $m['home_team_id'];
            // un csc compte pour l'adversaire
            if ($r['type'] === 'own_goal') { $isHome = !$isHome; }
            if ($isHome) { $home += (int) $r['n']; } else { $away += (int) $r['n']; }
        }

        Database::run('UPDATE matches SET home_score = ?, away_score = ? WHERE id = ?', [$home, $away, $matchId]);
    }
}
