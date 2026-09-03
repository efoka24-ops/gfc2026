<?php
declare(strict_types=1);

namespace Gfc\Controller\Api;

use Gfc\Core\Controller;
use Gfc\Core\Request;
use Gfc\Core\Response;
use Gfc\Repository\ContentRepository;

final class SponsorController extends Controller
{
    public function index(Request $req, array $args): never
    {
        Response::json(['sponsors' => (new ContentRepository($this->db))->sponsors()]);
    }
}
