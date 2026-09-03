<?php
declare(strict_types=1);
namespace Gfc\Controller\Admin;
use Gfc\Core\Controller;
use Gfc\Core\Crud;
use Gfc\Core\Request;

final class SponsorController extends Controller
{
    private function fields(): array
    {
        return [
            ['name'=>'name','label'=>'Nom','type'=>'text','required'=>true],
            ['name'=>'tier','label'=>'Niveau','type'=>'text','default'=>'Partenaire'],
            ['name'=>'url','label'=>'Site web','type'=>'text','nullable'=>true],
            ['name'=>'placement','label'=>'Emplacement','type'=>'text','nullable'=>true],
            ['name'=>'status','label'=>'Statut','type'=>'select','options'=>['active'=>'Actif','expiring'=>'Bientôt expiré','inactive'=>'Inactif']],
            ['name'=>'starts_on','label'=>'Début','type'=>'date','nullable'=>true],
            ['name'=>'ends_on','label'=>'Fin','type'=>'date','nullable'=>true],
        ];
    }
    public function index(Request $req, array $args): never
    {
        $user = $this->auth->requireStaff($req);
        $rows = $this->db->all('SELECT * FROM sponsors ORDER BY tier, name');
        $this->view('admin/_crud', [
            'user'=>$user,'module'=>'sponsors','title'=>'Sponsors','kicker'=>'Communication',
            'subtitle'=>"Partenaires et emplacements dans l'application",
            'notice'=>$req->str('ok')?'Enregistré.':null,'error'=>$req->str('err')?:null,
            'crud'=>['entity'=>'sponsors','title'=>'un sponsor','fields'=>$this->fields(),'columns'=>['name','tier','placement','status']],
            'rows'=>$rows,
        ]);
    }
    public function save(Request $req, array $args): never
    {
        $this->auth->requireStaff($req);
        Crud::handle($req,$this->db,$this->auth,'sponsors',$this->fields(),'/admin/sponsors');
    }
}
