<?php
declare(strict_types=1);
namespace Gfc\Controller\Admin;
use Gfc\Core\Controller;
use Gfc\Core\Crud;
use Gfc\Core\Request;

final class CompetitionController extends Controller
{
    private function fields(): array
    {
        return [
            ['name'=>'name','label'=>'Nom','type'=>'text','required'=>true],
            ['name'=>'slug','label'=>'Slug','type'=>'slug','from'=>'name'],
            ['name'=>'type','label'=>'Type','type'=>'select','options'=>['league'=>'Championnat','cup'=>'Coupe','supercup'=>'Super Coupe']],
            ['name'=>'format','label'=>'Format','type'=>'text','nullable'=>true],
            ['name'=>'color','label'=>'Couleur','type'=>'color','default'=>'#7a1c2a'],
            ['name'=>'points_win','label'=>'Pts victoire','type'=>'number','default'=>3],
            ['name'=>'points_draw','label'=>'Pts nul','type'=>'number','default'=>1],
            ['name'=>'qualify_slots','label'=>'Places qualif.','type'=>'number','default'=>2],
        ];
    }
    public function index(Request $req, array $args): never
    {
        $user = $this->auth->requireStaff($req);
        $ed = $this->currentEditionId();
        $rows = $this->db->all('SELECT * FROM competitions WHERE edition_id=? ORDER BY id',[$ed]);
        $this->view('admin/_crud', [
            'user'=>$user,'module'=>'competitions','title'=>'Compétitions & phases','kicker'=>'Structure',
            'subtitle'=>"Structure de l'édition courante",
            'notice'=>$req->str('ok')?'Enregistré.':null,'error'=>$req->str('err')?:null,
            'crud'=>['entity'=>'competitions','title'=>'une compétition','fields'=>$this->fields(),'columns'=>['name','type','format','qualify_slots']],
            'rows'=>$rows,
        ]);
    }
    public function save(Request $req, array $args): never
    {
        $this->auth->requireStaff($req);
        Crud::handle($req,$this->db,$this->auth,'competitions',$this->fields(),'/admin/competitions',['edition_id'=>$this->currentEditionId()]);
    }
}
