<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Competition extends Model
{
    protected $fillable = ['slug', 'name', 'type', 'season_id', 'active'];
    protected $casts    = ['active' => 'boolean'];

    public function season(): BelongsTo    { return $this->belongsTo(Season::class); }
    public function knockoutRounds(): HasMany { return $this->hasMany(KnockoutRound::class); }

    // Helpers
    public function isLeague(): bool      { return $this->type === 'league'; }
    public function isKnockout(): bool    { return $this->type === 'knockout'; }
    public function isSingleMatch(): bool { return $this->type === 'single_match'; }
}
