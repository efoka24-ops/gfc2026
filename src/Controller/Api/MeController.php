<?php
declare(strict_types=1);

namespace Gfc\Controller\Api;

use Gfc\Core\Controller;
use Gfc\Core\Request;
use Gfc\Core\Response;

final class MeController extends Controller
{
    private function requireAppUser(Request $req): array
    {
        $u = $this->auth->appUser($req);
        if ($u === null) {
            Response::error('unauthenticated', 'Connexion requise.', 401);
        }
        return $u;
    }

    public function favorites(Request $req, array $args): never
    {
        $u = $this->requireAppUser($req);
        Response::json([
            'favorites' => $this->db->all(
                'SELECT t.id, t.name, t.short_name, t.color_primary
                   FROM favorites f JOIN teams t ON t.id = f.team_id
                  WHERE f.app_user_id = ? ORDER BY t.name',
                [$u['id']]
            ),
        ]);
    }

    public function toggleFavorite(Request $req, array $args): never
    {
        $u      = $this->requireAppUser($req);
        $teamId = $req->int('team_id');
        if ($teamId === null) {
            Response::error('invalid', 'team_id manquant.', 422);
        }

        $exists = (bool) $this->db->value(
            'SELECT 1 FROM favorites WHERE app_user_id = ? AND team_id = ?',
            [$u['id'], $teamId]
        );

        $exists
            ? $this->db->run('DELETE FROM favorites WHERE app_user_id = ? AND team_id = ?', [$u['id'], $teamId])
            : $this->db->run('INSERT IGNORE INTO favorites (app_user_id, team_id) VALUES (?, ?)', [$u['id'], $teamId]);

        Response::json(['following' => !$exists]);
    }

    public function registerDevice(Request $req, array $args): never
    {
        $u     = $this->requireAppUser($req);
        $token = $req->str('push_token');
        if ($token === '') {
            Response::error('invalid', 'push_token manquant.', 422);
        }

        $this->db->run(
            'INSERT INTO devices (app_user_id, push_token, platform) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE app_user_id = VALUES(app_user_id)',
            [$u['id'], $token, $req->str('platform', 'web')]
        );

        Response::json(['registered' => true]);
    }
}
