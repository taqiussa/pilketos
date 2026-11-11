<?php

namespace App\Http\Controllers\Pilketos;

use App\Http\Controllers\Controller;
use App\Models\PilketosPasangan;
use App\Models\PilketosPeriode;
use App\Models\PilketosSuara;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class HasilController extends Controller
{
    public function index(Request $request): View
    {
        $periodes = PilketosPeriode::query()->latest('tahun')->get();
        $periode = $periodes->firstWhere('id', $request->integer('periode')) ?? $periodes->first();
        [$hasil, $total] = $this->resultData($periode);

        return view('pilketos.dashboard.hasil', compact('periodes', 'periode', 'hasil', 'total'));
    }

    public function pdf(PilketosPeriode $periode): Response
    {
        [$hasil, $total] = $this->resultData($periode);
        $filename = 'hasil-pilketos-'.str($periode->tahun)->slug().'.pdf';

        return Pdf::loadView('pilketos.pdf.hasil', compact('periode', 'hasil', 'total'))
            ->setPaper('a4')
            ->download($filename);
    }

    public function public(): View
    {
        $periode = PilketosPeriode::query()
            ->where('hasil_publik', true)
            ->whereIn('status', ['aktif', 'ditutup'])
            ->latest('id')
            ->firstOrFail();
        [$hasil, $total] = $this->resultData($periode);

        return view('pilketos.public.hasil', compact('periode', 'hasil', 'total'));
    }

    /** @return array{0: \Illuminate\Support\Collection<int, PilketosPasangan>, 1: int} */
    private function resultData(?PilketosPeriode $periode): array
    {
        if ($periode === null) {
            return [collect(), 0];
        }

        $total = PilketosSuara::query()->where('periode_id', $periode->id)->count();
        $counts = PilketosSuara::query()
            ->selectRaw('pasangan_id, count(*) as jumlah')
            ->where('periode_id', $periode->id)
            ->groupBy('pasangan_id')
            ->pluck('jumlah', 'pasangan_id');
        $hasil = PilketosPasangan::query()->where('periode_id', $periode->id)
            ->orderBy('kategori')->orderBy('nomor_urut')->get()
            ->map(function (PilketosPasangan $pasangan) use ($counts, $total): PilketosPasangan {
                $pasangan->jumlah_suara = (int) ($counts[$pasangan->id] ?? 0);
                $pasangan->persentase = $total === 0 ? 0 : round(($pasangan->jumlah_suara / $total) * 100, 2);

                return $pasangan;
            });

        return [$hasil, $total];
    }
}
