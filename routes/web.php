<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DeliveryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Routes untuk semua user yang sudah login (Owner & Staff)
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Delivery & Surat Jalan
    Route::get('/transactions/{transaction}/surat-jalan', [DeliveryController::class, 'printSuratJalan'])
        ->name('transactions.surat-jalan');

    // Transaction / POS routes (accessible to both owner and staff)
    Route::get('/transaction', [TransactionController::class, 'index'])->name('transaction.index');
    Route::post('/transaction', [TransactionController::class, 'store']);
    Route::post('/transaction/cart/add', [TransactionController::class, 'addToCart'])->name('cart.add');
    Route::post('/transaction/cart/remove', [TransactionController::class, 'removeFromCart'])->name('cart.remove');
    Route::post('/transaction/checkout', [TransactionController::class, 'checkout']);


});

// Routes khusus Owner (Laporan, User Management, dll)
Route::middleware(['auth', 'role:owner'])->group(function () {
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::resource('users', UserController::class);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:staff'])->group(function () {
    
});


require __DIR__.'/auth.php';
