<?php
declare(strict_types=1);
namespace Gfc\Controller\Admin;
use Gfc\Core\Controller;
use Gfc\Core\Crud;
use Gfc\Core\Request;

final class SanctionController extends Controller
{
    private function fields(): array
    {
        $ed = $this->currentEditionId();
        $teams = array_column($this->db->all('SELECT id,name FROM teams WHERE edition_id=? ORDER BY name',[$ed]),'name','id');
        $players = ['' => '—'] + array_column($this->db->all('SELECT p.id, CONCAT(p.last_name," ",p.first_name) AS n FROM players p JOIN teams t ON t.id=p.team_id WHERE t.edition_id=? ORDER BY n',[$ed]),'n','id');
        return [
            ['name'=>'team_id','label'=>'Équipe','type'=>'select','options'=>$teams,'required'=>true],
            ['name'=>'player_id','label'=>'Joueur','type'=>'select','options'=>$players,'nullable'=>true],
            ['name'=>'type','label'=>'Type','type'=>'select','options'=>['yellow_accumulation'=>'Cumul jaunes','red'=>'Carton rouge','misconduct'=>'Comportement','forfeit'=>'Forfait','fine'=>'Amende']],
            ['name'=>'reason','label'=>'Motif','type'=>'text','required'=>true],
            ['name'=>'games_banned','label'=>'Matchs de suspension','type'=>'number','default'=>0],
            ['name'=>'fine_amount','label'=>'Amende (FCFA)','type'=>'number','default'=>0],
            ['name'=>'status','label'=>'Statut','type'=>'select','options'=>['open'=>'Ouverte','applied'=>'Appliquée','appealed'=>'En appel','cancelled'=>'Annulée']],
        ];
    }
    public function index(Request $req, array $args): never
    {
        $user = $this->auth->requireStaff($req);
        $rows = $this->db->all('SELECT s.*, t.name AS team, CONCAT(p.first_name," ",p.last_name) AS player FROM sanctions s JOIN teams t ON t.id=s.team_id LEFT JOIN players p ON p.id=s.player_id ORDER BY s.id DESC');
        $this->view('admin/_crud', [
            'user'=>$user,'module'=>'sanctions','title'=>'Sanctions','kicker'=>'Discipline',
            'subtitle'=>'Cartons, suspensions et amendes',
            'notice'=>$req->str('ok')?'Enregistré.':null,'error'=>$req->str('err')?:null,
            'crud'=>['entity'=>'sanctions','title'=>'une sanction','fields'=>$this->fields(),'columns'=>['team','player','type','reason','games_banned','fine_amount','status']],
            'rows'=>$rows,
        ]);
    }
    public function save(Request $req, array $args): never
    {
        $this->auth->requireStaff($req);
        Crud::handle($req,$this->db,$this->auth,'sanctions',$this->fields(),'/admin/sanctions');
    }
}
