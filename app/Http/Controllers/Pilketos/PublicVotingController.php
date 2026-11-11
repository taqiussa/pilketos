<?php

namespace App\Http\Controllers\Pilketos;

use App\Http\Controllers\Controller;
use App\Models\PilketosPasangan;
use App\Models\PilketosPeriode;
use App\Models\PilketosToken;
use App\Services\PilketosStudentDirectory;
use App\Services\PilketosTokenService;
use App\Services\PilketosVotingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class PublicVotingController extends Controller
{
    public function landing(): View
    {
        $periode = PilketosPeriode::query()->where('status', 'aktif')->orderByDesc('id')->first();

        return view('pilketos.public.landing', compact('periode'));
    }

    public function verify(Request $request, PilketosTokenService $tokens, PilketosStudentDirectory $students, PilketosVotingService $voting): RedirectResponse
    {
        $request->validate(['token' => ['required', 'string', 'size:6']]);
        $key = 'pilketos-failed-token:'.$request->ip().'|'.$request->session()->getId();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors(['token' => 'Coba kembali beberapa saat lagi.']);
        }
        $plain = $tokens->normalize($request->string('token')->toString());
        if (! $tokens->isValidFormat($plain)) {
            RateLimiter::hit($key, 120);

            return back()->withErrors(['token' => 'Token tidak dapat diverifikasi.']);
        }
        $token = PilketosToken::query()->with('periode')->where('token_hash', $tokens->hash($plain))->first();
        if ($token === null || $token->status !== 'tersedia' || ! $voting->isOpen($token->periode)) {
            RateLimiter::hit($key, 120);

            return back()->withErrors(['token' => 'Token tidak dapat diverifikasi.']);
        }
        try {
            $student = $students->eligibleStudent((int) $token->nis, $token->periode->tahun);
        } catch (\Throwable) {
            RateLimiter::hit($key, 120);

            return back()->withErrors(['token' => 'Token tidak dapat diverifikasi.']);
        }
        RateLimiter::clear($key);
        $request->session()->regenerate();
        $request->session()->put('pilketos.vote', ['token_id' => $token->id, 'kategori' => $student['kategori'], 'nonce' => str()->random(40)]);

        return redirect()->route('pilketos.vote');
    }

    public function vote(Request $request): View|RedirectResponse
    {
        $session = $request->session()->get('pilketos.vote');
        if (! is_array($session)) {
            return redirect()->route('pilketos.landing');
        }
        $token = PilketosToken::query()->with('periode')->find($session['token_id']);
        if ($token === null || $token->status !== 'tersedia') {
            $request->session()->forget('pilketos.vote');

            return redirect()->route('pilketos.landing');
        }
        $pasangan = PilketosPasangan::query()->where('periode_id', $token->periode_id)->where('kategori', $session['kategori'])->where('is_active', true)->orderBy('nomor_urut')->get();

        return view('pilketos.public.vote', compact('pasangan', 'session'));
    }

    public function confirm(Request $request, PilketosVotingService $voting): View|RedirectResponse
    {
        $session = $request->session()->get('pilketos.vote');
        $request->validate(['pasangan_id' => ['required', 'integer']]);
        if (! is_array($session)) {
            return redirect()->route('pilketos.landing');
        }
        $token = PilketosToken::query()->with('periode')->find($session['token_id'] ?? null);
        if ($token === null || $token->status !== 'tersedia' || ! $voting->isOpen($token->periode)) {
            $request->session()->forget('pilketos.vote');

            return redirect()->route('pilketos.landing')->withErrors(['token' => 'Sesi voting sudah tidak berlaku.']);
        }
        $pasangan = PilketosPasangan::query()
            ->whereKey($request->integer('pasangan_id'))
            ->where('periode_id', $token->periode_id)
            ->where('kategori', $session['kategori'])
            ->where('is_active', true)
            ->firstOrFail();

        return view('pilketos.public.confirm', compact('pasangan', 'session'));
    }

    public function submit(Request $request, PilketosVotingService $voting): RedirectResponse
    {
        $session = $request->session()->get('pilketos.vote');
        $request->validate(['pasangan_id' => ['required', 'integer'], 'nonce' => ['required', 'string']]);
        if (! is_array($session) || ! hash_equals($session['nonce'], $request->string('nonce')->toString())) {
            abort(419);
        }
        $voting->record((int) $session['token_id'], $request->integer('pasangan_id'));
        $request->session()->forget('pilketos.vote');
        $request->session()->regenerateToken();

        return redirect()->route('pilketos.done');
    }

    public function done(): View
    {
        return view('pilketos.public.done');
    }
}
