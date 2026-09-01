<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Matchday extends Model
{
    public $timestamps = false;
    protected $fillable = ['season_id', 'number', 'label', 'date'];
    protected $casts    = ['date' => 'date'];

    public function season(): BelongsTo { return $this->belongsTo(Season::class); }
    public function matches(): HasMany  { return $this->hasMany(GameMatch::class); }
}
