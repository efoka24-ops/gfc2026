<?php
declare(strict_types=1);
namespace Gfc\Controller\Admin;
use Gfc\Core\Controller;
use Gfc\Core\Crud;
use Gfc\Core\Request;

final class CalendarController extends Controller
{
    private function opt(string $sql, array $p=[]): array { return array_column($this->db->all($sql,$p),'label','id'); }
    private function fields(): array
    {
        $ed = $this->currentEditionId();
        $teams = $this->opt('SELECT id, name AS label FROM teams WHERE edition_id=? ORDER BY name',[$ed]);
        $comps = $this->opt('SELECT id, name AS label FROM competitions WHERE edition_id=? ORDER BY name',[$ed]);
        $venues = ['' => '—'] + $this->opt('SELECT id, name AS label FROM venues ORDER BY name');
        $refs = ['' => '—'] + $this->opt("SELECT id, name AS label FROM users WHERE role='referee' ORDER BY name");
        return [
            ['name'=>'competition_id','label'=>'Compétition','type'=>'select','options'=>$comps,'required'=>true],
            ['name'=>'matchday','label'=>'Journée','type'=>'number','nullable'=>true],
            ['name'=>'home_team_id','label'=>'Équipe A (dom.)','type'=>'select','options'=>$teams,'required'=>true],
            ['name'=>'away_team_id','label'=>'Équipe B (ext.)','type'=>'select','options'=>$teams,'required'=>true],
            ['name'=>'venue_id','label'=>'Stade','type'=>'select','options'=>$venues,'nullable'=>true],
            ['name'=>'referee_id','label'=>'Arbitre','type'=>'select','options'=>$refs,'nullable'=>true],
            ['name'=>'kickoff_at','label'=>'Coup d\'envoi','type'=>'datetime-local','required'=>true],
            ['name'=>'status','label'=>'Statut','type'=>'select','options'=>['scheduled'=>'Programmé','live'=>'En direct','halftime'=>'Mi-temps','finished'=>'Terminé','postponed'=>'Reporté']],
        ];
    }
    public function index(Request $req, array $args): never
    {
        $user = $this->auth->requireStaff($req);
        $ed = $this->currentEditionId();
        $rows = $this->db->all(
            'SELECT m.*, c.name AS competition, h.name AS home, a.name AS away, v.name AS venue
               FROM matches m JOIN competitions c ON c.id=m.competition_id
               JOIN teams h ON h.id=m.home_team_id JOIN teams a ON a.id=m.away_team_id
          LEFT JOIN venues v ON v.id=m.venue_id
              WHERE c.edition_id=? ORDER BY m.kickoff_at',[$ed]);
        $this->view('admin/_crud', [
            'user'=>$user,'module'=>'calendar','title'=>'Calendrier','kicker'=>'Compétition',
            'subtitle'=>'Programmation des rencontres et désignation des arbitres',
            'notice'=>$req->str('ok')?'Enregistré.':null,'error'=>$req->str('err')?:null,
            'crud'=>['entity'=>'calendar','title'=>'une rencontre','fields'=>$this->fields(),'columns'=>['competition','home','away','kickoff_at','venue','status']],
            'rows'=>$rows,
        ]);
    }
    public function save(Request $req, array $args): never
    {
        $this->auth->requireStaff($req);
        Crud::handle($req,$this->db,$this->auth,'matches',$this->fields(),'/admin/calendar');
    }
}
