<?php
declare(strict_types=1);

namespace Gfc\Controller\Admin;

use Gfc\Core\Controller;
use Gfc\Core\Request;

final class PlayerController extends Controller
{
    public function index(Request $req, array $args): never
    {
        $user = $this->auth->requireStaff($req);

        $this->view('admin/players', [
            'user'   => $user,
            'module' => 'players',
            'players' => (new \Gfc\Repository\PlayerRepository($this->db))->all($this->currentEditionId()),
        ]);
    }
}
