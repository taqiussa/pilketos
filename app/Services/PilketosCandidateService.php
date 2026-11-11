<?php

namespace App\Services;

use App\Models\PilketosPasangan;
use App\Models\PilketosPeriode;
use App\Models\UserSiswa;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PilketosCandidateService
{
    public function __construct(private PilketosStudentDirectory $students) {}

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): PilketosPasangan
    {
        return DB::transaction(function () use ($attributes): PilketosPasangan {
            $periode = PilketosPeriode::query()->lockForUpdate()->findOrFail($attributes['periode_id']);
            $nis = [(int) $attributes['ketua_nis'], (int) $attributes['wakil_nis']];
            sort($nis);
            UserSiswa::query()->whereIn('nis', $nis)->orderBy('nis')->lockForUpdate()->get();
            $ketua = $this->students->eligibleStudent((int) $attributes['ketua_nis'], $periode->tahun, $attributes['kategori']);
            $wakil = $this->students->eligibleStudent((int) $attributes['wakil_nis'], $periode->tahun, $attributes['kategori']);
            if ($ketua['nis'] === $wakil['nis']) {
                throw ValidationException::withMessages(['wakil_nis' => 'Ketua dan wakil harus berbeda.']);
            }
            $used = PilketosPasangan::query()->where('periode_id', $periode->id)->where(function ($query) use ($nis): void {
                $query->whereIn('ketua_nis', $nis)->orWhereIn('wakil_nis', $nis);
            })->exists();
            if ($used) {
                throw ValidationException::withMessages(['ketua_nis' => 'Siswa sudah digunakan pada pasangan lain di periode ini.']);
            }

            return PilketosPasangan::query()->create($attributes);
        }, 3);
    }
}
