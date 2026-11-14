<?php

namespace App\Services;

use App\Models\PilketosPeriode;
use App\Models\PilketosToken;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Validation\ValidationException;

class PilketosTeacherTokenReportService
{
    public const STATUSES = ['belum_dibuat', 'tersedia', 'digunakan', 'dibatalkan'];

    public function __construct(private PilketosTeacherDirectory $teachers) {}

    /** @return array{periode:PilketosPeriode,rows:list<array{no:int,name:string,username:string,roles:string,token:?string,token_label:string,status:string}>} */
    public function forPeriod(int $periodeId, string $status = '', string $search = ''): array
    {
        $periode = PilketosPeriode::query()->find($periodeId);
        if ($periode === null) {
            throw ValidationException::withMessages(['periode' => 'Periode tidak valid.']);
        }

        $tokens = PilketosToken::query()
            ->where('periode_id', $periode->id)
            ->where('voter_type', 'guru')
            ->get()
            ->keyBy('user_id');

        $search = trim($search);
        $rows = $this->teachers->eligibleQuery()
            ->with('roles:id,name')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->get()
            ->map(function ($user) use ($tokens): array {
                $token = $tokens->get($user->id);
                $plain = $this->plain($token);

                return [
                    'name' => $user->name,
                    'username' => $user->username ?? '-',
                    'roles' => $user->roles->pluck('name')->join(', ') ?: '-',
                    'token' => $plain,
                    'token_label' => $token === null || $token->getRawOriginal('token_encrypted') === null
                        ? '-'
                        : ($plain ?? 'Token tidak dapat ditampilkan'),
                    'status' => $token?->status ?? 'belum_dibuat',
                ];
            })
            ->when($status !== '', fn ($rows) => $rows->filter(fn (array $row): bool => $row['status'] === $status))
            ->values()
            ->map(fn (array $row, int $index): array => ['no' => $index + 1, ...$row])
            ->all();

        return compact('periode', 'rows');
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'tersedia' => 'Tersedia',
            'digunakan' => 'Digunakan',
            'dibatalkan' => 'Dibatalkan',
            default => 'Belum dibuat',
        };
    }

    private function plain(?PilketosToken $token): ?string
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
