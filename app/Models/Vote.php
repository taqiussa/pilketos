<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vote extends Model
{
    /** @var array<int, string> */
    protected $fillable = [
        'nis',
        'calon_id',
    ];

    public function calon(): BelongsTo
    {
        return $this->belongsTo(Calon::class);
    }
}
