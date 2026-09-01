<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    protected $fillable = ['name', 'short_name', 'logo_url', 'city', 'primary_color', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function players(): HasMany   { return $this->hasMany(Player::class); }
    public function homeMatches(): HasMany { return $this->hasMany(GameMatch::class, 'home_team_id'); }
    public function awayMatches(): HasMany { return $this->hasMany(GameMatch::class, 'away_team_id'); }
    public function standings(): HasMany  { return $this->hasMany(Standing::class); }
}
