<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Player extends Model
{
    protected $fillable = [
        'team_id', 'first_name', 'last_name', 'jersey_number',
        'position', 'birth_date', 'photo_url', 'active',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'active'     => 'boolean',
    ];

    public function team(): BelongsTo  { return $this->belongsTo(Team::class); }
    public function events(): HasMany  { return $this->hasMany(MatchEvent::class); }
}
