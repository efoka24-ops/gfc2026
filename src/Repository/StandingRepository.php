<?php
declare(strict_types=1);

namespace Gfc\Repository;

use Gfc\Core\Database;

final class StandingRepository
{
    public function __construct(private Database $db)
    {
    }

    /**
     * Classement calculé par la vue v_standings, trié points > différence > buts pour.
     * Les équipes sans match joué sont ajoutées à zéro pour ne jamais disparaître.
     */
    public function forCompetition(int $competitionId): array
    {
        $rows = $this->db->all(
            'SELECT * FROM v_standings WHERE competition_id = ?
              ORDER BY points DESC, goal_diff DESC, goals_for DESC, team_name ASC',
            [$competitionId]
        );

        $seen = array_column($rows, 'team_id');
        $rest = $this->db->all(
            'SELECT t.id AS team_id, t.name AS team_name, t.short_name, t.city, t.color_primary
               FROM team_competition tc
               JOIN teams t ON t.id = tc.team_id
              WHERE tc.competition_id = ?' .
            ($seen === [] ? '' : ' AND t.id NOT IN (' . implode(',', array_map('intval', $seen)) . ')') .
            ' ORDER BY t.name',
            [$competitionId]
        );

        foreach ($rest as $r) {
            $rows[] = $r + [
                'competition_id' => $competitionId,
                'played' => 0, 'won' => 0, 'drawn' => 0, 'lost' => 0,
                'goals_for' => 0, 'goals_against' => 0, 'goal_diff' => 0, 'points' => 0,
            ];
        }

        $slots = (int) $this->db->value('SELECT qualify_slots FROM competitions WHERE id = ?', [$competitionId]);
        foreach ($rows as $i => &$row) {
            $row['rank'] = $i + 1;
            $row['zone'] = $i < $slots ? 'qualify' : ($i < $slots + 2 ? 'playoff' : '');
        }
        return $rows;
    }
}
