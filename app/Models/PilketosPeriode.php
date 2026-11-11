<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PilketosPeriode extends Model
{
    protected $table = 'pilketos_periodes';

    protected $guarded = [];

    public function pasangan(): HasMany
    {
        return $this->hasMany(PilketosPasangan::class, 'periode_id');
    }

    public function tokens(): HasMany
    {
        return $this->hasMany(PilketosToken::class, 'periode_id');
    }

    public function hakPilih(): HasMany
    {
        return $this->hasMany(PilketosHakPilih::class, 'periode_id');
    }

    public function suara(): HasMany
    {
        return $this->hasMany(PilketosSuara::class, 'periode_id');
    }

    protected function casts(): array
    {
        return ['voting_mulai' => 'datetime', 'voting_selesai' => 'datetime', 'hasil_publik' => 'boolean'];
    }
}
