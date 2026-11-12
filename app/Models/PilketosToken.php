<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PilketosToken extends Model
{
    protected $table = 'pilketos_tokens';

    protected $guarded = [];

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PilketosPeriode::class, 'periode_id');
    }

    protected function casts(): array
    {
        return [
            'token_encrypted' => 'encrypted',
            'used_at' => 'datetime',
        ];
    }
}
