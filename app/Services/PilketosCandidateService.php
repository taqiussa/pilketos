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
            $this->validateAttributes($periode, $attributes);

            return PilketosPasangan::query()->create($attributes);
        }, 3);
    }

    /** @param array<string, mixed> $attributes */
    public function update(PilketosPasangan $pasangan, array $attributes): PilketosPasangan
    {
        return DB::transaction(function () use ($pasangan, $attributes): PilketosPasangan {
            $locked = PilketosPasangan::query()->lockForUpdate()->findOrFail($pasangan->id);
            $periode = PilketosPeriode::query()->lockForUpdate()->findOrFail($locked->periode_id);
            if ((int) $attributes['periode_id'] !== $periode->id) {
                throw ValidationException::withMessages(['periode_id' => 'Periode pasangan tidak dapat diubah.']);
            }
            if ($locked->suara()->exists() && ((int) $attributes['ketua_nis'] !== (int) $locked->ketua_nis || (int) $attributes['wakil_nis'] !== (int) $locked->wakil_nis || $attributes['kategori'] !== $locked->kategori)) {
                throw ValidationException::withMessages(['pasangan' => 'Identitas pasangan yang sudah menerima suara tidak dapat diubah.']);
            }
            $this->validateAttributes($periode, $attributes, $locked->id);
            $locked->update($attributes);

            return $locked->refresh();
        }, 3);
    }

    /** @param array<string, mixed> $attributes */
    private function validateAttributes(PilketosPeriode $periode, array $attributes, ?int $ignoreId = null): void
    {
        $nis = [(int) $attributes['ketua_nis'], (int) $attributes['wakil_nis']];
        sort($nis);
        UserSiswa::query()->whereIn('nis', $nis)->orderBy('nis')->lockForUpdate()->get();
        $ketua = $this->students->eligibleStudent((int) $attributes['ketua_nis'], $periode->tahun, $attributes['kategori']);
        $wakil = $this->students->eligibleStudent((int) $attributes['wakil_nis'], $periode->tahun, $attributes['kategori']);
        if ($ketua['nis'] === $wakil['nis']) {
            throw ValidationException::withMessages(['wakil_nis' => 'Ketua dan wakil harus berbeda.']);
        }
        $used = PilketosPasangan::query()->where('periode_id', $periode->id)->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))->where(function ($query) use ($nis): void {
            $query->whereIn('ketua_nis', $nis)->orWhereIn('wakil_nis', $nis);
        })->exists();
        if ($used) {
            throw ValidationException::withMessages(['ketua_nis' => 'Siswa sudah digunakan pada pasangan lain di periode ini.']);
        }
        $numberUsed = PilketosPasangan::query()->where('periode_id', $periode->id)->where('kategori', $attributes['kategori'])->where('nomor_urut', $attributes['nomor_urut'])->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))->exists();
        if ($numberUsed) {
            throw ValidationException::withMessages(['nomor_urut' => 'Nomor urut sudah digunakan pada kategori ini.']);
        }
    }
}
