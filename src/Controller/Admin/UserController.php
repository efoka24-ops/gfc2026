<?php
declare(strict_types=1);

namespace Gfc\Controller\Admin;

use Gfc\Core\Controller;
use Gfc\Core\Request;

final class UserController extends Controller
{
    public function index(Request $req, array $args): never
    {
        $user = $this->auth->requireStaff($req);

        $this->view('admin/users', [
            'user'   => $user,
            'module' => 'users',
            'users' => $this->db->all('SELECT u.*, t.name AS team FROM users u LEFT JOIN teams t ON t.id = u.team_id ORDER BY FIELD(u.role, "admin","delegate","referee","editor"), u.name'),
        ]);
    }
}
