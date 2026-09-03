<?php
declare(strict_types=1);

namespace Gfc\Service;

use Gfc\Core\Database;

/**
 * Applique automatiquement les règles disciplinaires à la fin d'un match :
 * carton rouge = 1 match ferme, 3 jaunes cumulés = 1 match ferme.
 */
final class SanctionEngine
{
    private const YELLOW_THRESHOLD = 3;

    public function __construct(private Database $db)
    {
    }

    public function applyForMatch(int $matchId): array
    {
        $created = [];

        $reds = $this->db->all(
            'SELECT e.player_id, e.team_id FROM match_events e
              WHERE e.match_id = ? AND e.type = "red" AND e.player_id IS NOT NULL',
            [$matchId]
        );

        foreach ($reds as $r) {
            if ($this->exists((int) $r['player_id'], $matchId, 'red')) {
                continue;
            }
            $created[] = $this->db->insert('sanctions', [
                'player_id'    => (int) $r['player_id'],
                'team_id'      => (int) $r['team_id'],
                'match_id'     => $matchId,
                'type'         => 'red',
                'reason'       => 'Carton rouge direct',
                'games_banned' => 1,
                'status'       => 'applied',
                'decided_at'   => date('Y-m-d H:i:s'),
            ]);
        }

        $yellows = $this->db->all(
            'SELECT e.player_id, e.team_id, COUNT(*) AS total
               FROM match_events e
               JOIN matches m ON m.id = e.match_id
              WHERE e.type = "yellow" AND e.player_id IS NOT NULL
                AND m.competition_id = (SELECT competition_id FROM matches WHERE id = ?)
                AND m.status = "finished"
              GROUP BY e.player_id
             HAVING total % ? = 0',
            [$matchId, self::YELLOW_THRESHOLD]
        );

        foreach ($yellows as $y) {
            if ($this->exists((int) $y['player_id'], $matchId, 'yellow_accumulation')) {
                continue;
            }
            $created[] = $this->db->insert('sanctions', [
                'player_id'    => (int) $y['player_id'],
                'team_id'      => (int) $y['team_id'],
                'match_id'     => $matchId,
                'type'         => 'yellow_accumulation',
                'reason'       => $y['total'] . 'e carton jaune cumulé',
                'games_banned' => 1,
                'status'       => 'applied',
                'decided_at'   => date('Y-m-d H:i:s'),
            ]);
        }

        return $created;
    }

    private function exists(int $playerId, int $matchId, string $type): bool
    {
        return (bool) $this->db->value(
            'SELECT 1 FROM sanctions WHERE player_id = ? AND match_id = ? AND type = ?',
            [$playerId, $matchId, $type]
        );
    }
}
