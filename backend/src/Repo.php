<?php
namespace Gfc;

/**
 * Acces aux donnees : lectures consommees par l'application mobile et le
 * back-office, ecritures de la saisie live.
 *
 * Regle absolue : toute valeur variable passe par un parametre lie. Les seules
 * interpolations tolerees dans une requete sont des entiers deja castes
 * (LIMIT), MySQL n'acceptant pas de parametre lie a cet endroit.
 */
final class Repo
{
    // ====================================================== COMPETITIONS

    public static function competitions(int $season): array
    {
        return Database::all(
            'SELECT c.*,
                    (SELECT COUNT(*) FROM competition_team ct WHERE ct.competition_id = c.id) AS team_count,
                    (SELECT COUNT(*) FROM matches m WHERE m.competition_id = c.id) AS match_count
               FROM competitions c
              WHERE c.season_id = ?
           ORDER BY c.sort_order',
            [$season]
        );
    }

    // ============================================================ MATCHS

    private const MATCH_SELECT = '
        SELECT m.id, m.competition_id, m.matchday, m.round_label, m.kickoff_at, m.venue, m.referee,
               m.attendance, m.status, m.minute, m.home_score, m.away_score, m.home_pens, m.away_pens,
               m.home_team_id, m.away_team_id,
               c.name AS competition, c.slug AS competition_slug, c.type AS competition_type,
               h.id AS home_id, h.name AS home_name, h.abbr AS home_abbr, h.logo AS home_logo, h.color AS home_color,
               a.id AS away_id, a.name AS away_name, a.abbr AS away_abbr, a.logo AS away_logo, a.color AS away_color
          FROM matches m
          JOIN competitions c ON c.id = m.competition_id
          JOIN teams h ON h.id = m.home_team_id
          JOIN teams a ON a.id = m.away_team_id';

    public static function matches(array $f): array
    {
        $where  = ['c.season_id = ?'];
        $params = [(int) $f['season']];

        if (!empty($f['competition'])) {
            $where[]  = 'c.slug = ?';
            $params[] = $f['competition'];
        }
        if (!empty($f['team'])) {
            $where[]  = '(m.home_team_id = ? OR m.away_team_id = ?)';
            $params[] = (int) $f['team'];
            $params[] = (int) $f['team'];
        }
        if (($f['scope'] ?? '') === 'upcoming') {
            $where[] = "m.status IN ('scheduled','live','halftime','postponed')";
        }
        if (($f['scope'] ?? '') === 'results') {
            $where[] = "m.status = 'finished'";
        }

        $order = ($f['scope'] ?? '') === 'results' ? 'm.kickoff_at DESC' : 'm.kickoff_at ASC';
        $limit = max(1, min(200, (int) ($f['limit'] ?? 100)));

        return Database::all(
            self::MATCH_SELECT . ' WHERE ' . implode(' AND ', $where) . " ORDER BY $order LIMIT $limit",
            $params
        );
    }

    public static function match(int $id): ?array
    {
        $match = Database::one(self::MATCH_SELECT . ' WHERE m.id = ?', [$id]);
        if (!$match) {
            return null;
        }

        // Seuls les evenements publies sortent par l'API publique (invariant I3).
        $match['events'] = Database::all(
            "SELECT e.id, e.minute, e.type, e.detail, e.team_id, t.abbr AS team_abbr,
                    e.player_id, e.related_player_id,
                    CONCAT(p.first_name,' ',p.last_name) AS player,
                    CONCAT(r.first_name,' ',r.last_name) AS related_player
               FROM match_events e
          LEFT JOIN teams   t ON t.id = e.team_id
          LEFT JOIN players p ON p.id = e.player_id
          LEFT JOIN players r ON r.id = e.related_player_id
              WHERE e.match_id = ? AND e.is_published = 1
           ORDER BY e.minute DESC, e.id DESC",
            [$id]
        );

        $match['stats'] = Database::all(
            'SELECT s.*, t.abbr
               FROM match_team_stats s
               JOIN teams t ON t.id = s.team_id
              WHERE s.match_id = ?',
            [$id]
        );

        $match['lineups'] = Database::all(
            "SELECT l.team_id, l.is_starter, l.player_id, p.jersey_number, p.position, p.position_label,
                    CONCAT(p.first_name,' ',p.last_name) AS name
               FROM lineups l
               JOIN players p ON p.id = l.player_id
              WHERE l.match_id = ?
           ORDER BY l.team_id, l.is_starter DESC, p.jersey_number",
            [$id]
        );

        return $match;
    }

    /** Un match est-il en cours ? Determine la politique de cache (principe I). */
    public static function estEnDirect(array $match): bool
    {
        return in_array($match['status'] ?? '', ['live', 'halftime'], true);
    }

    // ======================================================== CLASSEMENT

    /**
     * Classement d'une competition, ordre deterministe (decision D4) : points,
     * puis difference de buts, puis buts marques, puis nom.
     *
     * Le rang n'est pas stocke : c'est la position dans ce tri. La zone de
     * qualification vient de la configuration, jamais d'une valeur en dur —
     * une edition a 12 equipes ne doit pas demander de toucher au code.
     */
    public static function standings(string $slug, int $placesQualification = 0): array
    {
        $rows = Database::all(
            'SELECT v.* FROM v_standings v
               JOIN competitions c ON c.id = v.competition_id
              WHERE c.slug = ?
           ORDER BY v.points DESC, v.goal_diff DESC, v.goals_for DESC, v.name ASC',
            [$slug]
        );

        foreach ($rows as $i => &$row) {
            $row['rank'] = $i + 1;
            $row['zone'] = ($placesQualification > 0 && $row['rank'] <= $placesQualification)
                ? 'qualification'
                : null;
        }

        return $rows;
    }

    // =========================================================== EQUIPES

    public static function teams(int $season): array
    {
        return Database::all(
            'SELECT DISTINCT t.* FROM teams t
               JOIN competition_team ct ON ct.team_id = t.id
               JOIN competitions c ON c.id = ct.competition_id
              WHERE c.season_id = ? AND t.is_active = 1
           ORDER BY t.name',
            [$season]
        );
    }

    public static function team(int $id, int $placesQualification = 0): ?array
    {
        $team = Database::one('SELECT * FROM teams WHERE id = ?', [$id]);
        if (!$team) {
            return null;
        }

        $team['squad'] = Database::all(
            "SELECT id, jersey_number, first_name, last_name, position, position_label,
                    TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) AS age, photo
               FROM players
              WHERE team_id = ? AND is_active = 1
           ORDER BY FIELD(position,'GB','DEF','MIL','ATT'), jersey_number",
            [$id]
        );

        // Position au classement du championnat : reprise du meme tri que
        // standings(), pour qu'une equipe ne soit jamais 3e ici et 4e la-bas.
        $championnat = Database::one("SELECT slug FROM competitions WHERE type = 'league' LIMIT 1");
        $team['standing'] = null;
        if ($championnat) {
            foreach (self::standings($championnat['slug'], $placesQualification) as $row) {
                if ((int) $row['team_id'] === $id) {
                    $team['standing'] = $row;
                    break;
                }
            }
        }

        return $team;
    }

    // ============================================== ESPACE OPERATEUR (US8)

    /**
     * Les matchs qu'un operateur peut saisir : ceux qui restent a jouer ou sont
     * en cours. Un arbitre ne voit que les rencontres ou il est designe ;
     * admin et secretaire voient tout (FR-037).
     */
    public static function matchsOperateur(array $user): array
    {
        $where  = ["m.status IN ('scheduled','live','halftime')"];
        $params = [];

        if ($user['role'] === Auth::ROLE_ARBITRE) {
            $where[]  = 'm.referee = ?';
            $params[] = $user['name'];
        }

        $rows = Database::all(
            self::MATCH_SELECT . ' WHERE ' . implode(' AND ', $where) . ' ORDER BY m.kickoff_at ASC LIMIT 50',
            $params
        );

        // lineups_ready : les deux compositions sont-elles saisies ? L'espace
        // operateur s'en sert pour signaler ce qui reste a faire avant le coup
        // d'envoi.
        foreach ($rows as &$row) {
            $compte = Database::one(
                'SELECT COUNT(DISTINCT team_id) AS n FROM lineups WHERE match_id = ?',
                [(int) $row['id']]
            );
            $row['lineups_ready'] = ((int) ($compte['n'] ?? 0)) >= 2;
        }

        return $rows;
    }

    /** Effectifs des deux equipes d'un match, pour composer (FR-038). */
    public static function effectifsDuMatch(int $matchId): ?array
    {
        $match = Database::one('SELECT home_team_id, away_team_id FROM matches WHERE id = ?', [$matchId]);
        if (!$match) {
            return null;
        }

        $effectif = static fn(int $teamId): array => Database::all(
            "SELECT id, jersey_number, position, position_label,
                    CONCAT(first_name,' ',last_name) AS name
               FROM players
              WHERE team_id = ? AND is_active = 1
           ORDER BY FIELD(position,'GB','DEF','MIL','ATT'), jersey_number",
            [$teamId]
        );

        return [
            'home' => $effectif((int) $match['home_team_id']),
            'away' => $effectif((int) $match['away_team_id']),
        ];
    }

    /**
     * Enregistre la composition d'une equipe. Idempotent : remplace
     * integralement ce qui existait pour cette equipe sur ce match.
     *
     * @param array<int,array{player_id:int,is_starter:bool}> $joueurs
     * @throws \RuntimeException si un joueur n'appartient pas a l'equipe
     */
    public static function enregistrerComposition(int $matchId, int $teamId, array $joueurs): void
    {
        $match = Database::one('SELECT home_team_id, away_team_id FROM matches WHERE id = ?', [$matchId]);
        if (!$match) {
            throw new \RuntimeException('Match introuvable.');
        }
        if ($teamId !== (int) $match['home_team_id'] && $teamId !== (int) $match['away_team_id']) {
            throw new \RuntimeException('Cette equipe ne joue pas ce match.');
        }

        $ids = [];
        foreach ($joueurs as $j) {
            $pid = (int) ($j['player_id'] ?? 0);
            if ($pid <= 0) {
                throw new \RuntimeException('Joueur invalide dans la composition.');
            }
            if (isset($ids[$pid])) {
                throw new \RuntimeException('Un joueur figure deux fois dans la composition.');
            }
            $ids[$pid] = true;
        }

        // Un joueur ne peut etre aligne que dans son equipe : on verifie en base
        // plutot que de faire confiance a ce que l'appareil envoie.
        foreach (array_keys($ids) as $pid) {
            $ok = Database::one(
                'SELECT id FROM players WHERE id = ? AND team_id = ? AND is_active = 1',
                [$pid, $teamId]
            );
            if (!$ok) {
                throw new \RuntimeException('Un joueur choisi n\'appartient pas a cette equipe.');
            }
        }

        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            Database::run('DELETE FROM lineups WHERE match_id = ? AND team_id = ?', [$matchId, $teamId]);
            foreach ($joueurs as $j) {
                Database::run(
                    'INSERT INTO lineups (match_id, team_id, player_id, is_starter) VALUES (?,?,?,?)',
                    [$matchId, $teamId, (int) $j['player_id'], !empty($j['is_starter']) ? 1 : 0]
                );
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /** Statistiques de rencontre, saisies apres le match (FR-040). Idempotent. */
    public static function enregistrerStatsMatch(int $matchId, int $teamId, array $s): void
    {
        Database::run(
            'INSERT INTO match_team_stats
                 (match_id, team_id, possession, shots, shots_on_target, corners, fouls, offsides)
             VALUES (?,?,?,?,?,?,?,?)
             ON DUPLICATE KEY UPDATE
                 possession = VALUES(possession), shots = VALUES(shots),
                 shots_on_target = VALUES(shots_on_target), corners = VALUES(corners),
                 fouls = VALUES(fouls), offsides = VALUES(offsides)',
            [
                $matchId, $teamId,
                isset($s['possession']) ? (int) $s['possession'] : null,
                (int) ($s['shots'] ?? 0),
                (int) ($s['shots_on_target'] ?? 0),
                (int) ($s['corners'] ?? 0),
                (int) ($s['fouls'] ?? 0),
                (int) ($s['offsides'] ?? 0),
            ]
        );
    }

    // ====================================================== EVENEMENTS

    public const TYPES_EVENEMENT = [
        'kickoff', 'goal', 'own_goal', 'penalty', 'penalty_missed',
        'yellow', 'red', 'sub', 'halftime', 'fulltime', 'var',
    ];

    /**
     * Ajoute un evenement et recalcule le score dans la meme transaction
     * (invariant I1).
     *
     * Idempotence : si l'appareil fournit un client_ref deja enregistre pour ce
     * match, l'evenement existant est renvoye au lieu d'en creer un second.
     * C'est ce qui permet a l'operateur de rejouer une saisie faite hors reseau
     * sans compter un but deux fois (FR-041).
     *
     * @return array{event:array,cree:bool}
     * @throws \RuntimeException sur donnee refusee (a traduire en 422)
     */
    public static function ajouterEvenement(int $matchId, array $body, int $userId): array
    {
        $type = (string) ($body['type'] ?? '');
        if (!in_array($type, self::TYPES_EVENEMENT, true)) {
            throw new \RuntimeException('Type d\'evenement inconnu.');
        }

        $match = Database::one('SELECT home_team_id, away_team_id FROM matches WHERE id = ?', [$matchId]);
        if (!$match) {
            throw new \RuntimeException('Match introuvable.');
        }

        $teamId   = isset($body['team_id']) ? (int) $body['team_id'] : null;
        $playerId = isset($body['player_id']) ? (int) $body['player_id'] : null;
        $assistId = isset($body['related_player_id']) ? (int) $body['related_player_id'] : null;

        if ($teamId !== null
            && $teamId !== (int) $match['home_team_id']
            && $teamId !== (int) $match['away_team_id']) {
            throw new \RuntimeException('Cette equipe ne joue pas ce match.');
        }

        // Le joueur doit appartenir a l'equipe portee par l'evenement : c'est ce
        // qui garantit qu'un but ne peut pas etre attribue au mauvais effectif.
        foreach ([$playerId, $assistId] as $pid) {
            if ($pid !== null && $teamId !== null) {
                $ok = Database::one('SELECT id FROM players WHERE id = ? AND team_id = ?', [$pid, $teamId]);
                if (!$ok) {
                    throw new \RuntimeException('Le joueur choisi n\'appartient pas a cette equipe.');
                }
            }
        }

        $clientRef = $body['client_ref'] ?? null;
        if ($clientRef !== null) {
            $clientRef = substr((string) $clientRef, 0, 36);
            $existant = Database::one(
                'SELECT * FROM match_events WHERE match_id = ? AND client_ref = ?',
                [$matchId, $clientRef]
            );
            if ($existant) {
                return ['event' => $existant, 'cree' => false];
            }
        }

        $minute = max(0, min(200, (int) ($body['minute'] ?? 0)));

        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            Database::run(
                'INSERT INTO match_events
                     (match_id, team_id, player_id, related_player_id, minute, type, detail,
                      is_published, client_ref, created_by)
                 VALUES (?,?,?,?,?,?,?,?,?,?)',
                [
                    $matchId, $teamId, $playerId, $assistId, $minute, $type,
                    $body['detail'] ?? null,
                    array_key_exists('is_published', $body) ? (int) (bool) $body['is_published'] : 1,
                    $clientRef, $userId,
                ]
            );
            $eventId = (int) $pdo->lastInsertId();

            Score::recompute($matchId);

            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            // Une collision d'unicite signifie qu'une transmission concurrente a
            // gagne la course : l'evenement existe, ce n'est pas une erreur.
            if ($clientRef !== null) {
                $existant = Database::one(
                    'SELECT * FROM match_events WHERE match_id = ? AND client_ref = ?',
                    [$matchId, $clientRef]
                );
                if ($existant) {
                    return ['event' => $existant, 'cree' => false];
                }
            }
            throw $e;
        }

        $event = Database::one('SELECT * FROM match_events WHERE id = ?', [$eventId]);
        return ['event' => $event, 'cree' => true];
    }

    /**
     * Supprime un evenement et recalcule le score. La suppression est
     * journalisee pour que la correction d'un score reste auditable (ecart E4).
     */
    public static function supprimerEvenement(int $matchId, int $eventId, int $userId): bool
    {
        $event = Database::one(
            'SELECT * FROM match_events WHERE id = ? AND match_id = ?',
            [$eventId, $matchId]
        );
        if (!$event) {
            return false;
        }

        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            Database::run(
                'INSERT INTO event_log (match_id, event_id, action, payload, user_id)
                 VALUES (?,?,?,?,?)',
                [$matchId, $eventId, 'delete', json_encode($event, JSON_UNESCAPED_UNICODE), $userId]
            );
            Database::run('DELETE FROM match_events WHERE id = ?', [$eventId]);
            Score::recompute($matchId);
            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }

        return true;
    }

    /**
     * Met a jour les champs modifiables d'un match.
     *
     * home_score et away_score sont volontairement absents de la liste : ils
     * sont derives des evenements et seul Score peut les ecrire (invariant I1).
     */
    public static function mettreAJourMatch(int $matchId, array $body, string $role): bool
    {
        $autorises = $role === Auth::ROLE_ARBITRE
            ? ['status', 'minute']                       // l'arbitre pilote le direct, rien d'autre
            : ['status', 'minute', 'attendance', 'referee', 'venue', 'home_pens', 'away_pens'];

        $set    = [];
        $params = [];
        foreach ($autorises as $col) {
            if (array_key_exists($col, $body)) {
                $set[]    = "$col = ?";
                $params[] = $body[$col] === '' ? null : $body[$col];
            }
        }
        if (!$set) {
            return false;
        }

        $params[] = $matchId;
        Database::run('UPDATE matches SET ' . implode(', ', $set) . ' WHERE id = ?', $params);
        return true;
    }

    // ============================================== LOT 2 — statistiques

    public static function player(int $id): ?array
    {
        $player = Database::one(
            'SELECT p.*, TIMESTAMPDIFF(YEAR, p.birth_date, CURDATE()) AS age,
                    t.name AS team_name, t.abbr AS team_abbr, t.color AS team_color
               FROM players p
               JOIN teams t ON t.id = p.team_id
              WHERE p.id = ?',
            [$id]
        );
        if (!$player) {
            return null;
        }

        $player['stats'] = Database::one(
            "SELECT
               (SELECT COUNT(*) FROM match_events e
                 WHERE e.player_id = ? AND e.type IN ('goal','penalty'))              AS goals,
               (SELECT COUNT(*) FROM match_events e
                 WHERE e.related_player_id = ? AND e.type IN ('goal','penalty'))      AS assists,
               (SELECT COUNT(*) FROM match_events e
                 WHERE e.player_id = ? AND e.type = 'yellow')                         AS yellow_cards,
               (SELECT COUNT(*) FROM match_events e
                 WHERE e.player_id = ? AND e.type = 'red')                            AS red_cards,
               (SELECT COUNT(*) FROM lineups l WHERE l.player_id = ?)                 AS appearances,
               (SELECT COALESCE(SUM(l.minutes_played),0) FROM lineups l
                 WHERE l.player_id = ?)                                               AS minutes",
            [$id, $id, $id, $id, $id, $id]
        );

        $player['goals_by_matchday'] = Database::all(
            "SELECT m.matchday, COUNT(*) AS goals
               FROM match_events e
               JOIN matches m ON m.id = e.match_id
              WHERE e.player_id = ? AND e.type IN ('goal','penalty')
           GROUP BY m.matchday
           ORDER BY m.matchday",
            [$id]
        );

        return $player;
    }

    public static function playerRankings(string $slug, string $metric, int $limit = 20): array
    {
        $jointure = match ($metric) {
            'assists' => "e.type IN ('goal','penalty') AND e.related_player_id = p.id",
            'cards'   => "e.type IN ('yellow','red') AND e.player_id = p.id",
            default   => "e.type IN ('goal','penalty') AND e.player_id = p.id",
        };
        $valeur = $metric === 'cards'
            ? "SUM(CASE WHEN e.type='red' THEN 3 ELSE 1 END)"
            : 'COUNT(*)';

        $limit = max(1, min(100, $limit));

        return Database::all(
            "SELECT p.id, CONCAT(p.first_name,' ',p.last_name) AS name, p.jersey_number,
                    t.name AS team, t.abbr, t.color,
                    $valeur AS value,
                    (SELECT COUNT(*) FROM lineups l WHERE l.player_id = p.id) AS appearances
               FROM players p
               JOIN teams t ON t.id = p.team_id
               JOIN match_events e ON $jointure
               JOIN matches m ON m.id = e.match_id
               JOIN competitions c ON c.id = m.competition_id
              WHERE c.slug = ?
           GROUP BY p.id
           ORDER BY value DESC, appearances ASC
              LIMIT $limit",
            [$slug]
        );
    }

    public static function teamRankings(string $slug): array
    {
        $rows = self::standings($slug);

        $attaque = $rows;
        usort($attaque, fn($a, $b) => $b['goals_for'] <=> $a['goals_for']);

        $defense = $rows;
        usort($defense, fn($a, $b) => $a['goals_against'] <=> $b['goals_against']);

        $possession = Database::all(
            'SELECT t.name, t.abbr, ROUND(AVG(s.possession)) AS value
               FROM match_team_stats s
               JOIN teams t ON t.id = s.team_id
               JOIN matches m ON m.id = s.match_id
               JOIN competitions c ON c.id = m.competition_id
              WHERE c.slug = ?
           GROUP BY t.id
           ORDER BY value DESC
              LIMIT 5',
            [$slug]
        );

        $affluence = Database::all(
            'SELECT t.name, t.abbr, ROUND(AVG(m.attendance)) AS value
               FROM matches m
               JOIN teams t ON t.id = m.home_team_id
               JOIN competitions c ON c.id = m.competition_id
              WHERE c.slug = ? AND m.attendance IS NOT NULL
           GROUP BY t.id
           ORDER BY value DESC
              LIMIT 5',
            [$slug]
        );

        return [
            'attack'     => array_slice($attaque, 0, 5),
            'defence'    => array_slice($defense, 0, 5),
            'possession' => $possession,
            'attendance' => $affluence,
        ];
    }

    public static function news(int $limit = 20): array
    {
        $limit = max(1, min(100, $limit));
        return Database::all(
            'SELECT id, title, slug, category, excerpt, cover_image, published_at
               FROM news
              WHERE published_at IS NOT NULL AND published_at <= NOW()
           ORDER BY published_at DESC
              LIMIT ' . $limit
        );
    }

    public static function media(?string $type, int $limit = 40): array
    {
        $limit  = max(1, min(200, $limit));
        $where  = '';
        $params = [];
        if ($type === 'photo' || $type === 'video') {
            $where    = 'WHERE type = ?';
            $params[] = $type;
        }
        return Database::all(
            "SELECT id, type, title, url, thumbnail, duration_seconds, published_at
               FROM media $where
           ORDER BY published_at DESC
              LIMIT $limit",
            $params
        );
    }
}
