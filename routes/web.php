<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return redirect()->route('login');
});



Route::middleware('auth')->group(function () {
    #Dashboard untuk semua yang sudah login (Owner & Staff)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    #Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    /* #Contoh route yang hanya bisa diakses oleh Owner (Misal: Halaman khusus Owner)
     Route::get('/owner-dashboard', function () {
        return view('owner.index');
    })->middleware(['auth', 'role:owner']); */
/* 
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store'); */

     // Halaman yang hanya bisa dibuka oleh Owner (Misal: Laporan Keuangan)
    Route::middleware(['auth', 'role:owner'])->group(function () {
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index'); 

        Route::resource('users', UserController::class);
    });

    // Halaman yang bisa dibuka semua yang sudah Login (Dashboard & POS)
    Route::middleware(['auth' , 'role:staff'])->group(function () {
        //Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    }); 
    
});

require __DIR__.'/auth.php';
