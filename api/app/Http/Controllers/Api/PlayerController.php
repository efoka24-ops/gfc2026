<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Gestion des joueurs.
 *
 * Lecture publique (l'application mobile affiche les effectifs sans compte),
 * ecriture reservee a l'administrateur. Les postes suivent l'enum de la table :
 * GK (gardien), DEF (defenseur), MID (milieu), FWD (attaquant).
 */
class PlayerController extends Controller
{
    private const POSITIONS = ['GK', 'DEF', 'MID', 'FWD'];

    /**
     * Liste des joueurs. Filtre facultatif par equipe (?team_id=) et inclusion
     * des joueurs inactifs (?all=1). Par defaut, joueurs actifs uniquement,
     * groupables cote client par poste.
     */
    /**
     * Buts et passes decisives, derives des evenements de match.
     * Ajoutes en agregats a chaque requete pour ne jamais stocker de compteur
     * qui pourrait diverger des evenements reels.
     */
    private function withStats($query)
    {
        return $query->withCount([
            'events as goals' => fn ($q) => $q->whereIn('type', ['goal', 'penalty_scored']),
            'assistEvents as assists' => fn ($q) => $q->whereIn('type', ['goal', 'penalty_scored']),
            'events as yellow_cards' => fn ($q) => $q->whereIn('type', ['yellow_card', 'yellow_red_card']),
            'events as red_cards' => fn ($q) => $q->whereIn('type', ['red_card', 'yellow_red_card']),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $query = $this->withStats(
            Player::query()->with('team:id,name,short_name,primary_color')
        );

        if ($request->filled('team_id')) {
            $query->where('team_id', (int) $request->query('team_id'));
        }
        if (! $request->boolean('all')) {
            $query->where('active', true);
        }

        // ?sort=scorers -> classement des buteurs (buts, puis passes)
        if ($request->query('sort') === 'scorers') {
            $query->orderByDesc('goals')->orderByDesc('assists');
            if ($request->filled('limit')) {
                $query->limit(max(1, min(100, (int) $request->query('limit'))));
            }
        } else {
            $query->orderByRaw("FIELD(position,'GK','DEF','MID','FWD')")
                  ->orderBy('jersey_number')
                  ->orderBy('last_name');
        }

        return response()->json($query->get());
    }

    public function show(Player $player): JsonResponse
    {
        $player->loadCount([
            'events as goals' => fn ($q) => $q->whereIn('type', ['goal', 'penalty_scored']),
            'assistEvents as assists' => fn ($q) => $q->whereIn('type', ['goal', 'penalty_scored']),
            'events as yellow_cards' => fn ($q) => $q->whereIn('type', ['yellow_card', 'yellow_red_card']),
            'events as red_cards' => fn ($q) => $q->whereIn('type', ['red_card', 'yellow_red_card']),
        ]);
        return response()->json($player->load('team:id,name,short_name,primary_color'));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validatePayload($request);

        $player = Player::create($data);

        return response()->json($player->load('team:id,name,short_name'), 201);
    }

    public function update(Request $request, Player $player): JsonResponse
    {
        $data = $this->validatePayload($request, $player->id, $player->team_id);

        $player->update($data);

        return response()->json($player->load('team:id,name,short_name'));
    }

    /**
     * Desactivation logique : on ne supprime pas un joueur qui porte des
     * evenements de match, sinon les buts deja marques perdraient leur auteur.
     */
    public function destroy(Player $player): JsonResponse
    {
        $player->update(['active' => false]);

        return response()->json(null, 204);
    }

    /**
     * @param int|null $playerId joueur en cours de modification (pour l'unicite du numero)
     * @param int|null $currentTeamId equipe actuelle si non fournie a la modification
     */
    private function validatePayload(Request $request, ?int $playerId = null, ?int $currentTeamId = null): array
    {
        $sometimes = $playerId ? 'sometimes' : 'required';
        $teamId    = (int) ($request->input('team_id', $currentTeamId));

        return $request->validate([
            'team_id'       => [$playerId ? 'sometimes' : 'required', 'integer', 'exists:teams,id'],
            'first_name'    => [$sometimes, 'string', 'max:100'],
            'last_name'     => [$sometimes, 'string', 'max:100'],
            // Un meme numero ne peut pas etre porte par deux joueurs de la meme equipe.
            'jersey_number' => [
                'nullable', 'integer', 'min:1', 'max:99',
                Rule::unique('players', 'jersey_number')
                    ->where(fn ($q) => $q->where('team_id', $teamId))
                    ->ignore($playerId),
            ],
            'position'      => ['nullable', Rule::in(self::POSITIONS)],
            'birth_date'    => ['nullable', 'date'],
            'photo_url'     => ['nullable', 'url', 'max:512'],
            'active'        => ['sometimes', 'boolean'],
        ]);
    }
}
