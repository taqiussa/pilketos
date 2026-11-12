<?php

namespace App\Services;

use App\Models\PilketosPasangan;
use Illuminate\Support\Collection;

class PilketosCandidateProfileService
{
    /** @param Collection<int, PilketosPasangan> $pasangan @return array<int, array{ketua: array{name: string, kelas: string}, wakil: array{name: string, kelas: string}}> */
    public function forPasangan(Collection $pasangan, string $tahun): array
    {
        $nis = $pasangan->flatMap(fn (PilketosPasangan $item): array => [$item->ketua_nis, $item->wakil_nis])->unique()->values();
        $students = \App\Models\Siswa::query()
            ->join('user_siswa', 'user_siswa.nis', '=', 'siswas.nis')
            ->join('kelas', 'kelas.id', '=', 'siswas.kelas_id')
            ->where('siswas.tahun', $tahun)
            ->whereIn('siswas.nis', $nis)
            ->get(['siswas.nis', 'user_siswa.name', 'kelas.tingkat', 'kelas.nama'])
            ->keyBy('nis');

        return $pasangan->mapWithKeys(function (PilketosPasangan $item) use ($students): array {
            return [$item->id => [
                'ketua' => $this->profile($students->get($item->ketua_nis)),
                'wakil' => $this->profile($students->get($item->wakil_nis)),
            ]];
        })->all();
    }

    private function profile(mixed $student): array
    {
        return [
            'name' => $student?->name ?? 'Data siswa tidak tersedia',
            'kelas' => $student ? trim($student->tingkat.' '.$student->nama) : '-',
        ];
    }
}
