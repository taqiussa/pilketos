<?php

namespace App\Services;

use App\Models\PilketosToken;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class PilketosTokenVerificationService
{
    public function __construct(
        private PilketosTokenService $tokens,
        private PilketosStudentDirectory $students,
        private PilketosTeacherDirectory $teachers,
        private PilketosVotingService $voting,
    ) {}

    public function verify(string $input, string $ip): void
    {
        $key = 'pilketos-failed-token:'.$ip.'|'.Session::getId();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['token' => 'Coba kembali beberapa saat lagi.']);
        }

        $plain = $this->tokens->normalize($input);
        $token = $this->tokens->isValidFormat($plain)
            ? PilketosToken::query()->with('periode')->where('token_hash', $this->tokens->hash($plain))->first()
            : null;

        if ($token === null || $token->status !== 'tersedia' || ! $this->voting->isOpen($token->periode)) {
            RateLimiter::hit($key, 120);
            throw ValidationException::withMessages(['token' => 'Token tidak dapat diverifikasi.']);
        }

        try {
            $voter = $token->voter_type === 'guru'
                ? $this->teachers->eligibleTeacher((int) $token->user_id)
                : $this->students->eligibleStudent((int) $token->nis, $token->periode->tahun);
        } catch (\Throwable) {
            RateLimiter::hit($key, 120);
            throw ValidationException::withMessages(['token' => 'Token tidak dapat diverifikasi.']);
        }

        RateLimiter::clear($key);
        Session::regenerate();
        Session::put('pilketos.vote', [
            'token_id' => $token->id,
            'voter_type' => $token->voter_type,
            'kategori' => $voter['kategori'],
            'nonce' => str()->random(40),
        ]);
    }
}
