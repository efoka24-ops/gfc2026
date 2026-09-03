<?php
declare(strict_types=1);

namespace Gfc\Controller\Api;

use Gfc\Core\Controller;
use Gfc\Core\Request;
use Gfc\Core\Response;
use Gfc\Core\Validator;

final class RegistrationController extends Controller
{
    public function store(Request $req, array $args): never
    {
        $data = [
            'team_name'    => $req->str('team_name'),
            'city'         => $req->str('city'),
            'manager_name' => $req->str('manager_name'),
            'phone'        => $req->str('phone'),
            'coach'        => $req->str('coach'),
            'squad_size'   => $req->int('squad_size'),
            'target'       => $req->str('target', 'Championnat'),
        ];

        $v = (new Validator($data))
            ->required('team_name', 'Le nom de l\'équipe')
            ->required('city', 'La ville')
            ->required('manager_name', 'Le responsable')
            ->required('phone', 'Le téléphone')
            ->phone('phone', 'Le téléphone')
            ->between('squad_size', 11, 40, 'Le nombre de joueurs')
            ->in('target', ['Championnat', 'Grand Prix', 'Les deux'], 'La compétition visée');

        if ($v->fails()) {
            Response::json(['error' => 'validation', 'fields' => $v->errors()], 422);
        }

        $id = $this->db->insert('registrations', $data);
        $this->auth->log(null, 'registration.received', 'registrations', $id, ['team' => $data['team_name']]);

        Response::json([
            'id'      => $id,
            'status'  => 'received',
            'message' => 'Dossier reçu. Le comité revient vers vous sous 72 heures.',
        ], 201);
    }
}
