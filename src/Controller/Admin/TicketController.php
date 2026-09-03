<?php
declare(strict_types=1);

namespace Gfc\Controller\Admin;

use Gfc\Core\Controller;
use Gfc\Core\Request;

final class TicketController extends Controller
{
    public function index(Request $req, array $args): never
    {
        $user = $this->auth->requireStaff($req);

        $this->view('admin/tickets', [
            'user'   => $user,
            'module' => 'tickets',
            'tickets' => (new \Gfc\Repository\ContentRepository($this->db))->tickets(),
        ]);
    }
}
