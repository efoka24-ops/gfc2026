<?php
declare(strict_types=1);
namespace Gfc\Controller\Admin;
use Gfc\Core\Controller;
use Gfc\Core\Crud;
use Gfc\Core\Request;

final class PlayerController extends Controller
{
    private function teamOptions(): array
    {
        $ed = $this->currentEditionId();
        $rows = $this->db->all('SELECT id,name FROM teams WHERE edition_id = ? ORDER BY name',[$ed]);
        return array_column($rows,'name','id');
    }
    private function fields(): array
    {
        return [
            ['name'=>'team_id','label'=>'Équipe','type'=>'select','options'=>$this->teamOptions(),'required'=>true],
            ['name'=>'first_name','label'=>'Prénom','type'=>'text','required'=>true],
            ['name'=>'last_name','label'=>'Nom','type'=>'text','required'=>true],
            ['name'=>'position','label'=>'Poste','type'=>'select','options'=>['GB'=>'Gardien','DEF'=>'Défenseur','MIL'=>'Milieu','ATT'=>'Attaquant']],
            ['name'=>'shirt_no','label'=>'Numéro','type'=>'number','nullable'=>true],
            ['name'=>'license_no','label'=>'Licence','type'=>'text','nullable'=>true],
            ['name'=>'license_status','label'=>'Statut licence','type'=>'select','options'=>['pending'=>'En attente','valid'=>'Valide','missing'=>'Manquante']],
        ];
    }
    public function index(Request $req, array $args): never
    {
        $user = $this->auth->requireStaff($req);
        $ed = $this->currentEditionId();
        $rows = $this->db->all('SELECT p.*, t.name AS team FROM players p JOIN teams t ON t.id=p.team_id WHERE t.edition_id=? ORDER BY t.name, p.last_name',[$ed]);
        $this->view('admin/_crud', [
            'user'=>$user,'module'=>'players','title'=>'Joueurs & statistiques','kicker'=>'Acteurs',
            'subtitle'=>'Statistiques issues des feuilles de match validées',
            'notice'=>$req->str('ok')?'Enregistré.':null,'error'=>$req->str('err')?:null,
            'crud'=>['entity'=>'players','title'=>'un joueur','fields'=>$this->fields(),'columns'=>['first_name','last_name','team','position','shirt_no','license_status']],
            'rows'=>$rows,
        ]);
    }
    public function save(Request $req, array $args): never
    {
        $this->auth->requireStaff($req);
        Crud::handle($req,$this->db,$this->auth,'players',$this->fields(),'/admin/players');
    }
}
