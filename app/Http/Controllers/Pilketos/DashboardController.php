<?php

namespace App\Http\Controllers\Pilketos;

use App\Http\Controllers\Controller;
use App\Models\Biodata;
use App\Models\PilketosHakPilih;
use App\Models\PilketosPasangan;
use App\Models\PilketosPeriode;
use App\Models\PilketosToken;
use App\Models\Siswa;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $periode = PilketosPeriode::query()->where('status', 'aktif')->latest('id')->first() ?? PilketosPeriode::query()->latest('id')->first();
        $stats = collect(['total_siswa' => 0, 'putra' => 0, 'putri' => 0, 'pasangan_putra' => 0, 'pasangan_putri' => 0, 'token_tersedia' => 0, 'token_digunakan' => 0, 'sudah_memilih' => 0, 'belum_memilih' => 0]);
        if ($periode !== null) {
            $base = Siswa::query()->where('tahun', $periode->tahun)->select('nis')->distinct();
            $stats['total_siswa'] = $base->count();
            $stats['putra'] = Biodata::query()->where('jenis_kelamin', 'L')->whereIn('nis', (clone $base)->pluck('nis'))->count();
            $stats['putri'] = Biodata::query()->where('jenis_kelamin', 'P')->whereIn('nis', (clone $base)->pluck('nis'))->count();
            $stats['pasangan_putra'] = PilketosPasangan::query()->where('periode_id', $periode->id)->where('kategori', 'L')->count();
            $stats['pasangan_putri'] = PilketosPasangan::query()->where('periode_id', $periode->id)->where('kategori', 'P')->count();
            $stats['token_tersedia'] = PilketosToken::query()->where('periode_id', $periode->id)->where('status', 'tersedia')->count();
            $stats['token_digunakan'] = PilketosToken::query()->where('periode_id', $periode->id)->where('status', 'digunakan')->count();
            $stats['sudah_memilih'] = PilketosHakPilih::query()->where('periode_id', $periode->id)->where('status', 'sudah_memilih')->count();
            $stats['belum_memilih'] = max(0, $stats['total_siswa'] - $stats['sudah_memilih']);
        }

        return view('pilketos.dashboard.index', compact('periode', 'stats'));
    }
}
