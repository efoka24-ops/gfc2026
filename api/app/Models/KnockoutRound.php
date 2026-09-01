<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KnockoutRound extends Model
{
    protected $fillable = ['competition_id', 'round', 'label', 'round_order'];

    public function competition(): BelongsTo { return $this->belongsTo(Competition::class); }
    public function slots(): HasMany         { return $this->hasMany(KnockoutSlot::class, 'round_id'); }
}
