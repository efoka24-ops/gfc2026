<?php
declare(strict_types=1);

namespace Gfc\Controller\Api;

use Gfc\Core\Controller;
use Gfc\Core\Request;
use Gfc\Core\Response;
use Gfc\Repository\StandingRepository;

final class StandingController extends Controller
{
    public function index(Request $req, array $args): never
    {
        $competitionId = $req->int('competition');
        if ($competitionId === null) {
            $competitionId = (int) $this->db->value(
                'SELECT id FROM competitions WHERE edition_id = ? AND type = "league" LIMIT 1',
                [$this->currentEditionId()]
            );
        }

        Response::json([
            'competition_id' => $competitionId,
            'standings'      => (new StandingRepository($this->db))->forCompetition($competitionId),
        ]);
    }
}
