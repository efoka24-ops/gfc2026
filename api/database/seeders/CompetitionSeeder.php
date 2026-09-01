<?php

namespace Database\Seeders;

use App\Models\Competition;
use App\Models\KnockoutRound;
use App\Models\Matchday;
use App\Models\Season;
use Illuminate\Database\Seeder;

class CompetitionSeeder extends Seeder
{
    public function run(): void
    {
        $season = Season::where('active', true)->firstOrFail();

        // ── 1. Championnat ─────────────────────────────────────
        $champ = Competition::updateOrCreate(
            ['slug' => 'championnat'],
            [
                'name'      => 'Championnat GFC 2025-2026',
                'type'      => 'league',
                'season_id' => $season->id,
                'active'    => true,
            ]
        );

        // 9 journées (aller simple, 10 équipes → 5 matchs/journée, 45 matchs total)
        for ($j = 1; $j <= 9; $j++) {
            Matchday::updateOrCreate(
                ['season_id' => $season->id, 'number' => $j],
                ['label' => "Journée {$j}"]
            );
        }

        $this->command->info("✅ Championnat créé : 9 journées");

        // ── 2. GP Gabriel MBAÏROBÉ ─────────────────────────────
        $gp = Competition::updateOrCreate(
            ['slug' => 'gp_gabriel'],
            [
                'name'      => 'Grand Prix Gabriel MBAÏROBÉ',
                'type'      => 'knockout',
                'season_id' => $season->id,
                'active'    => true,
            ]
        );

        // Seul les Quarts de finale pour le Lot 1 MVP
        // (top 8 du championnat se qualifient automatiquement)
        KnockoutRound::updateOrCreate(
            ['competition_id' => $gp->id, 'round' => 'qf'],
            ['label' => 'Quarts de finale', 'round_order' => 1]
        );

        $this->command->info("✅ GP Gabriel MBAÏROBÉ : Quarts de finale créés");

        // ── 3. Super Coupe ─────────────────────────────────────
        Competition::updateOrCreate(
            ['slug' => 'super_coupe'],
            [
                'name'      => 'Super Coupe GFC',
                'type'      => 'single_match',
                'season_id' => $season->id,
                'active'    => true,
            ]
        );

        $this->command->info("✅ Super Coupe créée");
        $this->command->info("   → Vainqueur GP Gabriel vs Vainqueur Championnat");
    }
}
