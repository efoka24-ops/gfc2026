<?php
declare(strict_types=1);

namespace Gfc\Controller\Admin;

use Gfc\Core\Controller;
use Gfc\Core\Request;

final class CalendarController extends Controller
{
    public function index(Request $req, array $args): never
    {
        $user = $this->auth->requireStaff($req);

        $this->view('admin/calendar', [
            'user'   => $user,
            'module' => 'cal',
            'matches' => (new \Gfc\Repository\MatchRepository($this->db))->search([], 200),
            'venues'  => $this->db->all('SELECT * FROM venues ORDER BY name'),
            'referees'=> $this->db->all('SELECT id, name FROM users WHERE role = "referee" AND status = "active" ORDER BY name'),
        ]);
    }
}
