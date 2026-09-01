<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Season extends Model
{
    public $timestamps = false;
    protected $fillable = ['name', 'start_date', 'end_date', 'active'];
    protected $casts    = ['start_date' => 'date', 'end_date' => 'date', 'active' => 'boolean'];

    public function matchdays(): HasMany { return $this->hasMany(Matchday::class); }
    public function standings(): HasMany { return $this->hasMany(Standing::class); }
}
