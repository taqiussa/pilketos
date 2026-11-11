<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

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

    /** The canonical image is foto_pasangan; legacy rows remain readable. */
    public function photoPath(): ?string
    {
        return $this->foto_pasangan ?: $this->foto_ketua ?: $this->foto_wakil;
    }

    public function photoUrl(): ?string
    {
        return ($path = $this->photoPath()) === null ? null : Storage::disk(config('pilketos.photo_disk'))->url($path);
    }
}
