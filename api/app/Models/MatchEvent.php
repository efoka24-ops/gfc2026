<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchEvent extends Model
{
    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'match_id', 'player_id', 'assist_player_id', 'team_id',
        'type', 'minute', 'extra_minute', 'description', 'recorded_by_id',
    ];

    public function match(): BelongsTo        { return $this->belongsTo(GameMatch::class, 'match_id'); }
    public function player(): BelongsTo       { return $this->belongsTo(Player::class); }
    public function assistPlayer(): BelongsTo { return $this->belongsTo(Player::class, 'assist_player_id'); }
    public function recordedBy(): BelongsTo   { return $this->belongsTo(User::class, 'recorded_by_id'); }
}
