<?php
declare(strict_types=1);

namespace Gfc\Controller\Admin;

use Gfc\Core\Controller;
use Gfc\Core\Request;

final class SanctionController extends Controller
{
    public function index(Request $req, array $args): never
    {
        $user = $this->auth->requireStaff($req);

        $this->view('admin/sanctions', [
            'user'   => $user,
            'module' => 'sanctions',
            'sanctions' => (new \Gfc\Repository\ContentRepository($this->db))->sanctions(),
        ]);
    }
}
