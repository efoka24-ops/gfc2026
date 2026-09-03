<?php
declare(strict_types=1);

namespace Gfc\Repository;

use Gfc\Core\Database;

final class ContentRepository
{
    public function __construct(private Database $db)
    {
    }

    public function publishedNews(int $limit = 12): array
    {
        return $this->db->all(
            'SELECT n.id, n.title, n.slug, n.category, n.excerpt, n.cover_path, n.published_at,
                    u.name AS author
               FROM news n LEFT JOIN users u ON u.id = n.author_id
              WHERE n.status = "published" AND n.published_at <= NOW()
              ORDER BY n.published_at DESC LIMIT ' . max(1, min($limit, 50))
        );
    }

    public function allNews(): array
    {
        return $this->db->all(
            'SELECT n.*, u.name AS author FROM news n
          LEFT JOIN users u ON u.id = n.author_id
              ORDER BY COALESCE(n.published_at, n.id) DESC'
        );
    }

    public function media(?int $editionId = null): array
    {
        return $editionId === null
            ? $this->db->all('SELECT * FROM media ORDER BY created_at DESC LIMIT 60')
            : $this->db->all('SELECT * FROM media WHERE edition_id = ? ORDER BY created_at DESC LIMIT 60', [$editionId]);
    }

    public function sponsors(bool $activeOnly = true): array
    {
        return $this->db->all(
            'SELECT * FROM sponsors' . ($activeOnly ? ' WHERE status = "active"' : '') . ' ORDER BY tier, name'
        );
    }

    public function honours(): array
    {
        return $this->db->all(
            'SELECT e.year, e.label AS edition, h.title,
                    COALESCE(t.name, h.team_label) AS team, h.player_label
               FROM honours h
               JOIN editions e ON e.id = h.edition_id
          LEFT JOIN teams t ON t.id = h.team_id
              ORDER BY e.year DESC, h.id'
        );
    }

    public function tickets(): array
    {
        return $this->db->all(
            'SELECT tt.*, m.kickoff_at, c.name AS competition,
                    h.name AS home, a.name AS away,
                    tt.sold * tt.price AS revenue
               FROM ticket_types tt
               JOIN matches m ON m.id = tt.match_id
               JOIN competitions c ON c.id = m.competition_id
               JOIN teams h ON h.id = m.home_team_id
               JOIN teams a ON a.id = m.away_team_id
              ORDER BY m.kickoff_at DESC'
        );
    }

    public function sanctions(): array
    {
        return $this->db->all(
            'SELECT s.*, t.name AS team,
                    CONCAT(p.first_name, " ", p.last_name) AS player,
                    m.kickoff_at, h.name AS home, a.name AS away
               FROM sanctions s
               JOIN teams t ON t.id = s.team_id
          LEFT JOIN players p ON p.id = s.player_id
          LEFT JOIN matches m ON m.id = s.match_id
          LEFT JOIN teams h ON h.id = m.home_team_id
          LEFT JOIN teams a ON a.id = m.away_team_id
              ORDER BY s.status = "open" DESC, s.id DESC'
        );
    }
}
