<?php

use App\Http\Controllers\GuruAuthController;
use App\Http\Controllers\Pilketos\DashboardController;
use App\Http\Controllers\Pilketos\HasilController;
use App\Http\Controllers\Pilketos\KandidatController;
use App\Http\Controllers\Pilketos\PemilihController;
use App\Http\Controllers\Pilketos\PengaturanController;
use App\Http\Controllers\Pilketos\PublicVotingController;
use App\Http\Controllers\Pilketos\TokenController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

// Routes untuk OSIS Voting - Landing Page
Route::get('/', [PublicVotingController::class, 'landing'])->name('pilketos.landing');
Route::post('/verifikasi-token', [PublicVotingController::class, 'verify'])->middleware('throttle:pilketos-token')->name('pilketos.verify');
Route::get('/voting', [PublicVotingController::class, 'vote'])->name('pilketos.vote');
Route::post('/voting/konfirmasi', [PublicVotingController::class, 'confirm'])->name('pilketos.confirm');
Route::post('/voting/kirim', [PublicVotingController::class, 'submit'])->name('pilketos.submit');
Route::get('/voting/selesai', [PublicVotingController::class, 'done'])->name('pilketos.done');
Route::get('/hasil', [HasilController::class, 'public'])->name('pilketos.hasil.public');

// Routes untuk Guru Login dan Dashboard
Route::middleware('guest')->group(function () {
    Route::get('/login', [GuruAuthController::class, 'showLoginForm'])->name('guru.login');
    Route::post('/login', [GuruAuthController::class, 'login'])->name('guru.login.post');
});

Route::middleware('auth:web')->group(function () {
    Route::post('/logout', [GuruAuthController::class, 'logout'])->name('guru.logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('guru.dashboard');
    Route::get('/dashboard/kandidat', [KandidatController::class, 'index'])->name('pilketos.kandidat.index');
    Route::get('/dashboard/kandidat/siswa', [KandidatController::class, 'lookup'])->name('pilketos.kandidat.lookup');
    Route::post('/dashboard/kandidat', [KandidatController::class, 'store'])->name('pilketos.kandidat.store');
    Route::put('/dashboard/kandidat/{pasangan}', [KandidatController::class, 'update'])->name('pilketos.kandidat.update');
    Route::patch('/dashboard/kandidat/{pasangan}/status', [KandidatController::class, 'toggle'])->name('pilketos.kandidat.toggle');
    Route::delete('/dashboard/kandidat/{pasangan}', [KandidatController::class, 'destroy'])->name('pilketos.kandidat.destroy');
    Route::get('/dashboard/token', [TokenController::class, 'index'])->name('pilketos.token.index');
    Route::post('/dashboard/token', [TokenController::class, 'generate'])->name('pilketos.token.generate');
    Route::post('/dashboard/token/{token}/replace', [TokenController::class, 'replace'])->name('pilketos.token.replace');
    Route::get('/dashboard/pemilih', [PemilihController::class, 'index'])->name('pilketos.pemilih.index');
    Route::get('/dashboard/hasil', [HasilController::class, 'index'])->name('pilketos.hasil.index');
    Route::get('/dashboard/hasil/{periode}/pdf', [HasilController::class, 'pdf'])->name('pilketos.hasil.pdf');
    Route::get('/dashboard/pengaturan', [PengaturanController::class, 'index'])->name('pilketos.pengaturan.index');
    Route::post('/dashboard/pengaturan', [PengaturanController::class, 'store'])->name('pilketos.pengaturan.store');
    Route::put('/dashboard/pengaturan/{periode}', [PengaturanController::class, 'update'])->name('pilketos.pengaturan.update');
    Route::delete('/dashboard/pengaturan/{periode}', [PengaturanController::class, 'destroy'])->name('pilketos.pengaturan.destroy');
});

// require __DIR__ . '/auth.php';
