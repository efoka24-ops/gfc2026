<?php
declare(strict_types=1);

namespace Gfc\Controller\Api;

use Gfc\Core\Controller;
use Gfc\Core\Request;
use Gfc\Core\Response;
use Gfc\Repository\ContentRepository;

final class EditionController extends Controller
{
    public function current(Request $req, array $args): never
    {
        $edition = $this->db->one('SELECT * FROM editions WHERE is_current = 1 LIMIT 1');
        if ($edition === null) {
            Response::error('no_edition', 'Aucune édition courante.', 404);
        }

        $id = (int) $edition['id'];
        Response::json([
            'edition' => $edition,
            'counters' => [
                'teams'         => (int) $this->db->value('SELECT COUNT(*) FROM teams WHERE edition_id = ?', [$id]),
                'competitions'  => (int) $this->db->value('SELECT COUNT(*) FROM competitions WHERE edition_id = ?', [$id]),
                'matches'       => (int) $this->db->value('SELECT COUNT(*) FROM matches m JOIN competitions c ON c.id = m.competition_id WHERE c.edition_id = ?', [$id]),
                'matches_played'=> (int) $this->db->value('SELECT COUNT(*) FROM matches m JOIN competitions c ON c.id = m.competition_id WHERE c.edition_id = ? AND m.status = "finished"', [$id]),
                'players'       => (int) $this->db->value('SELECT COUNT(*) FROM players p JOIN teams t ON t.id = p.team_id WHERE t.edition_id = ?', [$id]),
                'goals'         => (int) $this->db->value('SELECT COUNT(*) FROM match_events e JOIN matches m ON m.id = e.match_id JOIN competitions c ON c.id = m.competition_id WHERE c.edition_id = ? AND e.type IN ("goal","penalty")', [$id]),
            ],
        ]);
    }

    public function honours(Request $req, array $args): never
    {
        Response::json(['palmares' => (new ContentRepository($this->db))->honours()]);
    }
}
