<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matchday_id')->constrained('matchdays')->cascadeOnDelete();
            $table->foreignId('home_team_id')->constrained('teams');
            $table->foreignId('away_team_id')->constrained('teams');
            $table->dateTime('scheduled_at');
            $table->string('venue', 200)->nullable();
            $table->enum('status', [
                'scheduled', 'live', 'half_time', 'finished', 'postponed', 'cancelled'
            ])->default('scheduled');
            $table->tinyInteger('minute')->unsigned()->nullable(); // minute en cours si LIVE
            $table->tinyInteger('home_score')->unsigned()->default(0);
            $table->tinyInteger('away_score')->unsigned()->default(0);
            $table->timestamps();

            $table->index('matchday_id');
            $table->index('home_team_id');
            $table->index('away_team_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
