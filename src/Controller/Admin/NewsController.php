<?php
declare(strict_types=1);
namespace Gfc\Controller\Admin;
use Gfc\Core\Controller;
use Gfc\Core\Crud;
use Gfc\Core\Request;

final class NewsController extends Controller
{
    private function fields(): array
    {
        return [
            ['name'=>'title','label'=>'Titre','type'=>'text','required'=>true],
            ['name'=>'slug','label'=>'Slug','type'=>'slug','from'=>'title'],
            ['name'=>'category','label'=>'Catégorie','type'=>'text','default'=>'Championnat'],
            ['name'=>'excerpt','label'=>'Chapô','type'=>'text','nullable'=>true],
            ['name'=>'body','label'=>'Contenu','type'=>'textarea','nullable'=>true],
            ['name'=>'status','label'=>'Statut','type'=>'select','options'=>['draft'=>'Brouillon','scheduled'=>'Programmé','published'=>'Publié']],
            ['name'=>'published_at','label'=>'Publié le','type'=>'datetime-local','nullable'=>true],
        ];
    }
    public function index(Request $req, array $args): never
    {
        $user = $this->auth->requireStaff($req);
        $rows = $this->db->all('SELECT n.*, u.name AS author FROM news n LEFT JOIN users u ON u.id=n.author_id ORDER BY COALESCE(n.published_at, NOW()) DESC, n.id DESC');
        $this->view('admin/_crud', [
            'user'=>$user,'module'=>'news','title'=>'Actualités & médias','kicker'=>'Communication',
            'subtitle'=>"Articles publiés sur l'application web",
            'notice'=>$req->str('ok')?'Enregistré.':null,'error'=>$req->str('err')?:null,
            'crud'=>['entity'=>'news','title'=>'un article','fields'=>$this->fields(),'columns'=>['title','category','status','published_at']],
            'rows'=>$rows,
        ]);
    }
    public function save(Request $req, array $args): never
    {
        $user = $this->auth->requireStaff($req);
        Crud::handle($req,$this->db,$this->auth,'news',$this->fields(),'/admin/news',['author_id'=>(int)$user['id']]);
    }
}
