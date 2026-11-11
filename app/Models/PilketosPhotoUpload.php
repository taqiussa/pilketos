<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PilketosPhotoUpload extends Model
{
    protected $table = 'pilketos_photo_uploads';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'used_at' => 'datetime'];
    }
}
