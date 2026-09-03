<?php
declare(strict_types=1);

namespace Gfc\Controller\Admin;

use Gfc\Core\Controller;
use Gfc\Core\Request;
use Gfc\Core\Response;

/** Edition des informations du compte connecte. */
final class AccountController extends Controller
{
    public function form(Request $req, array $args): never
    {
        $user = $this->auth->requireStaff($req);
        $this->view('admin/account', [
            'user'    => $user,
            'module'  => 'account',
            'notice'  => $req->str('ok') !== '' ? 'Informations mises à jour.' : null,
            'error'   => null,
        ]);
    }

    public function save(Request $req, array $args): never
    {
        $user = $this->auth->requireStaff($req);

        $name  = trim($req->str('name'));
        $email = trim($req->str('email'));
        $pass  = $req->str('password');
        $pass2 = $req->str('password_confirm');

        $error = null;
        if ($name === '') {
            $error = 'Le nom est obligatoire.';
        } elseif ($pass !== '' && $pass !== $pass2) {
            $error = 'Les deux mots de passe ne correspondent pas.';
        } elseif ($pass !== '' && strlen($pass) < 6) {
            $error = 'Le mot de passe doit faire au moins 6 caractères.';
        }

        if ($error !== null) {
            $this->view('admin/account', ['user' => $user, 'module' => 'account', 'error' => $error, 'notice' => null]);
        }

        $data = ['name' => $name, 'email' => $email !== '' ? $email : null];
        if ($pass !== '') {
            $data['password_hash'] = password_hash($pass, PASSWORD_BCRYPT);
        }
        $this->db->update('users', (int) $user['id'], $data);
        $this->auth->log((int) $user['id'], 'account.update');

        Response::redirect('/admin/account?ok=1');
    }
}
