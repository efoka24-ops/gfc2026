<?php
declare(strict_types=1);

namespace Gfc\Controller\Api;

use Gfc\Core\Controller;
use Gfc\Core\Request;
use Gfc\Core\Response;
use Gfc\Repository\TeamRepository;

final class TeamController extends Controller
{
    public function index(Request $req, array $args): never
    {
        $repo = new TeamRepository($this->db);
        Response::json(['teams' => $repo->forEdition($this->currentEditionId())]);
    }

    public function show(Request $req, array $args): never
    {
        $team = (new TeamRepository($this->db))->find((int) $args['id']);
        if ($team === null) {
            Response::error('not_found', 'Équipe inconnue.', 404);
        }
        Response::json(['team' => $team]);
    }
}
