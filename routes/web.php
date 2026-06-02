<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
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
    
    #Transaction routes
    Route::get('/transaction',[TransactionController::class,'index'])->middleware('auth')->name('transaction.index');
    Route::post('/transaction',[TransactionController::class,'store']);
    #Route untuk menambahkan produk ke keranjang
    Route::post('/transaction/cart/add',[TransactionController::class,'addToCart'])->name('cart.add');
    #Route untuk menghapus produk dari keranjang
    Route::post('/transaction/cart/remove',[TransactionController::class,'removeFromCart']) ->name('cart.remove');
    #Route untuk checkout transaksi
    Route::post('/transaction/checkout',[TransactionController::class,'checkout'])->middleware('auth');

    #Inventory routes
    Route::get('/inventory',[ProductController::class,'index'])->middleware('auth')->name('inventory.index');
    Route::get('/inventory/create',[ProductController::class,'create'])->name('inventory.create');
    Route::post('/inventory/store',[ProductController::class,'store'])->name('inventory.store');
    Route::get('/inventory/{product}/edit',[ProductController::class,'edit'])->name('inventory.edit');
    Route::put('/inventory/{product}',[ProductController::class,'update'])->name('inventory.update');
    Route::delete('/inventory/{product}',[ProductController::class,'destroy'])->name('inventory.destroy');
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
