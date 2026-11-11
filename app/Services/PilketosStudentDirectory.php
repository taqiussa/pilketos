<?php

namespace App\Services;

use App\Models\Biodata;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\UserSiswa;
use Illuminate\Validation\ValidationException;

class PilketosStudentDirectory
{
    /** @return array{nis: int, name: string, kelas_id: int, kelas: string, kategori: string} */
    public function eligibleStudent(int $nis, string $tahun, ?string $kategori = null): array
    {
        $identity = UserSiswa::query()->where('nis', $nis)->first();
        $riwayat = Siswa::query()->where('nis', $nis)->where('tahun', $tahun)->latest('id')->first();
        $biodata = Biodata::query()->where('nis', $nis)->first();

        if ($identity === null || $riwayat === null || $biodata === null || ! in_array($biodata->jenis_kelamin, ['L', 'P'], true)) {
            throw ValidationException::withMessages(['nis' => 'Siswa tidak memenuhi syarat pada periode ini.']);
        }

        if ($kategori !== null && $biodata->jenis_kelamin !== $kategori) {
            throw ValidationException::withMessages(['nis' => 'Kategori siswa tidak sesuai.']);
        }

        $kelas = Kelas::query()->find($riwayat->kelas_id);
        if ($kelas === null) {
            throw ValidationException::withMessages(['nis' => 'Kelas siswa tidak ditemukan.']);
        }

        return [
            'nis' => (int) $identity->nis,
            'name' => $identity->name,
            'kelas_id' => (int) $riwayat->kelas_id,
            'kelas' => trim($kelas->tingkat.' '.$kelas->nama),
            'kategori' => $biodata->jenis_kelamin,
        ];
    }
}
