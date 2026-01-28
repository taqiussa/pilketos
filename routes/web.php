<?php

use App\Http\Controllers\GuruAuthController;
use App\Http\Controllers\GuruDashboardController;
use App\Http\Controllers\OsisController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

// Routes untuk OSIS Voting - Landing Page
Route::get('/', [OsisController::class, 'landingPage'])->name('osis.landing');
Route::get('/voting', [OsisController::class, 'voting'])->name('osis.voting');

// Routes untuk Guru Login dan Dashboard
Route::middleware('guest')->group(function () {
    Route::get('/login', [GuruAuthController::class, 'showLoginForm'])->name('guru.login');
    Route::post('/login', [GuruAuthController::class, 'login'])->name('guru.login.post');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [GuruAuthController::class, 'logout'])->name('guru.logout');
    Route::get('/dashboard', [GuruDashboardController::class, 'index'])->name('guru.dashboard');
    Route::get('/statistics', [GuruDashboardController::class, 'statistics'])->name('guru.statistics');
    Route::get('/dashboard/vote', [GuruDashboardController::class, 'showVote'])->name('guru.vote');
    Route::post('/dashboard/vote', [GuruDashboardController::class, 'vote'])->name('guru.vote.post');
});

// require __DIR__ . '/auth.php';
