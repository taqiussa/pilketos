<?php

namespace App\Http\Controllers\Pilketos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pilketos\StorePeriodeRequest;
use App\Models\PilketosPeriode;
use App\Services\TahunAkademikResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PengaturanController extends Controller
{
    public function index(TahunAkademikResolver $tahun): View
    {
        $periodes = PilketosPeriode::query()->latest('tahun')->get();
        $resolved = $tahun->resolve();

        return view('pilketos.dashboard.pengaturan', compact('periodes', 'resolved'));
    }

    public function store(StorePeriodeRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['logo', 'banner']);
        $data['created_by'] = $request->user()->id;
        $data['hasil_publik'] = $request->boolean('hasil_publik');
        $this->storeAssets($request, $data);
        if ($data['status'] === 'aktif') {
            PilketosPeriode::query()->where('status', 'aktif')->update(['status' => 'ditutup']);
        }
        PilketosPeriode::query()->create($data);

        return back()->with('success', 'Periode pemilihan dibuat.');
    }

    public function update(StorePeriodeRequest $request, PilketosPeriode $periode): RedirectResponse
    {
        if ($periode->suara()->exists() && ($request->string('tahun')->toString() !== $periode->tahun || $request->string('status')->toString() === 'draft')) {
            return back()->withErrors(['periode' => 'Tahun periode bersuara tidak dapat diubah dan tidak dapat dikembalikan ke draft.']);
        }
        $data = $request->safe()->except(['logo', 'banner']);
        $data['hasil_publik'] = $request->boolean('hasil_publik');
        $obsoleteAssets = $this->storeAssets($request, $data, $periode);
        if ($data['status'] === 'aktif') {
            PilketosPeriode::query()->whereKeyNot($periode->id)->where('status', 'aktif')->update(['status' => 'ditutup']);
        }
        $periode->update($data);
        foreach ($obsoleteAssets as $path) {
            Storage::disk(config('pilketos.photo_disk'))->delete($path);
        }

        return back()->with('success', 'Pengaturan periode diperbarui.');
    }

    public function destroy(PilketosPeriode $periode): RedirectResponse
    {
        if ($periode->suara()->exists() || $periode->tokens()->exists() || $periode->hakPilih()->exists()) {
            return back()->withErrors(['periode' => 'Periode yang sudah memiliki token, hak pilih, atau suara tidak dapat dihapus.']);
        }
        $periode->delete();

        return back()->with('success', 'Periode dihapus.');
    }

    /** @param array<string, mixed> $data */
    private function storeAssets(Request $request, array &$data, ?PilketosPeriode $periode = null): array
    {
        $obsoleteAssets = [];
        foreach (['logo' => 'logo_path', 'banner' => 'banner_path'] as $input => $column) {
            if (! $request->hasFile($input)) {
                continue;
            }
            $data[$column] = $request->file($input)->store('pilketos/landing', config('pilketos.photo_disk'));
            if ($periode?->{$column} !== null) {
                $obsoleteAssets[] = $periode->{$column};
            }
        }

        return $obsoleteAssets;
    }
}
