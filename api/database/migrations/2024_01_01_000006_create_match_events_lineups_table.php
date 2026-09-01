<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->foreignId('player_id')->nullable()->constrained('players')->nullOnDelete();
            $table->foreignId('assist_player_id')->nullable()->constrained('players')->nullOnDelete();
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->enum('type', [
                'goal', 'own_goal', 'yellow_card', 'red_card',
                'yellow_red_card', 'substitution_in', 'substitution_out',
                'penalty_scored', 'penalty_missed'
            ]);
            $table->tinyInteger('minute')->unsigned();
            $table->tinyInteger('extra_minute')->unsigned()->nullable();
            $table->string('description', 500)->nullable();
            $table->foreignId('recorded_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index('match_id');
            $table->index('player_id');
        });

        Schema::create('lineup_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->foreignId('player_id')->constrained('players');
            $table->foreignId('team_id')->constrained('teams');
            $table->enum('status', ['starter', 'substitute'])->default('starter');
            $table->enum('position', ['GK', 'DEF', 'MID', 'FWD'])->nullable();

            $table->unique(['match_id', 'player_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lineup_slots');
        Schema::dropIfExists('match_events');
    }
};
