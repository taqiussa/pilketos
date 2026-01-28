<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Siswa extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Get the biodata associated with the Siswa
     */
    public function biodata(): HasOne
    {
        return $this->hasOne(Biodata::class, 'nis', 'nis')->withDefault();
    }

    /**
     * Get the kelas that owns the Siswa
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class)->withDefault();
    }

    /**
     * Get the user that owns the Siswa
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nis', 'nis')->withDefault();
    }

    /**
     * Get the vote that owns the Siswa
     */
    public function vote(): HasOne
    {
        return $this->hasOne(Vote::class, 'nis', 'nis');
    }
}
