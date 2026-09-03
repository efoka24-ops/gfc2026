<?php
declare(strict_types=1);

namespace Gfc\Repository;

use Gfc\Core\Database;

final class MatchRepository
{
    private const SELECT = 'SELECT m.id, m.matchday, m.kickoff_at, m.status, m.minute,
                m.home_score, m.away_score, m.sheet_status,
                c.name AS competition, c.color AS competition_color,
                h.id AS home_id, h.name AS home, h.short_name AS home_short, h.color_primary AS home_color,
                a.id AS away_id, a.name AS away, a.short_name AS away_short, a.color_primary AS away_color,
                v.name AS venue, v.city AS venue_city,
                r.name AS referee
           FROM matches m
           JOIN competitions c ON c.id = m.competition_id
           JOIN teams h ON h.id = m.home_team_id
           JOIN teams a ON a.id = m.away_team_id
      LEFT JOIN venues v ON v.id = m.venue_id
      LEFT JOIN users r  ON r.id = m.referee_id';

    public function __construct(private Database $db)
    {
    }

    public function search(array $filters = [], int $limit = 100): array
    {
        $where  = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[]  = 'm.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['competition_id'])) {
            $where[]  = 'm.competition_id = ?';
            $params[] = (int) $filters['competition_id'];
        }
        if (!empty($filters['team_id'])) {
            $where[]  = '(m.home_team_id = ? OR m.away_team_id = ?)';
            $params[] = (int) $filters['team_id'];
            $params[] = (int) $filters['team_id'];
        }
        if (!empty($filters['from'])) {
            $where[]  = 'm.kickoff_at >= ?';
            $params[] = $filters['from'];
        }

        $sql = self::SELECT
             . ($where === [] ? '' : ' WHERE ' . implode(' AND ', $where))
             . ' ORDER BY FIELD(m.status, "live", "halftime", "scheduled", "finished", "postponed"),'
             . ' m.kickoff_at ASC LIMIT ' . max(1, min($limit, 500));

        return $this->db->all($sql, $params);
    }

    public function find(int $id): ?array
    {
        $match = $this->db->one(self::SELECT . ' WHERE m.id = ?', [$id]);
        if ($match === null) {
            return null;
        }

        $match['events'] = $this->events($id);
        $match['stats']  = $this->db->all(
            'SELECT s.*, t.name AS team_name FROM match_stats s
               JOIN teams t ON t.id = s.team_id WHERE s.match_id = ?',
            [$id]
        );
        $match['lineups'] = $this->db->all(
            'SELECT l.team_id, l.shirt_no, l.is_starter, l.is_captain,
                    CONCAT(p.first_name, " ", p.last_name) AS player, p.position
               FROM lineups l JOIN players p ON p.id = l.player_id
              WHERE l.match_id = ? ORDER BY l.team_id, l.is_starter DESC, l.shirt_no',
            [$id]
        );
        return $match;
    }

    public function events(int $matchId): array
    {
        return $this->db->all(
            'SELECT e.id, e.minute, e.type, e.note, e.team_id, t.name AS team_name,
                    CONCAT(p.first_name, " ", p.last_name)   AS player,
                    CONCAT(pi.first_name, " ", pi.last_name) AS player_in
               FROM match_events e
          LEFT JOIN teams t   ON t.id = e.team_id
          LEFT JOIN players p ON p.id = e.player_id
          LEFT JOIN players pi ON pi.id = e.player_in_id
              WHERE e.match_id = ?
              ORDER BY e.minute, e.id',
            [$matchId]
        );
    }

    /** Recalcule le score depuis les événements — la feuille de match est la source de vérité. */
    public function recomputeScore(int $matchId): array
    {
        $m = $this->db->one('SELECT home_team_id, away_team_id FROM matches WHERE id = ?', [$matchId]);
        if ($m === null) {
            return [0, 0];
        }

        $rows = $this->db->all(
            'SELECT team_id, type, COUNT(*) AS n FROM match_events
              WHERE match_id = ? AND type IN ("goal","penalty","own_goal")
              GROUP BY team_id, type',
            [$matchId]
        );

        $home = 0;
        $away = 0;
        foreach ($rows as $r) {
            $isHome = (int) $r['team_id'] === (int) $m['home_team_id'];
            // un but contre son camp compte pour l'adversaire
            $forHome = $r['type'] === 'own_goal' ? !$isHome : $isHome;
            $forHome ? $home += (int) $r['n'] : $away += (int) $r['n'];
        }

        $this->db->run(
            'UPDATE matches SET home_score = ?, away_score = ? WHERE id = ?',
            [$home, $away, $matchId]
        );
        return [$home, $away];
    }
}
