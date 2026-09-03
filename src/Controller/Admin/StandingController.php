<?php
declare(strict_types=1);

namespace Gfc\Controller\Admin;

use Gfc\Core\Controller;
use Gfc\Core\Request;

final class StandingController extends Controller
{
    public function index(Request $req, array $args): never
    {
        $user = $this->auth->requireStaff($req);

        $this->view('admin/standings', [
            'user'   => $user,
            'module' => 'stand',
            'competitions' => $this->db->all('SELECT id, name FROM competitions WHERE edition_id = ? ORDER BY id', [$this->currentEditionId()]),
            'standings' => (new \Gfc\Repository\StandingRepository($this->db))->forCompetition(
                $req->int('competition') ?? (int) $this->db->value('SELECT id FROM competitions WHERE edition_id = ? AND type = "league" LIMIT 1', [$this->currentEditionId()])
            ),
        ]);
    }
}
