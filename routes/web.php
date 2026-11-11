<?php

use App\Http\Controllers\GuruAuthController;
use App\Http\Controllers\GuruDashboardController;
use App\Http\Controllers\Pilketos\DashboardController;
use App\Http\Controllers\Pilketos\KandidatController;
use App\Http\Controllers\Pilketos\PublicVotingController;
use App\Http\Controllers\Pilketos\TokenController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

// Routes untuk OSIS Voting - Landing Page
Route::get('/', [PublicVotingController::class, 'landing'])->name('pilketos.landing');
Route::post('/verifikasi-token', [PublicVotingController::class, 'verify'])->middleware('pilketos.token')->name('pilketos.verify');
Route::get('/voting', [PublicVotingController::class, 'vote'])->name('pilketos.vote');
Route::post('/voting/konfirmasi', [PublicVotingController::class, 'confirm'])->name('pilketos.confirm');
Route::post('/voting/kirim', [PublicVotingController::class, 'submit'])->name('pilketos.submit');
Route::get('/voting/selesai', [PublicVotingController::class, 'done'])->name('pilketos.done');

// Routes untuk Guru Login dan Dashboard
Route::middleware('guest')->group(function () {
    Route::get('/login', [GuruAuthController::class, 'showLoginForm'])->name('guru.login');
    Route::post('/login', [GuruAuthController::class, 'login'])->name('guru.login.post');
});

Route::middleware('auth:web')->group(function () {
    Route::post('/logout', [GuruAuthController::class, 'logout'])->name('guru.logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('guru.dashboard');
    Route::get('/dashboard/kandidat', [KandidatController::class, 'index'])->name('pilketos.kandidat.index');
    Route::post('/dashboard/kandidat', [KandidatController::class, 'store'])->name('pilketos.kandidat.store');
    Route::delete('/dashboard/kandidat/{pasangan}', [KandidatController::class, 'destroy'])->name('pilketos.kandidat.destroy');
    Route::get('/dashboard/token', [TokenController::class, 'index'])->name('pilketos.token.index');
    Route::post('/dashboard/token', [TokenController::class, 'generate'])->name('pilketos.token.generate');
    Route::get('/statistics', [GuruDashboardController::class, 'statistics'])->name('guru.statistics');
    Route::get('/dashboard/vote', [GuruDashboardController::class, 'showVote'])->name('guru.vote');
    Route::post('/dashboard/vote', [GuruDashboardController::class, 'vote'])->name('guru.vote.post');
});

// require __DIR__ . '/auth.php';
