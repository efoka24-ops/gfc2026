<?php
namespace Gfc;

/** Toutes les requêtes de lecture consommées par l'app mobile. */
final class Repo
{
    public static function competitions(int $season): array
    {
        return Database::all(
            'SELECT c.*, (SELECT COUNT(*) FROM competition_team ct WHERE ct.competition_id = c.id) AS team_count,
                    (SELECT COUNT(*) FROM matches m WHERE m.competition_id = c.id) AS match_count
             FROM competitions c WHERE c.season_id = ? ORDER BY c.sort_order',
            [$season]
        );
    }

    private const MATCH_SELECT = '
        SELECT m.id, m.competition_id, m.matchday, m.round_label, m.kickoff_at, m.venue, m.referee,
               m.attendance, m.status, m.minute, m.home_score, m.away_score,
               c.name AS competition, c.slug AS competition_slug,
               h.id AS home_id, h.name AS home_name, h.abbr AS home_abbr, h.logo AS home_logo, h.color AS home_color,
               a.id AS away_id, a.name AS away_name, a.abbr AS away_abbr, a.logo AS away_logo, a.color AS away_color
        FROM matches m
        JOIN competitions c ON c.id = m.competition_id
        JOIN teams h ON h.id = m.home_team_id
        JOIN teams a ON a.id = m.away_team_id';

    public static function matches(array $f): array
    {
        $w = ['c.season_id = ?'];
        $p = [$f['season']];
        if (!empty($f['competition'])) { $w[] = 'c.slug = ?';   $p[] = $f['competition']; }
        if (!empty($f['team']))        { $w[] = '(m.home_team_id = ? OR m.away_team_id = ?)'; $p[] = $f['team']; $p[] = $f['team']; }
        if (($f['scope'] ?? '') === 'upcoming') { $w[] = "m.status IN ('scheduled','live','halftime')"; }
        if (($f['scope'] ?? '') === 'results')  { $w[] = "m.status = 'finished'"; }
        $order = ($f['scope'] ?? '') === 'results' ? 'm.kickoff_at DESC' : 'm.kickoff_at ASC';
        $limit = (int) ($f['limit'] ?? 100);

        return Database::all(
            self::MATCH_SELECT . ' WHERE ' . implode(' AND ', $w) . " ORDER BY $order LIMIT $limit",
            $p
        );
    }

    public static function match(int $id): ?array
    {
        $match = Database::one(self::MATCH_SELECT . ' WHERE m.id = ?', [$id]);
        if (!$match) { return null; }

        $match['events'] = Database::all(
            "SELECT e.id, e.minute, e.type, e.detail, e.team_id, t.abbr AS team_abbr,
                    CONCAT(p.first_name,' ',p.last_name)   AS player,
                    CONCAT(r.first_name,' ',r.last_name)   AS related_player
             FROM match_events e
             LEFT JOIN teams t   ON t.id = e.team_id
             LEFT JOIN players p ON p.id = e.player_id
             LEFT JOIN players r ON r.id = e.related_player_id
             WHERE e.match_id = ? AND e.is_published = 1
             ORDER BY e.minute DESC, e.id DESC",
            [$id]
        );
        $match['stats'] = Database::all(
            'SELECT s.*, t.abbr FROM match_team_stats s JOIN teams t ON t.id = s.team_id WHERE s.match_id = ?',
            [$id]
        );
        $match['lineups'] = Database::all(
            "SELECT l.team_id, l.is_starter, p.jersey_number, p.position, p.position_label,
                    CONCAT(p.first_name,' ',p.last_name) AS name
             FROM lineups l JOIN players p ON p.id = l.player_id
             WHERE l.match_id = ? ORDER BY l.team_id, l.is_starter DESC, p.jersey_number",
            [$id]
        );
        return $match;
    }

    public static function standings(string $slug): array
    {
        return Database::all(
            'SELECT v.* FROM v_standings v
             JOIN competitions c ON c.id = v.competition_id
             WHERE c.slug = ?
             ORDER BY v.points DESC, v.goal_diff DESC, v.goals_for DESC, v.name ASC',
            [$slug]
        );
    }

    public static function teams(int $season): array
    {
        return Database::all(
            'SELECT DISTINCT t.* FROM teams t
             JOIN competition_team ct ON ct.team_id = t.id
             JOIN competitions c ON c.id = ct.competition_id
             WHERE c.season_id = ? AND t.is_active = 1 ORDER BY t.name',
            [$season]
        );
    }

    public static function team(int $id): ?array
    {
        $team = Database::one('SELECT * FROM teams WHERE id = ?', [$id]);
        if (!$team) { return null; }
        $team['squad'] = Database::all(
            'SELECT id, jersey_number, first_name, last_name, position, position_label,
                    TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) AS age, photo
             FROM players WHERE team_id = ? AND is_active = 1
             ORDER BY FIELD(position,"GB","DEF","MIL","ATT"), jersey_number',
            [$id]
        );
        $team['standing'] = Database::one(
            'SELECT v.* FROM v_standings v JOIN competitions c ON c.id = v.competition_id
             WHERE v.team_id = ? AND c.type = "league" LIMIT 1',
            [$id]
        );
        return $team;
    }

    public static function player(int $id): ?array
    {
        $player = Database::one(
            'SELECT p.*, TIMESTAMPDIFF(YEAR, p.birth_date, CURDATE()) AS age,
                    t.name AS team_name, t.abbr AS team_abbr, t.color AS team_color
             FROM players p JOIN teams t ON t.id = p.team_id WHERE p.id = ?',
            [$id]
        );
        if (!$player) { return null; }
        $player['stats'] = Database::one(
            "SELECT
               SUM(e.type IN ('goal','penalty'))                        AS goals,
               SUM(e.type = 'goal' AND e.related_player_id = p.id)      AS assists_placeholder,
               (SELECT COUNT(*) FROM match_events a WHERE a.related_player_id = ? AND a.type IN ('goal','penalty')) AS assists,
               SUM(e.type = 'yellow')                                   AS yellow_cards,
               SUM(e.type = 'red')                                      AS red_cards,
               (SELECT COUNT(*) FROM lineups l WHERE l.player_id = ?)    AS appearances,
               (SELECT COALESCE(SUM(l.minutes_played),0) FROM lineups l WHERE l.player_id = ?) AS minutes
             FROM players p LEFT JOIN match_events e ON e.player_id = p.id
             WHERE p.id = ? GROUP BY p.id",
            [$id, $id, $id, $id]
        );
        $player['goals_by_matchday'] = Database::all(
            "SELECT m.matchday, COUNT(*) AS goals
             FROM match_events e JOIN matches m ON m.id = e.match_id
             WHERE e.player_id = ? AND e.type IN ('goal','penalty')
             GROUP BY m.matchday ORDER BY m.matchday",
            [$id]
        );
        return $player;
    }

    public static function playerRankings(string $slug, string $metric, int $limit = 20): array
    {
        $types = match ($metric) {
            'assists' => "e.type IN ('goal','penalty') AND e.related_player_id = p.id",
            'cards'   => "e.type IN ('yellow','red') AND e.player_id = p.id",
            default   => "e.type IN ('goal','penalty') AND e.player_id = p.id",
        };
        $value = $metric === 'cards'
            ? "SUM(CASE WHEN e.type='red' THEN 3 ELSE 1 END)"
            : 'COUNT(*)';

        return Database::all(
            "SELECT p.id, CONCAT(p.first_name,' ',p.last_name) AS name, p.jersey_number,
                    t.name AS team, t.abbr, t.color,
                    $value AS value,
                    (SELECT COUNT(*) FROM lineups l WHERE l.player_id = p.id) AS appearances
             FROM players p
             JOIN teams t ON t.id = p.team_id
             JOIN match_events e ON $types
             JOIN matches m ON m.id = e.match_id
             JOIN competitions c ON c.id = m.competition_id
             WHERE c.slug = ?
             GROUP BY p.id ORDER BY value DESC, appearances ASC LIMIT $limit",
            [$slug]
        );
    }

    public static function teamRankings(string $slug): array
    {
        $rows = self::standings($slug);
        $attack  = $rows; usort($attack,  fn($a, $b) => $b['goals_for'] <=> $a['goals_for']);
        $defence = $rows; usort($defence, fn($a, $b) => $a['goals_against'] <=> $b['goals_against']);
        $possession = Database::all(
            'SELECT t.name, t.abbr, ROUND(AVG(s.possession)) AS value
             FROM match_team_stats s
             JOIN teams t ON t.id = s.team_id
             JOIN matches m ON m.id = s.match_id
             JOIN competitions c ON c.id = m.competition_id
             WHERE c.slug = ? GROUP BY t.id ORDER BY value DESC LIMIT 5',
            [$slug]
        );
        $attendance = Database::all(
            'SELECT t.name, t.abbr, ROUND(AVG(m.attendance)) AS value
             FROM matches m JOIN teams t ON t.id = m.home_team_id
             JOIN competitions c ON c.id = m.competition_id
             WHERE c.slug = ? AND m.attendance IS NOT NULL
             GROUP BY t.id ORDER BY value DESC LIMIT 5',
            [$slug]
        );
        return [
            'attack'     => array_slice($attack, 0, 5),
            'defence'    => array_slice($defence, 0, 5),
            'possession' => $possession,
            'attendance' => $attendance,
        ];
    }

    public static function news(int $limit = 20): array
    {
        return Database::all(
            'SELECT id, title, slug, category, excerpt, cover_image, published_at
             FROM news WHERE published_at IS NOT NULL AND published_at <= NOW()
             ORDER BY published_at DESC LIMIT ' . $limit
        );
    }

    public static function media(?string $type, int $limit = 40): array
    {
        $w = $type ? 'WHERE type = ?' : '';
        $p = $type ? [$type] : [];
        return Database::all(
            "SELECT id, type, title, url, thumbnail, duration_seconds, published_at
             FROM media $w ORDER BY published_at DESC LIMIT $limit",
            $p
        );
    }
}
