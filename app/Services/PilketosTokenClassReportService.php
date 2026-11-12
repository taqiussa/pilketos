<?php

namespace App\Services;

use App\Models\Kelas;
use App\Models\PilketosPeriode;
use App\Models\PilketosToken;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Validation\ValidationException;

class PilketosTokenClassReportService
{
    public function __construct(private PilketosStudentDirectory $students) {}

    /** @return array{periode: PilketosPeriode, kelas: Kelas, rows: list<array{no: int, nis: int, name: string, kelas: string, token: ?string, token_label: string, status: string, token_id: ?int, can_replace: bool}>} */
    public function forClass(int $periodeId, int $kelasId): array
    {
        $periode = PilketosPeriode::query()->find($periodeId);
        $kelas = Kelas::query()->find($kelasId);

        if ($periode === null || $kelas === null) {
            throw ValidationException::withMessages(['filter' => 'Tahun akademik atau kelas tidak valid.']);
        }

        $tokens = PilketosToken::query()
            ->where('periode_id', $periode->id)
            ->where('kelas_id', $kelas->id)
            ->get()
            ->keyBy('nis');

        $rows = collect($this->students->studentsForClass($periode->tahun, $kelas->id))
            ->values()
            ->map(function (array $student, int $index) use ($tokens): array {
                $token = $tokens->get($student['nis']);
                $plain = $this->recoverToken($token);

                return [
                    'no' => $index + 1,
                    'nis' => $student['nis'],
                    'name' => $student['name'],
                    'kelas' => $student['kelas'],
                    'token' => $plain,
                    'token_label' => $token === null ? '-' : ($plain ?? 'Token lama tidak tersedia'),
                    'status' => $token?->status ?? 'belum_dibuat',
                    'token_id' => $token?->id,
                    'can_replace' => $token?->status === 'tersedia',
                ];
            })
            ->all();

        return compact('periode', 'kelas', 'rows');
    }

    private function recoverToken(?PilketosToken $token): ?string
    {
        if ($token === null || $token->getRawOriginal('token_encrypted') === null) {
            return null;
        }

        try {
            return $token->token_encrypted;
        } catch (DecryptException) {
            return null;
        }
    }
}
