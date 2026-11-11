<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PilketosHakPilih extends Model
{
    protected $table = 'pilketos_hak_pilih';

    protected $guarded = [];

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PilketosPeriode::class, 'periode_id');
    }

    protected function casts(): array
    {
        return ['voted_at' => 'datetime'];
    }
}
