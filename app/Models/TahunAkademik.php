<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TahunAkademik extends Model
{
    protected $table = 'tahun_akademik';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['semester' => 'integer', 'tanggal' => 'date'];
    }
}
