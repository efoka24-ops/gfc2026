<?php
declare(strict_types=1);

namespace Gfc\Controller\Api;

use Gfc\Core\Controller;
use Gfc\Core\Request;
use Gfc\Core\Response;
use Gfc\Repository\PlayerRepository;

final class PlayerController extends Controller
{
    public function topScorers(Request $req, array $args): never
    {
        Response::json([
            'scorers' => (new PlayerRepository($this->db))
                ->topScorers($this->currentEditionId(), $req->int('limit', 20)),
        ]);
    }
}
