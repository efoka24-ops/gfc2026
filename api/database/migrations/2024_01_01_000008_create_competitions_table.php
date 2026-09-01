<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Compétitions GFC 2026 :
 *  - Championnat       : aller simple, 10 équipes, 9 journées, 45 matchs
 *  - GP Gabriel MBAÏROBÉ : élimination directe, QF (8 équipes top championnat)
 *  - Super Coupe       : 1 match, vainqueur GP vs vainqueur Championnat
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competitions', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 50)->unique();   // championnat | gp_gabriel | super_coupe
            $table->string('name', 150);
            $table->enum('type', ['league', 'knockout', 'single_match']);
            $table->foreignId('season_id')->constrained('seasons')->cascadeOnDelete();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Ajouter competition_id aux matchs
        Schema::table('matches', function (Blueprint $table) {
            $table->foreignId('competition_id')->nullable()->after('matchday_id')
                  ->constrained('competitions')->nullOnDelete();
        });

        // Rounds pour la phase knockout (GP Gabriel MBAÏROBÉ)
        Schema::create('knockout_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained('competitions')->cascadeOnDelete();
            $table->enum('round', ['qf', 'sf', 'final']);  // QF=Quarts, SF=Demies, F=Finale
            $table->string('label', 100)->nullable();       // "Quarts de finale"
            $table->tinyInteger('round_order')->default(1); // 1=QF, 2=SF, 3=F
            $table->timestamps();
        });

        // Lien match ↔ round knockout
        Schema::create('knockout_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('round_id')->constrained('knockout_rounds')->cascadeOnDelete();
            $table->foreignId('match_id')->nullable()->constrained('matches')->nullOnDelete();
            $table->tinyInteger('slot_number');   // numéro de l'affiche (1-4 pour QF)
            $table->timestamps();
        });

        // Classement étendu avec competition_id
        Schema::table('standings', function (Blueprint $table) {
            $table->foreignId('competition_id')->nullable()->after('season_id')
                  ->constrained('competitions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('standings', fn ($t) => $t->dropForeign(['competition_id']));
        Schema::table('standings', fn ($t) => $t->dropColumn('competition_id'));
        Schema::dropIfExists('knockout_slots');
        Schema::dropIfExists('knockout_rounds');
        Schema::table('matches', fn ($t) => $t->dropForeign(['competition_id']));
        Schema::table('matches', fn ($t) => $t->dropColumn('competition_id'));
        Schema::dropIfExists('competitions');
    }
};
