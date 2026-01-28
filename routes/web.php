<?php

use App\Http\Controllers\OsisController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

// Routes untuk OSIS Voting - Landing Page
Route::get('/', [OsisController::class, 'landingPage'])->name('osis.landing');
Route::get('/voting', [OsisController::class, 'voting'])->name('osis.voting');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__ . '/auth.php';
