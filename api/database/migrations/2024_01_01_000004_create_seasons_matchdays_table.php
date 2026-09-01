<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seasons', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique(); // "2025-2026"
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('active')->default(false);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('matchdays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained('seasons')->cascadeOnDelete();
            $table->smallInteger('number')->unsigned();
            $table->string('label', 100)->nullable(); // "Demi-finales"
            $table->date('date')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['season_id', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matchdays');
        Schema::dropIfExists('seasons');
    }
};
