<?php
declare(strict_types=1);

namespace Gfc\Controller\Admin;

use Gfc\Core\Controller;
use Gfc\Core\Request;

final class SponsorController extends Controller
{
    public function index(Request $req, array $args): never
    {
        $user = $this->auth->requireStaff($req);

        $this->view('admin/sponsors', [
            'user'   => $user,
            'module' => 'sponsors',
            'sponsors' => (new \Gfc\Repository\ContentRepository($this->db))->sponsors(false),
        ]);
    }
}
