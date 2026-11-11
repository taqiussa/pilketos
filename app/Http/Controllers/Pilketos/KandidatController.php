<?php

namespace App\Http\Controllers\Pilketos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pilketos\StorePasanganRequest;
use App\Models\PilketosPasangan;
use App\Models\PilketosPeriode;
use App\Services\PilketosCandidateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KandidatController extends Controller
{
    public function index(Request $request): View
    {
        $periodes = PilketosPeriode::query()->orderByDesc('tahun')->get();
        $periode = $periodes->firstWhere('id', $request->integer('periode')) ?? $periodes->first();
        $pasangan = $periode ? PilketosPasangan::query()->where('periode_id', $periode->id)->orderBy('kategori')->orderBy('nomor_urut')->paginate(25)->withQueryString() : collect();

        return view('pilketos.dashboard.kandidat', compact('periodes', 'periode', 'pasangan'));
    }

    public function store(StorePasanganRequest $request, PilketosCandidateService $candidates): RedirectResponse
    {
        $data = $request->safe()->except(['foto_ketua', 'foto_wakil', 'foto_pasangan']);
        foreach (['foto_ketua', 'foto_wakil', 'foto_pasangan'] as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('pilketos/kandidat', config('pilketos.photo_disk'));
            }
        }
        $candidates->create($data);

        return back()->with('success', 'Pasangan kandidat ditambahkan.');
    }

    public function destroy(PilketosPasangan $pasangan): RedirectResponse
    {
        if ($pasangan->suara()->exists()) {
            return back()->withErrors(['pasangan' => 'Pasangan yang sudah memiliki suara tidak dapat dihapus.']);
        }
        $pasangan->delete();

        return back()->with('success', 'Pasangan dihapus.');
    }
}
