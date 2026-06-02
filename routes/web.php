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
    #Dashboard untuk semua yang sudah login (Owner & Staff)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/transactions/{transaction}/surat-jalan', [DeliveryController::class, 'printSuratJalan'])
        ->name('transactions.surat-jalan');

    #Profile routes

    
/* 
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store'); */

     // Halaman yang hanya bisa dibuka oleh Owner (Misal: Laporan Keuangan)
    Route::middleware(['auth', 'role:owner'])->group(function () {
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index'); 
        Route::resource('users', UserController::class);
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    // Halaman yang bisa dibuka staff yang sudah Login (Dashboard & POS)
    Route::middleware(['auth' , 'role:staff'])->group(function () {
        //Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    }); 
    
});

require __DIR__.'/auth.php';
