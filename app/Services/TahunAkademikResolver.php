<?php

namespace App\Services;

use App\Models\TahunAkademik;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Support\Collection;
use LogicException;

class TahunAkademikResolver
{
    /** @return array{tahun: string, semester: int, tanggal_mulai: CarbonImmutable, sumber: string} */
    public function resolve(DateTimeInterface|string|null $tanggal = null): array
    {
        $referensi = $this->normalizeDate($tanggal);
        $batas = $this->boundaries(range($referensi->year - 2, $referensi->year + 1));
        $this->assertChronological($batas);
        $terpilih = $batas->filter(fn (array $periode): bool => $periode['tanggal_mulai']->lte($referensi))->sortBy([['tanggal_mulai', 'desc'], ['urutan', 'desc']])->first();
        if ($terpilih === null) {
            throw new LogicException('Batas tahun akademik tidak tersedia.');
        }
        unset($terpilih['urutan']);

        return $terpilih;
    }

    /** @return Collection<int, array{tahun: string, semester: int, tanggal_mulai: CarbonImmutable, sumber: string, urutan: int}> */
    private function boundaries(array $tahunPertama): Collection
    {
        $tahun = collect($tahunPertama)->unique()->sort()->values();
        $format = $tahun->map(fn (int $nilai): string => $this->formatTahun($nilai));
        $konfigurasi = TahunAkademik::query()->whereIn('tahun', $format)->get(['tahun', 'semester', 'tanggal'])->keyBy(fn (TahunAkademik $periode): string => $periode->tahun.'|'.$periode->semester);

        return $tahun->flatMap(function (int $mulai) use ($konfigurasi): array {
            return collect([1, 2])->map(function (int $semester) use ($mulai, $konfigurasi): array {
                $nama = $this->formatTahun($mulai);
                $tersimpan = $konfigurasi->get($nama.'|'.$semester);
                $fallback = CarbonImmutable::create($mulai + ($semester === 2 ? 1 : 0), $semester === 1 ? 7 : 1, 1, 0, 0, 0, config('app.timezone'));

                return ['tahun' => $nama, 'semester' => $semester, 'tanggal_mulai' => $tersimpan ? $this->normalizeDate($tersimpan->tanggal) : $fallback, 'sumber' => $tersimpan ? 'database' : 'fallback', 'urutan' => ($mulai * 2) + $semester];
            })->all();
        })->values();
    }

    private function assertChronological(Collection $batas): void
    {
        $sebelumnya = null;
        foreach ($batas->sortBy('urutan') as $batasPeriode) {
            if ($sebelumnya !== null && $batasPeriode['tanggal_mulai']->lte($sebelumnya['tanggal_mulai'])) {
                throw new LogicException('Konfigurasi batas tahun akademik tidak kronologis.');
            }
            $sebelumnya = $batasPeriode;
        }
    }

    private function normalizeDate(DateTimeInterface|string|null $tanggal): CarbonImmutable
    {
        $timezone = config('app.timezone', 'Asia/Jakarta');

        return $tanggal instanceof DateTimeInterface ? CarbonImmutable::instance($tanggal)->setTimezone($timezone)->startOfDay() : CarbonImmutable::parse($tanggal ?? 'now', $timezone)->startOfDay();
    }

    private function formatTahun(int $tahun): string
    {
        return $tahun.' / '.($tahun + 1);
    }
}
