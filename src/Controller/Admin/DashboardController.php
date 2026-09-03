<?php
declare(strict_types=1);

namespace Gfc\Controller\Admin;

use Gfc\Core\Controller;
use Gfc\Core\Request;
use Gfc\Repository\MatchRepository;
use Gfc\Repository\TeamRepository;

final class DashboardController extends Controller
{
    public function index(Request $req, array $args): never
    {
        $user      = $this->auth->requireStaff($req);
        $editionId = $this->currentEditionId();
        $matches   = new MatchRepository($this->db);

        $this->view('admin/dashboard', [
            'user'    => $user,
            'module'  => 'dash',
            'kpis'    => [
                ['l' => 'Équipes engagées', 'v' => (int) $this->db->value('SELECT COUNT(*) FROM teams WHERE edition_id = ?', [$editionId])],
                ['l' => 'Matchs joués',     'v' => (int) $this->db->value('SELECT COUNT(*) FROM matches m JOIN competitions c ON c.id = m.competition_id WHERE c.edition_id = ? AND m.status = "finished"', [$editionId])],
                ['l' => 'Buts inscrits',    'v' => (int) $this->db->value('SELECT COUNT(*) FROM match_events e JOIN matches m ON m.id = e.match_id JOIN competitions c ON c.id = m.competition_id WHERE c.edition_id = ? AND e.type IN ("goal","penalty")', [$editionId])],
                ['l' => 'Licenciés',        'v' => (int) $this->db->value('SELECT COUNT(*) FROM players p JOIN teams t ON t.id = p.team_id WHERE t.edition_id = ?', [$editionId])],
                ['l' => 'Billets vendus',   'v' => (int) $this->db->value('SELECT COALESCE(SUM(sold),0) FROM ticket_types')],
            ],
            'live'     => $matches->search(['status' => 'live'], 3),
            'upcoming' => $matches->search(['status' => 'scheduled'], 5),
            'todos'    => [
                'dossiers'  => (new TeamRepository($this->db))->pendingDossiers($editionId),
                'noReferee' => $this->db->all(
                    'SELECT m.id, m.kickoff_at, h.name AS home, a.name AS away
                       FROM matches m JOIN teams h ON h.id = m.home_team_id JOIN teams a ON a.id = m.away_team_id
                      WHERE m.referee_id IS NULL AND m.status = "scheduled" ORDER BY m.kickoff_at LIMIT 5'
                ),
                'sheets'    => $this->db->all(
                    'SELECT m.id, h.name AS home, a.name AS away
                       FROM matches m JOIN teams h ON h.id = m.home_team_id JOIN teams a ON a.id = m.away_team_id
                      WHERE m.status = "finished" AND m.sheet_status <> "validated" ORDER BY m.kickoff_at DESC LIMIT 5'
                ),
                'drafts'    => $this->db->all('SELECT id, title FROM news WHERE status = "draft" ORDER BY id DESC LIMIT 5'),
            ],
            'ticketChart' => $this->db->all(
                'SELECT DATE(created_at) AS d, SUM(qty) AS n FROM ticket_orders
                  WHERE status = "paid" AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                  GROUP BY DATE(created_at) ORDER BY d'
            ),
            'activity' => $this->db->all(
                'SELECT a.action, a.entity, a.created_at, u.name AS who
                   FROM audit_log a LEFT JOIN users u ON u.id = a.user_id
                  ORDER BY a.id DESC LIMIT 12'
            ),
        ]);
    }
}
