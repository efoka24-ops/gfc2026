<?php
namespace Gfc;

/**
 * Recalcule le score d'un match a partir de ses evenements.
 *
 * C'est le seul code autorise a ecrire matches.home_score et
 * matches.away_score (invariant I1). Ces colonnes ne sont pas la source de
 * verite : ce sont un cache de ce que disent les lignes de match_events, tenu a
 * jour dans la meme transaction que l'ecriture de l'evenement. Aucun formulaire,
 * aucune route d'API ne les saisit directement.
 */
final class Score
{
    /**
     * Recalcule et enregistre le score du match.
     *
     * A appeler apres toute insertion, modification ou suppression d'un
     * evenement. Prend un verrou sur la ligne du match pour que deux operateurs
     * saisissant la meme rencontre en meme temps ne se marchent pas dessus :
     * sans ce verrou, deux buts saisis simultanement peuvent produire deux
     * recalculs concurrents dont le dernier ecrase le premier (ecart E3).
     *
     * @return array{home:int,away:int} le score enregistre
     */
    public static function recompute(int $matchId): array
    {
        $pdo = Database::pdo();

        // Si l'appelant a deja ouvert une transaction — c'est le cas normal,
        // l'evenement et le recalcul devant etre atomiques — on s'y greffe.
        $ownTransaction = !$pdo->inTransaction();
        if ($ownTransaction) {
            $pdo->beginTransaction();
        }

        try {
            $st = $pdo->prepare(
                'SELECT home_team_id, away_team_id FROM matches WHERE id = ? FOR UPDATE'
            );
            $st->execute([$matchId]);
            $match = $st->fetch();

            if (!$match) {
                if ($ownTransaction) { $pdo->rollBack(); }
                return ['home' => 0, 'away' => 0];
            }

            $st = $pdo->prepare(
                "SELECT team_id, type, COUNT(*) AS n
                   FROM match_events
                  WHERE match_id = ?
                    AND is_published = 1
                    AND type IN ('goal','penalty','own_goal')
               GROUP BY team_id, type"
            );
            $st->execute([$matchId]);

            $home = 0;
            $away = 0;
            foreach ($st->fetchAll() as $row) {
                $countsForHome = (int) $row['team_id'] === (int) $match['home_team_id'];
                // Un but contre son camp est porte au credit de l'adversaire.
                if ($row['type'] === 'own_goal') {
                    $countsForHome = !$countsForHome;
                }
                if ($countsForHome) {
                    $home += (int) $row['n'];
                } else {
                    $away += (int) $row['n'];
                }
            }

            $st = $pdo->prepare('UPDATE matches SET home_score = ?, away_score = ? WHERE id = ?');
            $st->execute([$home, $away, $matchId]);

            if ($ownTransaction) {
                $pdo->commit();
            }

            return ['home' => $home, 'away' => $away];
        } catch (\Throwable $e) {
            if ($ownTransaction && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Verifie que le cache de score de tous les matchs correspond bien a leurs
     * evenements, et signale chaque ecart sans rien corriger.
     *
     * Sert de filet a l'invariant I1 : si cette methode renvoie autre chose
     * qu'un tableau vide, c'est qu'un chemin d'ecriture a contourne Score.
     *
     * @return array<int,array{match_id:int,stored:string,computed:string}>
     */
    public static function audit(): array
    {
        $ecarts = [];

        foreach (Database::all('SELECT id, home_score, away_score FROM matches') as $match) {
            $matchId = (int) $match['id'];

            // Recalcul en lecture seule : on ne veut pas reparer en auditant.
            $rows = Database::all(
                "SELECT e.team_id, e.type, COUNT(*) AS n, m.home_team_id
                   FROM match_events e
                   JOIN matches m ON m.id = e.match_id
                  WHERE e.match_id = ?
                    AND e.is_published = 1
                    AND e.type IN ('goal','penalty','own_goal')
               GROUP BY e.team_id, e.type, m.home_team_id",
                [$matchId]
            );

            $home = 0;
            $away = 0;
            foreach ($rows as $row) {
                $countsForHome = (int) $row['team_id'] === (int) $row['home_team_id'];
                if ($row['type'] === 'own_goal') {
                    $countsForHome = !$countsForHome;
                }
                if ($countsForHome) { $home += (int) $row['n']; } else { $away += (int) $row['n']; }
            }

            $stored = ((int) $match['home_score']) . '-' . ((int) $match['away_score']);
            $computed = $home . '-' . $away;

            if ($stored !== $computed) {
                $ecarts[] = ['match_id' => $matchId, 'stored' => $stored, 'computed' => $computed];
            }
        }

        return $ecarts;
    }
}
