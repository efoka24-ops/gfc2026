<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->tinyInteger('jersey_number')->unsigned()->nullable();
            $table->enum('position', ['GK', 'DEF', 'MID', 'FWD'])->nullable();
            $table->date('birth_date')->nullable();
            $table->string('photo_url', 512)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index('team_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
