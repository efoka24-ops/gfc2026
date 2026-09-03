<?php
declare(strict_types=1);

namespace Gfc\Controller\Api;

use Gfc\Core\Controller;
use Gfc\Core\Request;
use Gfc\Core\Response;
use Gfc\Repository\ContentRepository;

final class NewsController extends Controller
{
    public function index(Request $req, array $args): never
    {
        Response::json(['news' => (new ContentRepository($this->db))->publishedNews($req->int('limit', 12))]);
    }
}
