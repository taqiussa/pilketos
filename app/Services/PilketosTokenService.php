<?php

namespace App\Services;

use App\Models\PilketosAuditLog;
use App\Models\PilketosHakPilih;
use App\Models\PilketosPeriode;
use App\Models\PilketosToken;
use App\Models\Siswa;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PilketosTokenService
{
    public function __construct(private PilketosStudentDirectory $students) {}

    public function hash(string $token): string
    {
        $key = config('pilketos.token_key') ?: config('app.key');
        if (! is_string($key) || $key === '') {
            throw new RuntimeException('Kunci token PILKETOS belum dikonfigurasi.');
        }

        return hash_hmac('sha256', $this->normalize($token), $key);
    }

    public function normalize(string $token): string
    {
        return strtoupper(trim($token));
    }

    public function isValidFormat(string $token): bool
    {
        return (bool) preg_match('/^[ABCDEFGHJKLMNPQRSTUVWXYZ123456789]{6}$/', $this->normalize($token));
    }

    /** @return array<int, array{nis: int, name: string, kelas: string, token: string}> */
    public function generateForClass(PilketosPeriode $periode, int $kelasId, ?int $actorId): array
    {
        $rows = Siswa::query()->where('tahun', $periode->tahun)->where('kelas_id', $kelasId)->orderBy('nis')->get()->unique('nis');
        $printed = [];

        DB::transaction(function () use ($rows, $periode, $kelasId, $actorId, &$printed): void {
            foreach ($rows as $row) {
                $student = $this->students->eligibleStudent((int) $row->nis, $periode->tahun);
                $exists = PilketosToken::query()->where('periode_id', $periode->id)->where('nis', $student['nis'])->lockForUpdate()->exists();
                if ($exists) {
                    continue;
                }

                $token = $this->createToken($periode->id, $student['nis'], $kelasId, $actorId);
                PilketosHakPilih::query()->firstOrCreate(
                    ['periode_id' => $periode->id, 'nis' => $student['nis']],
                    ['kategori' => $student['kategori'], 'status' => 'belum_memilih'],
                );
                $printed[] = ['nis' => $student['nis'], 'name' => $student['name'], 'kelas' => $student['kelas'], 'token' => $token];
            }
        }, 3);

        return $printed;
    }

    public function replaceUnusedToken(int $tokenId, ?int $actorId): string
    {
        return DB::transaction(function () use ($tokenId, $actorId): string {
            $token = PilketosToken::query()->lockForUpdate()->findOrFail($tokenId);
            $hakPilih = PilketosHakPilih::query()
                ->where('periode_id', $token->periode_id)
                ->where('nis', $token->nis)
                ->lockForUpdate()
                ->first();
            if ($token->status !== 'tersedia' || $hakPilih === null || $hakPilih->status !== 'belum_memilih') {
                throw new RuntimeException('Token yang sudah digunakan atau tidak aktif tidak dapat diganti.');
            }

            for ($attempt = 0; $attempt < config('pilketos.max_token_attempts'); $attempt++) {
                $plain = $this->randomToken();
                try {
                    $token->update(['token_hash' => $this->hash($plain), 'generated_by' => $actorId]);
                    PilketosAuditLog::query()->create([
                        'periode_id' => $token->periode_id,
                        'nis' => $token->nis,
                        'actor_id' => $actorId,
                        'event' => 'token_diganti',
                        'context' => ['token_id' => $token->id],
                    ]);

                    return $plain;
                } catch (QueryException $exception) {
                    if (! $this->isUniqueViolation($exception)) {
                        throw $exception;
                    }
                }
            }

            throw new RuntimeException('Token pengganti aman tidak dapat dibuat. Coba lagi.');
        }, 3);
    }

    private function createToken(int $periodeId, int $nis, int $kelasId, ?int $actorId): string
    {
        for ($attempt = 0; $attempt < config('pilketos.max_token_attempts'); $attempt++) {
            $plain = $this->randomToken();
            try {
                PilketosToken::query()->create(['periode_id' => $periodeId, 'nis' => $nis, 'kelas_id' => $kelasId, 'token_hash' => $this->hash($plain), 'status' => 'tersedia', 'generated_by' => $actorId]);

                return $plain;
            } catch (QueryException $exception) {
                if (! $this->isUniqueViolation($exception)) {
                    throw $exception;
                }
            }
        }
        throw new RuntimeException('Token aman tidak dapat dibuat. Coba lagi.');
    }

    private function randomToken(): string
    {
        $alphabet = config('pilketos.token_alphabet');
        $token = '';
        for ($index = 0; $index < config('pilketos.token_length'); $index++) {
            $token .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return $token;
    }

    private function isUniqueViolation(QueryException $exception): bool
    {
        return in_array($exception->errorInfo[1] ?? null, [1062, 19], true);
    }
}
