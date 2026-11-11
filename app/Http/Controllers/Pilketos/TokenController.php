<?php

namespace App\Http\Controllers\Pilketos;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\PilketosPeriode;
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

        return view('pilketos.dashboard.token', compact('periodes', 'periode', 'kelas'));
    }

    public function generate(Request $request, PilketosTokenService $tokens): RedirectResponse
    {
        $data = $request->validate(['periode_id' => ['required', 'exists:pilketos_periodes,id'], 'kelas_id' => ['required', 'exists:kelas,id']]);
        $periode = PilketosPeriode::query()->findOrFail($data['periode_id']);
        $print = $tokens->generateForClass($periode, (int) $data['kelas_id'], $request->user()->id);
        $request->session()->flash('pilketos.tokens.print', $print);

        return redirect()->route('pilketos.token.index', ['periode' => $periode->id])->with('success', count($print).' token baru dibuat. Cetak sekarang; token plaintext tidak disimpan.');
    }
}
