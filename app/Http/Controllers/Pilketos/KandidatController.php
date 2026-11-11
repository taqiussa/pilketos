<?php

namespace App\Http\Controllers\Pilketos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pilketos\StorePasanganRequest;
use App\Models\PilketosPasangan;
use App\Models\PilketosPeriode;
use App\Services\PilketosCandidateService;
use App\Services\PilketosStudentDirectory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

    public function lookup(Request $request, PilketosStudentDirectory $students): JsonResponse
    {
        $data = $request->validate([
            'periode_id' => ['required', 'integer', 'exists:pilketos_periodes,id'],
            'nis' => ['required', 'integer'],
            'kategori' => ['required', 'in:L,P'],
        ]);
        $periode = PilketosPeriode::query()->findOrFail($data['periode_id']);

        try {
            return response()->json($students->eligibleStudent((int) $data['nis'], $periode->tahun, $data['kategori']));
        } catch (\Illuminate\Validation\ValidationException) {
            return response()->json(['message' => 'Siswa tidak memenuhi syarat untuk periode atau kategori ini.'], 422);
        }
    }

    public function destroy(PilketosPasangan $pasangan): RedirectResponse
    {
        if ($pasangan->suara()->exists()) {
            return back()->withErrors(['pasangan' => 'Pasangan yang sudah memiliki suara tidak dapat dihapus.']);
        }
        $pasangan->delete();

        return back()->with('success', 'Pasangan dihapus.');
    }

    public function update(StorePasanganRequest $request, PilketosPasangan $pasangan, PilketosCandidateService $candidates): RedirectResponse
    {
        if ($pasangan->suara()->exists() && ((int) $request->integer('ketua_nis') !== (int) $pasangan->ketua_nis || (int) $request->integer('wakil_nis') !== (int) $pasangan->wakil_nis || $request->string('kategori')->toString() !== $pasangan->kategori)) {
            return back()->withErrors(['pasangan' => 'Identitas pasangan yang sudah menerima suara tidak dapat diubah.']);
        }
        $data = $request->safe()->except(['foto_ketua', 'foto_wakil', 'foto_pasangan']);
        $obsoletePhotos = [];
        foreach (['foto_ketua', 'foto_wakil', 'foto_pasangan'] as $field) {
            if (! $request->hasFile($field)) {
                continue;
            }
            $path = $request->file($field)->store('pilketos/kandidat', config('pilketos.photo_disk'));
            if ($pasangan->{$field} !== null) {
                $obsoletePhotos[] = $pasangan->{$field};
            }
            $data[$field] = $path;
        }
        $candidates->update($pasangan, $data);
        foreach ($obsoletePhotos as $path) {
            Storage::disk(config('pilketos.photo_disk'))->delete($path);
        }

        return back()->with('success', 'Pasangan kandidat diperbarui.');
    }

    public function toggle(Request $request, PilketosPasangan $pasangan): RedirectResponse
    {
        $pasangan->update(['is_active' => ! $pasangan->is_active]);

        return back()->with('success', $pasangan->is_active ? 'Pasangan diaktifkan.' : 'Pasangan dinonaktifkan.');
    }
}
