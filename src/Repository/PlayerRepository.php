<?php
declare(strict_types=1);

namespace Gfc\Repository;

use Gfc\Core\Database;

final class PlayerRepository
{
    public function __construct(private Database $db)
    {
    }

    public function topScorers(int $editionId, int $limit = 20): array
    {
        return $this->db->all(
            'SELECT s.* FROM v_top_scorers s
               JOIN teams t ON t.id = s.team_id
              WHERE t.edition_id = ? AND s.goals > 0
              ORDER BY s.goals DESC, s.player_name ASC
              LIMIT ' . max(1, min($limit, 100)),
            [$editionId]
        );
    }

    public function all(int $editionId): array
    {
        return $this->db->all(
            'SELECT s.*, t.name AS team FROM v_top_scorers s
               JOIN teams t ON t.id = s.team_id
              WHERE t.edition_id = ?
              ORDER BY s.goals DESC, s.player_name',
            [$editionId]
        );
    }

    public function forTeam(int $teamId): array
    {
        return $this->db->all(
            'SELECT * FROM players WHERE team_id = ? ORDER BY position, last_name',
            [$teamId]
        );
    }
}
