<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class UserSiswa extends Authenticatable
{
    protected $table = 'user_siswa';

    protected $guarded = [];

    protected $hidden = ['password', 'remember_token'];

    public function riwayatSiswa(): HasMany
    {
        return $this->hasMany(Siswa::class, 'nis', 'nis');
    }
}
