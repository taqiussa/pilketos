<?php

namespace App\Http\Controllers\Pilketos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pilketos\StorePasanganRequest;
use App\Models\PilketosPasangan;
use App\Models\PilketosPeriode;
use App\Services\PilketosCandidateService;
use App\Services\PilketosPhotoUploadService;
use App\Services\PilketosStudentDirectory;
use Illuminate\Http\JsonResponse;
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

    public function store(StorePasanganRequest $request, PilketosCandidateService $candidates, PilketosPhotoUploadService $photos): RedirectResponse
    {
        $data = $request->safe()->all();
        if (blank($data['foto_pasangan'] ?? null)) {
            unset($data['foto_pasangan']);
        }
        $intent = isset($data['foto_pasangan']) ? $photos->verifyFor($request->user(), $data['foto_pasangan']) : null;
        $candidates->create($data);
        if ($intent !== null) {
            $photos->consume($intent);
        }

        return back()->with('success', 'Pasangan kandidat ditambahkan.');
    }

    public function lookup(Request $request, PilketosStudentDirectory $students): JsonResponse
    {
        $data = $request->validate([
            'periode_id' => ['required', 'integer', 'exists:pilketos_periodes,id'],
            'kategori' => ['required', 'in:L,P'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $periode = PilketosPeriode::query()->findOrFail($data['periode_id']);

        return response()->json(['data' => $students->eligibleStudents($periode->tahun, $data['kategori'])]);
    }

    public function destroy(PilketosPasangan $pasangan): RedirectResponse
    {
        if ($pasangan->suara()->exists()) {
            return back()->withErrors(['pasangan' => 'Pasangan yang sudah memiliki suara tidak dapat dihapus.']);
        }
        $pasangan->delete();

        return back()->with('success', 'Pasangan dihapus.');
    }

    public function update(StorePasanganRequest $request, PilketosPasangan $pasangan, PilketosCandidateService $candidates, PilketosPhotoUploadService $photos): RedirectResponse
    {
        if ($pasangan->suara()->exists() && ((int) $request->integer('ketua_nis') !== (int) $pasangan->ketua_nis || (int) $request->integer('wakil_nis') !== (int) $pasangan->wakil_nis || $request->string('kategori')->toString() !== $pasangan->kategori)) {
            return back()->withErrors(['pasangan' => 'Identitas pasangan yang sudah menerima suara tidak dapat diubah.']);
        }
        $data = $request->safe()->all();
        if (blank($data['foto_pasangan'] ?? null)) {
            unset($data['foto_pasangan']);
        }
        $intent = isset($data['foto_pasangan']) ? $photos->verifyFor($request->user(), $data['foto_pasangan']) : null;
        $candidates->update($pasangan, $data);
        if ($intent !== null) {
            $photos->consume($intent);
        }

        return back()->with('success', 'Pasangan kandidat diperbarui.');
    }

    public function toggle(Request $request, PilketosPasangan $pasangan): RedirectResponse
    {
        $pasangan->update(['is_active' => ! $pasangan->is_active]);

        return back()->with('success', $pasangan->is_active ? 'Pasangan diaktifkan.' : 'Pasangan dinonaktifkan.');
    }
}
