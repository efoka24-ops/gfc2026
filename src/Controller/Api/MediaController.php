<?php
declare(strict_types=1);

namespace Gfc\Controller\Api;

use Gfc\Core\Controller;
use Gfc\Core\Request;
use Gfc\Core\Response;
use Gfc\Repository\ContentRepository;

final class MediaController extends Controller
{
    public function index(Request $req, array $args): never
    {
        Response::json(['media' => (new ContentRepository($this->db))->media($req->int('edition'))]);
    }
}
