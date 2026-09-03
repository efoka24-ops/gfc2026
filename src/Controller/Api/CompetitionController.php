<?php
declare(strict_types=1);

namespace Gfc\Controller\Api;

use Gfc\Core\Controller;
use Gfc\Core\Request;
use Gfc\Core\Response;
use Gfc\Repository\CompetitionRepository;

final class CompetitionController extends Controller
{
    public function index(Request $req, array $args): never
    {
        $repo = new CompetitionRepository($this->db);
        Response::json(['competitions' => $repo->forEdition($this->currentEditionId())]);
    }
}
