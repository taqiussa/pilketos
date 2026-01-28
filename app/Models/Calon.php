<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Calon extends Model
{
    use HasFactory;

    /** @var array<int, string> */
    protected $fillable = [
        'nama',
        'jenis_kelamin',
        'nomor_urut',
        'foto_url',
        'deskripsi',
    ];

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function countVotes(): int
    {
        return $this->votes()->count();
    }
}
