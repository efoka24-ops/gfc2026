<?php
declare(strict_types=1);

namespace Gfc\Controller\Admin;

use Gfc\Core\Controller;
use Gfc\Core\Request;

final class NewsController extends Controller
{
    public function index(Request $req, array $args): never
    {
        $user = $this->auth->requireStaff($req);

        $this->view('admin/news', [
            'user'   => $user,
            'module' => 'news',
            'news'  => (new \Gfc\Repository\ContentRepository($this->db))->allNews(),
            'media' => (new \Gfc\Repository\ContentRepository($this->db))->media(),
        ]);
    }
}
