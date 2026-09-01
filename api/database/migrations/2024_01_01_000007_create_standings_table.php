<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('standings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained('seasons')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->tinyInteger('rank')->unsigned()->default(0);
            $table->tinyInteger('played')->unsigned()->default(0);
            $table->tinyInteger('won')->unsigned()->default(0);
            $table->tinyInteger('drawn')->unsigned()->default(0);
            $table->tinyInteger('lost')->unsigned()->default(0);
            $table->smallInteger('goals_for')->unsigned()->default(0);
            $table->smallInteger('goals_against')->unsigned()->default(0);
            $table->smallInteger('goal_difference')->default(0);
            $table->tinyInteger('points')->unsigned()->default(0);
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['season_id', 'team_id']);
            $table->index(['season_id', 'points']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('standings');
    }
};
