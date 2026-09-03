<?php
declare(strict_types=1);

namespace Gfc\Controller;

use Gfc\Core\Controller;
use Gfc\Core\Request;
use Gfc\Repository\ContentRepository;
use Gfc\Repository\MatchRepository;
use Gfc\Repository\PlayerRepository;
use Gfc\Repository\StandingRepository;
use Gfc\Repository\TeamRepository;
use Gfc\Repository\CompetitionRepository;

final class SiteController extends Controller
{
    public function index(Request $req, array $args): never
    {
        $ed      = $this->currentEditionId();
        $matches = new MatchRepository($this->db);
        $content = new ContentRepository($this->db);
        $path    = rtrim($req->path, '/') ?: '/';

        $common = [
            'edition'  => $this->db->one('SELECT * FROM editions WHERE id = ?', [$ed]),
            'live'     => $matches->search(['status' => 'live'], 5),
            'upcoming' => $matches->search(['status' => 'scheduled'], 6),
            'results'  => $matches->search(['status' => 'finished'], 6),
            'path'     => $path,
        ];

        $league = $this->db->one('SELECT * FROM competitions WHERE edition_id = ? AND type = "league" LIMIT 1', [$ed]);
        $leagueId = (int) ($league['id'] ?? 0);
        $coaches = [];
        foreach ($this->db->all('SELECT id, coach FROM teams WHERE edition_id = ?', [$ed]) as $t) {
            $coaches[(int) $t['id']] = $t['coach'];
        }

        // ── Détails ──────────────────────────────────────────────
        if (preg_match('#^/matchs/(\d+)$#', $path, $m)) {
            $match = $matches->find((int) $m[1]);
            $this->view('site/match', $common + ['match' => $match, 'events' => $match ? $matches->events((int) $m[1]) : []]);
        }
        if (preg_match('#^/equipes/(\d+)$#', $path, $m)) {
            $team = (new TeamRepository($this->db))->find((int) $m[1]);
            $this->view('site/equipe', $common + ['team' => $team, 'squad' => (new PlayerRepository($this->db))->forTeam((int) $m[1])]);
        }

        $standingsOf = function (int $compId) use ($coaches): array {
            $rows = (new StandingRepository($this->db))->forCompetition($compId);
            foreach ($rows as $i => &$r) {
                $r['rank']  = $i + 1;
                $r['coach'] = $coaches[(int) $r['team_id']] ?? null;
            }
            return $rows;
        };

        switch (true) {
            case $path === '/matchs':
                $f = $req->str('f', 'tous');
                $map = ['direct' => 'live', 'avenir' => 'scheduled', 'resultats' => 'finished'];
                if (isset($map[$f])) {
                    $fixtures = $matches->search(['status' => $map[$f]], 200);
                } else {
                    $fixtures = array_merge(
                        $matches->search(['status' => 'live'], 50),
                        $matches->search(['status' => 'scheduled'], 200),
                        $matches->search(['status' => 'finished'], 200)
                    );
                }
                $this->view('site/matchs', $common + ['fixtures' => $fixtures, 'filter' => $f]);

            case $path === '/classement':
            case $path === '/competitions':
                $tab = $path === '/classement' ? 'championnat' : $req->str('c', 'championnat');
                $cup   = $this->db->one('SELECT * FROM competitions WHERE edition_id = ? AND type = "cup" LIMIT 1', [$ed]);
                $super = $this->db->one('SELECT * FROM competitions WHERE edition_id = ? AND type = "supercup" LIMIT 1', [$ed]);
                $data = $common + [
                    'tab' => $tab, 'league' => $league, 'cup' => $cup, 'super' => $super,
                    'standings' => $standingsOf($leagueId),
                    'bracket' => $cup ? $this->db->all(
                        'SELECT ph.name AS phase, ph.ord, m.status, m.home_score, m.away_score,
                                h.name AS home, a.name AS away
                           FROM matches m
                           JOIN phases ph ON ph.id = m.phase_id
                           JOIN teams h ON h.id = m.home_team_id
                           JOIN teams a ON a.id = m.away_team_id
                          WHERE m.competition_id = ?
                       ORDER BY ph.ord, m.kickoff_at', [(int) $cup['id']]
                    ) : [],
                    'superMatch' => $super ? $this->db->one(
                        'SELECT m.*, h.name AS home, a.name AS away, v.name AS venue
                           FROM matches m JOIN teams h ON h.id=m.home_team_id JOIN teams a ON a.id=m.away_team_id
                      LEFT JOIN venues v ON v.id=m.venue_id
                          WHERE m.competition_id = ? ORDER BY m.kickoff_at LIMIT 1', [(int) $super['id']]
                    ) : null,
                ];
                $this->view('site/competitions', $data);

            case $path === '/equipes':
                $this->view('site/equipes', $common + ['teams' => $standingsOf($leagueId)]);

            case $path === '/buteurs':
                $this->view('site/buteurs', $common + ['scorers' => (new PlayerRepository($this->db))->topScorers($ed, 40)]);

            case $path === '/medias':
                $tab = $req->str('tab', 'actualites');
                $this->view('site/medias', $common + [
                    'tab' => $tab,
                    'news' => $content->allNews(),
                    'media' => $content->media($ed),
                    'honours' => $content->honours(),
                ]);

            default:
                $this->view('site/home', $common + [
                    'standings' => $standingsOf($leagueId),
                    'news'      => $content->publishedNews(3),
                    'sponsors'  => $content->sponsors(),
                ]);
        }
    }
}
