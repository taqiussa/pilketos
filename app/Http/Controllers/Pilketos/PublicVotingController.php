<?php

namespace App\Http\Controllers\Pilketos;

use App\Http\Controllers\Controller;
use App\Models\PilketosPasangan;
use App\Models\PilketosPeriode;
use App\Services\PilketosTokenVerificationService;
use App\Services\PilketosVotingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicVotingController extends Controller
{
    public function landing(): View
    {
        $periode = PilketosPeriode::query()->where('status', 'aktif')->orderByDesc('id')->first();

        return view('pilketos.public.landing', compact('periode'));
    }

    public function verify(Request $request, PilketosTokenVerificationService $verification): RedirectResponse
    {
        $request->validate(['token' => ['required', 'string', 'size:6']]);
        try {
            $verification->verify($request->string('token')->toString(), $request->ip());
        } catch (\Illuminate\Validation\ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

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
