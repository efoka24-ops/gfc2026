<?php
declare(strict_types=1);

namespace Gfc\Controller\Admin;

use Gfc\Core\Controller;
use Gfc\Core\Request;
use Gfc\Repository\MatchRepository;
use Gfc\Repository\PlayerRepository;

final class LiveController extends Controller
{
    public function index(Request $req, array $args): never
    {
        $user    = $this->auth->requireStaff($req);
        $repo    = new MatchRepository($this->db);
        $matchId = $req->int('match');

        if ($matchId === null) {
            $candidates = $repo->search(['status' => 'live'], 1) ?: $repo->search(['status' => 'scheduled'], 1);
            $matchId    = $candidates === [] ? null : (int) $candidates[0]['id'];
        }

        $match   = $matchId === null ? null : $repo->find($matchId);
        $players = new PlayerRepository($this->db);

        $this->view('admin/live', [
            'user'      => $user,
            'module'    => 'live',
            'match'     => $match,
            'squads'    => $match === null ? [] : [
                'home' => $players->forTeam((int) $match['home_id']),
                'away' => $players->forTeam((int) $match['away_id']),
            ],
            'assignable' => $repo->search(
                $user['role'] === 'referee' ? ['status' => 'scheduled'] : [],
                30
            ),
        ]);
    }
}
