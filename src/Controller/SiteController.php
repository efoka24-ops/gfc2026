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

/**
 * Application web publique : routage serveur par URL. Chaque page reutilise
 * l en-tete/pied communs (_head/_foot) et le bandeau des matchs.
 */
final class SiteController extends Controller
{
    public function index(Request $req, array $args): never
    {
        $ed      = $this->currentEditionId();
        $matches = new MatchRepository($this->db);
        $content = new ContentRepository($this->db);
        $path    = rtrim($req->path, '/') ?: '/';

        // Donnees communes (bandeau + en-tete)
        $common = [
            'edition'  => $this->db->one('SELECT * FROM editions WHERE id = ?', [$ed]),
            'live'     => $matches->search(['status' => 'live'], 5),
            'upcoming' => $matches->search(['status' => 'scheduled'], 6),
            'results'  => $matches->search(['status' => 'finished'], 6),
            'path'     => $path,
        ];

        $leagueId = (int) $this->db->value(
            'SELECT id FROM competitions WHERE edition_id = ? AND type = "league" LIMIT 1', [$ed]
        );

        // Detail : /matchs/{id} et /equipes/{id}
        if (preg_match('#^/matchs/(\d+)$#', $path, $m)) {
            $match = $matches->find((int) $m[1]);
            $this->view('site/match', $common + ['match' => $match, 'events' => $match ? $matches->events((int) $m[1]) : []]);
        }
        if (preg_match('#^/equipes/(\d+)$#', $path, $m)) {
            $team = (new TeamRepository($this->db))->find((int) $m[1]);
            $this->view('site/equipe', $common + ['team' => $team, 'squad' => (new PlayerRepository($this->db))->forTeam((int) $m[1])]);
        }

        switch ($path) {
            case '/matchs':
                $this->view('site/matchs', $common + [
                    'upcomingAll' => $matches->search(['status' => 'scheduled'], 100),
                    'resultsAll'  => $matches->search(['status' => 'finished'], 100),
                    'liveAll'     => $matches->search(['status' => 'live'], 20),
                ]);
            case '/classement':
                $this->view('site/classement', $common + [
                    'standings' => (new StandingRepository($this->db))->forCompetition($leagueId),
                ]);
            case '/equipes':
                $this->view('site/equipes', $common + [
                    'teams' => (new TeamRepository($this->db))->forEdition($ed),
                ]);
            case '/buteurs':
                $this->view('site/buteurs', $common + [
                    'scorers' => (new PlayerRepository($this->db))->topScorers($ed, 40),
                ]);
            case '/competitions':
                $this->view('site/competitions', $common + [
                    'competitions' => (new CompetitionRepository($this->db))->forEdition($ed),
                ]);
            case '/medias':
                $this->view('site/medias', $common + ['media' => $content->media($ed)]);
            case '/':
            default:
                $this->view('site/home', $common + [
                    'standings' => (new StandingRepository($this->db))->forCompetition($leagueId),
                    'news'      => $content->publishedNews(3),
                    'sponsors'  => $content->sponsors(),
                ]);
        }
    }
}
