<?php

namespace App\Http\Controllers\Pilketos;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\PilketosPeriode;
use App\Models\PilketosToken;
use App\Services\PilketosTokenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TokenController extends Controller
{
    public function index(Request $request): View
    {
        $periodes = PilketosPeriode::query()->orderByDesc('tahun')->get();
        $periode = $periodes->firstWhere('id', $request->integer('periode')) ?? $periodes->first();
        $kelas = Kelas::query()->orderBy('tingkat')->orderBy('nama')->get();
        $tokens = $periode ? PilketosToken::query()->where('periode_id', $periode->id)->orderByDesc('id')->paginate(25, ['*'], 'tokens')->withQueryString() : collect();

        return view('pilketos.dashboard.token', compact('periodes', 'periode', 'kelas', 'tokens'));
    }

    public function generate(Request $request, PilketosTokenService $tokens): RedirectResponse
    {
        $data = $request->validate(['periode_id' => ['required', 'exists:pilketos_periodes,id'], 'kelas_id' => ['required', 'exists:kelas,id']]);
        $periode = PilketosPeriode::query()->findOrFail($data['periode_id']);
        $created = $tokens->generateForClass($periode, (int) $data['kelas_id'], $request->user()->id);

        return redirect()->route('pilketos.token.index')->with('success', count($created).' token baru dibuat. Pilih periode dan kelas untuk mencetak atau mengekspor token.');
    }

    public function replace(PilketosToken $token, PilketosTokenService $tokens): RedirectResponse
    {
        try {
            $tokens->replaceUnusedToken($token->id, request()->user()?->id);
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['token' => $exception->getMessage()]);
        }

        return redirect()->route('pilketos.token.index')->with('success', 'Token pengganti dibuat. Pilih periode dan kelas untuk mencetak atau mengekspor token.');
    }
}
