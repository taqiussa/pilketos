<?php

namespace App\Services;

use App\Models\PilketosPeriode;
use App\Models\PilketosToken;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Validation\ValidationException;

class PilketosTeacherTokenReportService
{
    public function __construct(private PilketosTeacherDirectory $teachers) {}

    /** @return array{periode:PilketosPeriode,rows:list<array{no:int,name:string,username:string,roles:string,token:?string,status:string}>} */
    public function forPeriod(int $periodeId, string $status = ''): array
    {
        $periode = PilketosPeriode::query()->find($periodeId);
        if ($periode === null) {
            throw ValidationException::withMessages(['periode' => 'Periode tidak valid.']);
        }

        $tokens = PilketosToken::query()->where('periode_id', $periode->id)->where('voter_type', 'guru')->get()->keyBy('user_id');
        $rows = $this->teachers->eligibleQuery()->with('roles:id,name')->orderBy('name')->get()->map(function ($user) use ($tokens): array {
            $token = $tokens->get($user->id);

            return ['name' => $user->name, 'username' => $user->username ?? '-', 'roles' => $user->roles->pluck('name')->join(', ') ?: '-', 'token' => $this->plain($token), 'status' => $token?->status ?? 'belum_dibuat'];
        })->when($status !== '', fn ($rows) => $rows->filter(fn (array $row) => $row['status'] === $status))->values()->map(fn (array $row, int $index) => ['no' => $index + 1, ...$row])->all();

        return compact('periode', 'rows');
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
