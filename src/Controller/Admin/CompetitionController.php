<?php
declare(strict_types=1);

namespace Gfc\Controller\Admin;

use Gfc\Core\Controller;
use Gfc\Core\Request;

final class CompetitionController extends Controller
{
    public function index(Request $req, array $args): never
    {
        $user = $this->auth->requireStaff($req);

        $this->view('admin/competitions', [
            'user'   => $user,
            'module' => 'comp',
            'competitions' => (new \Gfc\Repository\CompetitionRepository($this->db))->forEdition($this->currentEditionId()),
        ]);
    }
}
