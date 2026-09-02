<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Nommé GameMatch (pas Match) car "match" est un mot réservé en PHP 8+.
 */
class GameMatch extends Model
{
    protected $table = 'matches';

    protected $fillable = [
        'matchday_id', 'competition_id', 'home_team_id', 'away_team_id',
        'scheduled_at', 'venue', 'status', 'minute',
        'home_score', 'away_score',
    ];

    protected $casts = ['scheduled_at' => 'datetime'];

    public function matchday(): BelongsTo { return $this->belongsTo(Matchday::class); }
    public function homeTeam(): BelongsTo { return $this->belongsTo(Team::class, 'home_team_id'); }
    public function awayTeam(): BelongsTo { return $this->belongsTo(Team::class, 'away_team_id'); }
    public function events(): HasMany     { return $this->hasMany(MatchEvent::class, 'match_id'); }
    public function lineups(): HasMany    { return $this->hasMany(LineupSlot::class, 'match_id'); }

    public function isLive(): bool     { return $this->status === 'live'; }
    public function isFinished(): bool { return $this->status === 'finished'; }
}
