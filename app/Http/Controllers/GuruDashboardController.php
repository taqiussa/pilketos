<?php

namespace App\Http\Controllers;

use App\Models\Calon;
use App\Models\Siswa;
use App\Models\Vote;
use Illuminate\View\View;

class GuruDashboardController extends Controller
{
    public function index(): View
    {
        $tahunAjaran = '2025 / 2026';

        // Statistik Siswa
        $siswaTotal = Siswa::where('tahun', $tahunAjaran)->count();
        $siswaPutra = Siswa::whereHas('biodata', function ($query) {
            $query->where('jenis_kelamin', 'L');
        })->where('tahun', $tahunAjaran)->count();
        $siswaPutri = Siswa::whereHas('biodata', function ($query) {
            $query->where('jenis_kelamin', 'P');
        })->where('tahun', $tahunAjaran)->count();

        // Statistik Voting
        $siswaSudahVoting = Vote::distinct('nis')
            ->whereIn('nis', Siswa::where('tahun', $tahunAjaran)->pluck('nis'))
            ->count();
        $siswaBelumVoting = $siswaTotal - $siswaSudahVoting;

        return view('guru.dashboard', [
            'tahunAjaran' => $tahunAjaran,
            'siswaTotal' => $siswaTotal,
            'siswaPutra' => $siswaPutra,
            'siswaPutri' => $siswaPutri,
            'siswaSudahVoting' => $siswaSudahVoting,
            'siswaBelumVoting' => $siswaBelumVoting,
        ]);
    }

    public function statistics(): View
    {
        // Get voting results per calon berdasarkan jenis kelamin
        $calonsPutra = Calon::where('jenis_kelamin', 'putra')
            ->withCount('votes')
            ->orderBy('nomor_urut')
            ->get();

        $calonsPutri = Calon::where('jenis_kelamin', 'putri')
            ->withCount('votes')
            ->orderBy('nomor_urut')
            ->get();

        $totalVotesPutra = $calonsPutra->sum('votes_count');
        $totalVotesPutri = $calonsPutri->sum('votes_count');

        return view('guru.statistics', [
            'calonsPutra' => $calonsPutra,
            'calonsPutri' => $calonsPutri,
            'totalVotesPutra' => $totalVotesPutra,
            'totalVotesPutri' => $totalVotesPutri,
        ]);
    }
}
