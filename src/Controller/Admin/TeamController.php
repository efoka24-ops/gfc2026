<?php
declare(strict_types=1);
namespace Gfc\Controller\Admin;
use Gfc\Core\Controller;
use Gfc\Core\Crud;
use Gfc\Core\Request;

final class TeamController extends Controller
{
    private function fields(): array
    {
        return [
            ['name'=>'name','label'=>'Équipe','type'=>'text','required'=>true],
            ['name'=>'short_name','label'=>'Code','type'=>'text','required'=>true],
            ['name'=>'city','label'=>'Ville','type'=>'text','required'=>true],
            ['name'=>'coach','label'=>'Entraîneur','type'=>'text','nullable'=>true],
            ['name'=>'founded','label'=>'Fondé en','type'=>'number','nullable'=>true],
            ['name'=>'color_primary','label'=>'Couleur 1','type'=>'color','default'=>'#7a1c2a'],
            ['name'=>'color_secondary','label'=>'Couleur 2','type'=>'color','default'=>'#e8720c'],
            ['name'=>'status','label'=>'Statut','type'=>'select','options'=>['pending'=>'En attente','validated'=>'Validé','rejected'=>'Rejeté']],
            ['name'=>'logo_path','label'=>'Logo','type'=>'file','nullable'=>true],
        ];
    }
    public function index(Request $req, array $args): never
    {
        $user = $this->auth->requireStaff($req);
        $ed = $this->currentEditionId();
        $rows = $this->db->all('SELECT * FROM teams WHERE edition_id = ? ORDER BY name', [$ed]);
        $this->view('admin/_crud', [
            'user'=>$user,'module'=>'teams','title'=>$req->str('ok')?'Équipes & effectifs':'Équipes & effectifs',
            'kicker'=>'Acteurs','subtitle'=>"Équipes engagées dans l'édition courante",
            'notice'=>$req->str('ok')?'Enregistré.':null,'error'=>$req->str('err')?:null,
            'crud'=>['entity'=>'teams','title'=>'une équipe','fields'=>$this->fields(),'columns'=>['name','city','coach','status']],
            'rows'=>$rows,
        ]);
    }
    public function save(Request $req, array $args): never
    {
        $this->auth->requireStaff($req);
        Crud::handle($req,$this->db,$this->auth,'teams',$this->fields(),'/admin/teams',['edition_id'=>$this->currentEditionId()]);
    }
}
