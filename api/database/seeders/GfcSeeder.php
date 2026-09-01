<?php

namespace Database\Seeders;

use App\Models\Season;
use App\Models\Standing;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GfcSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin ──────────────────────────────────────────────
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@gfc.local')],
            [
                'name'       => 'Admin GFC',
                'first_name' => 'Admin',
                'last_name'  => 'GFC',
                'password'   => Hash::make(env('ADMIN_PASSWORD', 'GFC@admin2026!')),
                'role'       => 'admin',
                'active'     => true,
            ]
        );

        // ── Saison active ──────────────────────────────────────
        $season = Season::updateOrCreate(
            ['name' => '2025-2026'],
            [
                'start_date' => '2025-09-01',
                'end_date'   => '2026-06-30',
                'active'     => true,
            ]
        );

        // ── 10 équipes du championnat GFC ─────────────────────
        $teams = [
            ['name' => 'AS Jeunesse',       'short_name' => 'ASJ',  'city' => 'Ville 1',  'primary_color' => '#E63946'],
            ['name' => 'FC Étoile',          'short_name' => 'FCE',  'city' => 'Ville 2',  'primary_color' => '#FFD700'],
            ['name' => 'Sporting Club',      'short_name' => 'SC',   'city' => 'Ville 3',  'primary_color' => '#2DC653'],
            ['name' => 'Olympique Lumière',  'short_name' => 'OL',   'city' => 'Ville 4',  'primary_color' => '#457B9D'],
            ['name' => 'Racing Union',       'short_name' => 'RU',   'city' => 'Ville 5',  'primary_color' => '#6D2B97'],
            ['name' => 'Atletico Soleil',    'short_name' => 'ATL',  'city' => 'Ville 6',  'primary_color' => '#FF6B35'],
            ['name' => 'FC Victoire',        'short_name' => 'FCV',  'city' => 'Ville 7',  'primary_color' => '#1D3557'],
            ['name' => 'Stade Espoir',       'short_name' => 'SE',   'city' => 'Ville 8',  'primary_color' => '#00B4D8'],
            ['name' => 'Club Patriotes',     'short_name' => 'CP',   'city' => 'Ville 9',  'primary_color' => '#2B9348'],
            ['name' => 'Union Sportive',     'short_name' => 'US',   'city' => 'Ville 10', 'primary_color' => '#8D0801'],
        ];

        foreach ($teams as $teamData) {
            $team = Team::updateOrCreate(['name' => $teamData['name']], $teamData);

            // Initialiser le classement à 0 pour chaque équipe
            Standing::updateOrCreate(
                ['season_id' => $season->id, 'team_id' => $team->id],
                ['rank' => 0, 'played' => 0, 'won' => 0, 'drawn' => 0, 'lost' => 0,
                 'goals_for' => 0, 'goals_against' => 0, 'goal_difference' => 0, 'points' => 0]
            );
        }

        $this->command->info('✅ GFC Seeder terminé : 1 admin + 10 équipes + saison 2025-2026');
    }
}
