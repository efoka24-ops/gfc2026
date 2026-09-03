<?php
declare(strict_types=1);
namespace Gfc\Controller\Admin;
use Gfc\Core\Controller;
use Gfc\Core\Crud;
use Gfc\Core\Request;

final class UserController extends Controller
{
    private function fields(): array
    {
        $ed = $this->currentEditionId();
        $teams = ['' => '—'] + array_column($this->db->all('SELECT id,name FROM teams WHERE edition_id=? ORDER BY name',[$ed]),'name','id');
        return [
            ['name'=>'name','label'=>'Nom','type'=>'text','required'=>true],
            ['name'=>'phone','label'=>'Téléphone','type'=>'text','required'=>true],
            ['name'=>'email','label'=>'E-mail','type'=>'email','nullable'=>true],
            ['name'=>'role','label'=>'Rôle','type'=>'select','options'=>['admin'=>'Administrateur','delegate'=>'Délégué','referee'=>'Arbitre','editor'=>'Éditeur']],
            ['name'=>'team_id','label'=>'Équipe (délégué)','type'=>'select','options'=>$teams,'nullable'=>true],
            ['name'=>'status','label'=>'Statut','type'=>'select','options'=>['active'=>'Actif','invited'=>'Invité','disabled'=>'Désactivé']],
            ['name'=>'password','label'=>'Mot de passe','type'=>'password'],
        ];
    }
    public function index(Request $req, array $args): never
    {
        $user = $this->auth->requireStaff($req);
        $rows = $this->db->all('SELECT u.*, t.name AS team FROM users u LEFT JOIN teams t ON t.id=u.team_id ORDER BY u.role, u.name');
        $this->view('admin/_crud', [
            'user'=>$user,'module'=>'users','title'=>'Utilisateurs & rôles','kicker'=>'Administration',
            'subtitle'=>"Administrateurs, délégués d'équipe et arbitres",
            'notice'=>$req->str('ok')?'Enregistré.':null,'error'=>$req->str('err')?:null,
            'crud'=>['entity'=>'users','title'=>'un utilisateur','fields'=>$this->fields(),'columns'=>['name','phone','role','team','status']],
            'rows'=>$rows,
        ]);
    }
    public function save(Request $req, array $args): never
    {
        $this->auth->requireStaff($req);
        // Mot de passe par defaut a la creation si le champ est laisse vide.
        // Injecte via $defaults (Crud::handle) et non $_POST : la Request est
        // deja construite a cet instant, modifier $_POST n a aucun effet sur
        // $req->body (bug constate : password_hash NOT NULL jamais rempli).
        $defaults = [];
        if ($req->str('_action') === 'create' && $req->str('password') === '') {
            $defaults['password_hash'] = password_hash('gfc2026', PASSWORD_BCRYPT);
        }
        Crud::handle($req,$this->db,$this->auth,'users',$this->fields(),'/admin/users',$defaults);
    }
}
