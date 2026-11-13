<?php

use App\Http\Controllers\GuruAuthController;
use App\Http\Controllers\Pilketos\HasilController;
use App\Http\Controllers\Pilketos\KandidatController;
use App\Http\Controllers\Pilketos\PengaturanController;
use App\Http\Controllers\Pilketos\PresignPasanganPhotoController;
use App\Http\Controllers\Pilketos\PublicVotingController;
use App\Http\Controllers\Pilketos\TeacherTokenReportController;
use App\Http\Controllers\Pilketos\TokenController;
use App\Http\Controllers\Pilketos\TokenReportController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

// Routes untuk OSIS Voting - Landing Page
Volt::route('/', 'pages.pilketos.landing')->name('pilketos.landing');
Volt::route('/masukkan-token', 'pages.pilketos.enter-token')->middleware('cache.headers:private;no_store')->name('pilketos.enter-token');
Route::post('/verifikasi-token', [PublicVotingController::class, 'verify'])->middleware('throttle:pilketos-token')->name('pilketos.verify');
Volt::route('/voting', 'pages.pilketos.vote')->middleware('cache.headers:private;no_store')->name('pilketos.vote');
Volt::route('/voting/konfirmasi', 'pages.pilketos.confirm')->middleware('cache.headers:private;no_store')->name('pilketos.confirm');
Route::post('/voting/kirim', [PublicVotingController::class, 'submit'])->name('pilketos.submit');
Volt::route('/voting/selesai', 'pages.pilketos.done')->middleware('cache.headers:private;no_store')->name('pilketos.done');
Volt::route('/hasil', 'pages.pilketos.public-results')->name('pilketos.hasil.public');

// Routes untuk Guru Login dan Dashboard
Route::middleware('guest')->group(function () {
    Route::get('/login', [GuruAuthController::class, 'showLoginForm'])->name('guru.login');
    Route::post('/login', [GuruAuthController::class, 'login'])->name('guru.login.post');
});

Route::middleware('auth:web')->group(function () {
    Route::post('/logout', [GuruAuthController::class, 'logout'])->name('guru.logout');
    Volt::route('/dashboard', 'pages.pilketos.dashboard')->name('guru.dashboard');
    Volt::route('/dashboard/kandidat', 'pages.pilketos.candidates')->name('pilketos.kandidat.index');
    Route::get('/dashboard/kandidat/siswa', [KandidatController::class, 'lookup'])->name('pilketos.kandidat.lookup');
    Route::post('/dashboard/kandidat/foto/presign', PresignPasanganPhotoController::class)->name('pilketos.kandidat.photo.presign');
    Route::post('/dashboard/kandidat', [KandidatController::class, 'store'])->name('pilketos.kandidat.store');
    Route::put('/dashboard/kandidat/{pasangan}', [KandidatController::class, 'update'])->name('pilketos.kandidat.update');
    Route::patch('/dashboard/kandidat/{pasangan}/status', [KandidatController::class, 'toggle'])->name('pilketos.kandidat.toggle');
    Route::delete('/dashboard/kandidat/{pasangan}', [KandidatController::class, 'destroy'])->name('pilketos.kandidat.destroy');
    Volt::route('/dashboard/token', 'pages.pilketos.tokens')->name('pilketos.token.index');
    Volt::route('/dashboard/token-guru', 'pages.pilketos.teacher-tokens')->name('pilketos.token-guru.index');
    Route::get('/dashboard/token-guru/print', [TeacherTokenReportController::class, 'print'])->name('pilketos.token-guru.print');
    Route::get('/dashboard/token-guru/export', [TeacherTokenReportController::class, 'export'])->name('pilketos.token-guru.export');
    Route::post('/dashboard/token', [TokenController::class, 'generate'])->name('pilketos.token.generate');
    Route::post('/dashboard/token/{token}/replace', [TokenController::class, 'replace'])->name('pilketos.token.replace');
    Route::get('/dashboard/token/print', [TokenReportController::class, 'print'])->name('pilketos.token.print');
    Route::get('/dashboard/token/export', [TokenReportController::class, 'export'])->name('pilketos.token.export');
    Volt::route('/dashboard/pemilih', 'pages.pilketos.voters')->name('pilketos.pemilih.index');
    Volt::route('/dashboard/pemilih-guru', 'pages.pilketos.teacher-voters')->name('pilketos.pemilih-guru.index');
    Volt::route('/dashboard/hasil', 'pages.pilketos.results')->name('pilketos.hasil.index');
    Route::get('/dashboard/hasil/{periode}/pdf', [HasilController::class, 'pdf'])->name('pilketos.hasil.pdf');
    Volt::route('/dashboard/pengaturan', 'pages.pilketos.settings')->name('pilketos.pengaturan.index');
    Route::post('/dashboard/pengaturan', [PengaturanController::class, 'store'])->name('pilketos.pengaturan.store');
    Route::put('/dashboard/pengaturan/{periode}', [PengaturanController::class, 'update'])->name('pilketos.pengaturan.update');
    Route::delete('/dashboard/pengaturan/{periode}', [PengaturanController::class, 'destroy'])->name('pilketos.pengaturan.destroy');
});

// require __DIR__ . '/auth.php';
