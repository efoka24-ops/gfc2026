<?php
declare(strict_types=1);

namespace Gfc\Controller\Admin;

use Gfc\Core\Controller;
use Gfc\Core\Request;

final class TeamController extends Controller
{
    public function index(Request $req, array $args): never
    {
        $user = $this->auth->requireStaff($req);

        $this->view('admin/teams', [
            'user'   => $user,
            'module' => 'teams',
            'teams' => (new \Gfc\Repository\TeamRepository($this->db))->forEdition($this->currentEditionId()),
        ]);
    }
}
