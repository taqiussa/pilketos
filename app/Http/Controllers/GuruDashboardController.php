<?php

namespace App\Http\Controllers;

use App\Models\Calon;
use App\Models\Siswa;
use App\Models\Vote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        // Statistik Guru
        $guruTotal = User::whereNull('nis')
            ->whereNotNull('username')
            ->where('active', true)
            ->count();

        $guruSudahVoting = Vote::whereNotNull('user_id')
            ->whereIn('user_id', User::whereNull('nis')->whereNotNull('username')->where('active', true)->pluck('id'))
            ->count();

        $guruBelumVoting = $guruTotal - $guruSudahVoting;

        return view('guru.dashboard', [
            'tahunAjaran' => $tahunAjaran,
            'siswaTotal' => $siswaTotal,
            'siswaPutra' => $siswaPutra,
            'siswaPutri' => $siswaPutri,
            'siswaSudahVoting' => $siswaSudahVoting,
            'siswaBelumVoting' => $siswaBelumVoting,
            'guruTotal' => $guruTotal,
            'guruSudahVoting' => $guruSudahVoting,
            'guruBelumVoting' => $guruBelumVoting,
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

        // Guru statistics for statistics page
        $guruTotal = User::whereNull('nis')
            ->whereNotNull('username')
            ->where('active', true)
            ->count();

        $guruSudahVoting = Vote::whereNotNull('user_id')
            ->whereIn('user_id', User::whereNull('nis')->whereNotNull('username')->where('active', true)->pluck('id'))
            ->count();

        $guruBelumVoting = $guruTotal - $guruSudahVoting;

        return view('guru.statistics', [
            'calonsPutra' => $calonsPutra,
            'calonsPutri' => $calonsPutri,
            'totalVotesPutra' => $totalVotesPutra,
            'totalVotesPutri' => $totalVotesPutri,
            'guruTotal' => $guruTotal,
            'guruSudahVoting' => $guruSudahVoting,
            'guruBelumVoting' => $guruBelumVoting,
        ]);
    }

    public function showVote(): View
    {
        $calons = Calon::orderBy('jenis_kelamin')->orderBy('nomor_urut')->get();

        $hasVotedGuru = Auth::check() ? Vote::where('user_id', Auth::id())->exists() : false;

        return view('guru.vote', [
            'calons' => $calons,
            'hasVotedGuru' => $hasVotedGuru,
        ]);
    }

    public function vote(Request $request)
    {
        $request->validate([
            'calon_id' => 'required|exists:calons,id',
        ]);

        $user = Auth::user();

        // ensure user is guru according to criteria
        if (! $user || $user->nis !== null || $user->username === null || ! $user->active) {
            abort(403);
        }

        // prevent double voting by guru
        if (Vote::where('user_id', $user->id)->exists()) {
            return redirect()->route('guru.dashboard')->with('error', 'Anda sudah melakukan voting.');
        }

        Vote::create([
            'nis' => null,
            'user_id' => $user->id,
            'calon_id' => $request->input('calon_id'),
        ]);

        return redirect()->route('guru.dashboard')->with('success', 'Terima kasih, suara Anda telah direkam.');
    }
}
