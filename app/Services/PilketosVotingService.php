<?php

namespace App\Services;

use App\Models\PilketosHakPilih;
use App\Models\PilketosPasangan;
use App\Models\PilketosPeriode;
use App\Models\PilketosSuara;
use App\Models\PilketosToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PilketosVotingService
{
    public function record(int $tokenId, int $pasanganId): void
    {
        DB::transaction(function () use ($tokenId, $pasanganId): void {
            $token = PilketosToken::query()->lockForUpdate()->find($tokenId);
            if ($token === null || $token->status !== 'tersedia') {
                $this->reject();
            }

            $periode = PilketosPeriode::query()->lockForUpdate()->find($token->periode_id);
            if ($periode === null || ! $this->isOpen($periode)) {
                $this->reject();
            }

            $hakPilih = PilketosHakPilih::query()->where('periode_id', $periode->id)->where('nis', $token->nis)->lockForUpdate()->first();
            if ($hakPilih === null || $hakPilih->status !== 'belum_memilih') {
                $this->reject();
            }

            $pasangan = PilketosPasangan::query()->whereKey($pasanganId)->where('periode_id', $periode->id)->where('kategori', $hakPilih->kategori)->where('is_active', true)->lockForUpdate()->first();
            if ($pasangan === null) {
                $this->reject();
            }

            PilketosSuara::query()->create(['periode_id' => $periode->id, 'pasangan_id' => $pasangan->id, 'kategori' => $hakPilih->kategori, 'ballot_uuid' => (string) str()->uuid()]);
            $hakPilih->update(['status' => 'sudah_memilih', 'voted_at' => now()]);
            $token->update(['status' => 'digunakan', 'used_at' => now()]);
        }, 3);
    }

    public function isOpen(PilketosPeriode $periode): bool
    {
        $now = now(config('app.timezone'));

        return $periode->status === 'aktif' && ($periode->voting_mulai === null || $periode->voting_mulai->lte($now)) && ($periode->voting_selesai === null || $periode->voting_selesai->gte($now));
    }

    private function reject(): never
    {
        throw ValidationException::withMessages(['token' => 'Voting tidak dapat diproses.']);
    }
}
