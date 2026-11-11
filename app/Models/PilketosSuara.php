<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PilketosSuara extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'pilketos_suara';

    protected $guarded = [];

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PilketosPeriode::class, 'periode_id');
    }

    public function pasangan(): BelongsTo
    {
        return $this->belongsTo(PilketosPasangan::class, 'pasangan_id');
    }
}
