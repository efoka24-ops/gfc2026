<?php
declare(strict_types=1);

namespace Gfc\Controller\Admin;

use Gfc\Core\Controller;
use Gfc\Core\Request;
use Gfc\Core\Response;

final class AuthController extends Controller
{
    public function form(Request $req, array $args): never
    {
        if ($this->auth->user($req) !== null) {
            Response::redirect('/admin');
        }
        $this->view('admin/login', ['error' => null]);
    }

    public function login(Request $req, array $args): never
    {
        $user = $this->auth->attempt(
            preg_replace('/\s+/', '', $req->str('phone')),
            $req->str('password')
        );

        if ($user === null) {
            $this->view('admin/login', ['error' => 'Identifiants incorrects.']);
        }

        Response::redirect($user['role'] === 'referee' ? '/admin/live' : '/admin');
    }

    public function logout(Request $req, array $args): never
    {
        $this->auth->logout();
        Response::redirect('/admin/login');
    }
}
