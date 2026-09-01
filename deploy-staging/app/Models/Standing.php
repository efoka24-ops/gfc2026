<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Standing extends Model
{
    public $timestamps = false;

    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'season_id', 'team_id', 'rank',
        'played', 'won', 'drawn', 'lost',
        'goals_for', 'goals_against', 'goal_difference', 'points',
    ];

    public function season(): BelongsTo { return $this->belongsTo(Season::class); }
    public function team(): BelongsTo   { return $this->belongsTo(Team::class); }
}
