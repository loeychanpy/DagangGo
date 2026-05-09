<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/owner-dashboard', function () {
    return view('owner.index');
    })->middleware(['auth', 'role:owner']);
/*     // Halaman yang hanya bisa dibuka oleh Owner (Misal: Laporan Keuangan)
    Route::middleware(['auth', 'role:owner'])->group(function () {
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    });

    // Halaman yang bisa dibuka semua yang sudah Login (Dashboard & POS)
    Route::middleware(['auth'])->group(function () {
        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    }); */
});

require __DIR__.'/auth.php';
