<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PilketosPasangan extends Model
{
    protected $table = 'pilketos_pasangan';

    protected $guarded = [];

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PilketosPeriode::class, 'periode_id');
    }

    public function suara(): HasMany
    {
        return $this->hasMany(PilketosSuara::class, 'pasangan_id');
    }

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
