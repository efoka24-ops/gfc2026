<?php
declare(strict_types=1);

namespace Gfc\Repository;

use Gfc\Core\Database;

final class TeamRepository
{
    public function __construct(private Database $db)
    {
    }

    public function forEdition(int $editionId): array
    {
        return $this->db->all(
            'SELECT t.id, t.name, t.short_name, t.city, t.coach, t.founded,
                    t.color_primary, t.color_secondary, t.logo_path, t.status,
                    (SELECT COUNT(*) FROM players p WHERE p.team_id = t.id) AS squad_size,
                    (SELECT COUNT(*) FROM players p WHERE p.team_id = t.id
                       AND p.license_status <> "valid")                     AS licenses_pending
               FROM teams t
              WHERE t.edition_id = ?
              ORDER BY t.name',
            [$editionId]
        );
    }

    public function find(int $id): ?array
    {
        $team = $this->db->one('SELECT * FROM teams WHERE id = ?', [$id]);
        if ($team === null) {
            return null;
        }

        $team['squad'] = $this->db->all(
            'SELECT id, first_name, last_name, position, shirt_no, license_status
               FROM players WHERE team_id = ? ORDER BY position, last_name',
            [$id]
        );
        $team['honours'] = $this->db->all(
            'SELECT h.title, e.year FROM honours h
               JOIN editions e ON e.id = h.edition_id
              WHERE h.team_id = ? ORDER BY e.year DESC',
            [$id]
        );
        $team['form'] = $this->db->all(
            'SELECT m.id, m.kickoff_at, m.home_team_id, m.away_team_id,
                    m.home_score, m.away_score
               FROM matches m
              WHERE m.status = "finished" AND (m.home_team_id = ? OR m.away_team_id = ?)
              ORDER BY m.kickoff_at DESC LIMIT 5',
            [$id, $id]
        );
        return $team;
    }

    public function pendingDossiers(int $editionId): array
    {
        return $this->db->all(
            'SELECT id, name, status FROM teams
              WHERE edition_id = ? AND status <> "validated" ORDER BY name',
            [$editionId]
        );
    }
}
