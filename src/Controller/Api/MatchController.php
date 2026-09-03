<?php
declare(strict_types=1);

namespace Gfc\Controller\Api;

use Gfc\Core\Controller;
use Gfc\Core\Request;
use Gfc\Core\Response;
use Gfc\Repository\MatchRepository;

final class MatchController extends Controller
{
    public function index(Request $req, array $args): never
    {
        $matches = (new MatchRepository($this->db))->search([
            'status'         => $req->str('status') ?: null,
            'competition_id' => $req->int('competition'),
            'team_id'        => $req->int('team'),
            'from'           => $req->str('from') ?: null,
        ], $req->int('limit', 100));

        Response::json(['matches' => $matches]);
    }

    public function show(Request $req, array $args): never
    {
        $match = (new MatchRepository($this->db))->find((int) $args['id']);
        if ($match === null) {
            Response::error('not_found', 'Rencontre inconnue.', 404);
        }
        Response::json(['match' => $match]);
    }
}
