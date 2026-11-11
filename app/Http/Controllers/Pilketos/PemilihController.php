<?php

namespace App\Http\Controllers\Pilketos;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\PilketosHakPilih;
use App\Models\PilketosPeriode;
use App\Models\PilketosToken;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PemilihController extends Controller
{
    public function index(Request $request): View
    {
        $periodes = PilketosPeriode::query()->latest('tahun')->get();
        $periode = $periodes->firstWhere('id', $request->integer('periode')) ?? $periodes->first();
        $kelas = Kelas::query()->orderBy('tingkat')->orderBy('nama')->get();
        $pemilih = collect();
        $rekapKelas = collect();
        $stats = ['total' => 0, 'sudah' => 0, 'belum' => 0, 'token_tersedia' => 0, 'token_digunakan' => 0];

        if ($periode !== null) {
            $siswa = Siswa::query()->where('tahun', $periode->tahun)->select('nis', 'kelas_id')->distinct();
            $query = PilketosHakPilih::query()->where('periode_id', $periode->id)
                ->joinSub($siswa, 'siswa_periode', fn ($join) => $join->on('pilketos_hak_pilih.nis', '=', 'siswa_periode.nis'))
                ->leftJoin('user_siswa', 'user_siswa.nis', '=', 'pilketos_hak_pilih.nis')
                ->leftJoin('biodata', 'biodata.nis', '=', 'pilketos_hak_pilih.nis')
                ->leftJoin('kelas', 'kelas.id', '=', 'siswa_periode.kelas_id')
                ->select('pilketos_hak_pilih.*', 'user_siswa.name', 'biodata.jenis_kelamin', 'kelas.tingkat', 'kelas.nama as nama_kelas');
            $query->when($request->filled('kelas_id'), fn ($q) => $q->where('siswa_periode.kelas_id', $request->integer('kelas_id')))
                ->when($request->filled('gender'), fn ($q) => $q->where('pilketos_hak_pilih.kategori', $request->string('gender')->toString()))
                ->when($request->filled('status'), fn ($q) => $q->where('pilketos_hak_pilih.status', $request->string('status')->toString()));
            $pemilih = $query->orderBy('kelas.tingkat')->orderBy('kelas.nama')->orderBy('user_siswa.name')->paginate(25)->withQueryString();
            $stats['total'] = PilketosHakPilih::query()->where('periode_id', $periode->id)->count();
            $stats['sudah'] = PilketosHakPilih::query()->where('periode_id', $periode->id)->where('status', 'sudah_memilih')->count();
            $stats['belum'] = max(0, $stats['total'] - $stats['sudah']);
            $stats['token_tersedia'] = PilketosToken::query()->where('periode_id', $periode->id)->where('status', 'tersedia')->count();
            $stats['token_digunakan'] = PilketosToken::query()->where('periode_id', $periode->id)->where('status', 'digunakan')->count();
            $rekapKelas = PilketosHakPilih::query()->where('pilketos_hak_pilih.periode_id', $periode->id)
                ->joinSub($siswa, 'siswa_rekap', fn ($join) => $join->on('pilketos_hak_pilih.nis', '=', 'siswa_rekap.nis'))
                ->join('kelas', 'kelas.id', '=', 'siswa_rekap.kelas_id')
                ->selectRaw('kelas.id, kelas.tingkat, kelas.nama, count(*) as total, sum(pilketos_hak_pilih.status = "sudah_memilih") as sudah')
                ->groupBy('kelas.id', 'kelas.tingkat', 'kelas.nama')->orderBy('kelas.tingkat')->orderBy('kelas.nama')->get();
        }

        return view('pilketos.dashboard.pemilih', compact('periodes', 'periode', 'kelas', 'pemilih', 'rekapKelas', 'stats'));
    }
}
