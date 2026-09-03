<?php
declare(strict_types=1);

namespace Gfc\Controller;

use Gfc\Core\Controller;
use Gfc\Core\Request;
use Gfc\Repository\ContentRepository;
use Gfc\Repository\MatchRepository;
use Gfc\Repository\StandingRepository;

/**
 * Application web publique : rendu serveur de la page d'entrée
 * (référencement + premier affichage immédiat), puis navigation
 * assurée côté client par public/assets/js/app.js sur l'API JSON.
 */
final class SiteController extends Controller
{
    public function index(Request $req, array $args): never
    {
        $editionId = $this->currentEditionId();
        $matches   = new MatchRepository($this->db);
        $content   = new ContentRepository($this->db);

        $leagueId = (int) $this->db->value(
            'SELECT id FROM competitions WHERE edition_id = ? AND type = "league" LIMIT 1',
            [$editionId]
        );

        $this->view('site/index', [
            'edition'   => $this->db->one('SELECT * FROM editions WHERE id = ?', [$editionId]),
            'live'      => $matches->search(['status' => 'live'], 5),
            'upcoming'  => $matches->search(['status' => 'scheduled'], 6),
            'results'   => $matches->search(['status' => 'finished'], 6),
            'standings' => (new StandingRepository($this->db))->forCompetition($leagueId),
            'news'      => $content->publishedNews(3),
            'sponsors'  => $content->sponsors(),
            'path'      => $req->path,
        ]);
    }
}
