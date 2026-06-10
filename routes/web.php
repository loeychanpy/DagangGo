<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\UnitController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Routes untuk semua user yang sudah login (Owner & Staff)
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Delivery & Surat Jalan
    Route::get('/transactions/{transaction}/surat-jalan', [DeliveryController::class, 'printSuratJalan'])
        ->name('transactions.surat-jalan');
    Route::post('/transactions/{transaction}/delivery', [DeliveryController::class, 'store'])
        ->name('transactions.delivery.store');

    // Customer routes (staff & owner)
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');

    // Transaction / POS routes
    Route::get('/transaction', [TransactionController::class, 'index'])->name('transaction.index');
    Route::post('/transaction/cart/add', [TransactionController::class, 'addToCart'])->name('cart.add');
    Route::post('/transaction/cart/remove', [TransactionController::class, 'removeFromCart'])->name('cart.remove');
    Route::post('/transaction/checkout', [TransactionController::class, 'checkout'])->name('transaction.checkout');

    // Invoice accessible to all authenticated users (staff may need to reprint)
    Route::get('/transactions/{transaction}/invoice', [LaporanController::class, 'exportInvoice'])->name('transactions.invoice');

    Route::get('/inventory', [ProductController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/create', [ProductController::class, 'create'])->name('inventory.create');
    Route::post('/inventory/store', [ProductController::class, 'store'])->name('inventory.store');
    Route::get('/inventory/{product}/edit', [ProductController::class, 'edit'])->name('inventory.edit');
    Route::put('/inventory/{product}', [ProductController::class, 'update'])->name('inventory.update');
    Route::delete('/inventory/{product}', [ProductController::class, 'destroy'])->name('inventory.destroy');

    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::post('/units', [UnitController::class, 'store'])->name('units.store');
});

// Routes khusus Owner
Route::middleware(['auth', 'role:owner'])->group(function () {
    // Financial reports — owner only
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/pdf', [LaporanController::class, 'exportPdf'])->name('laporan.pdf');
    Route::post('/laporan/transactions/{transaction}/pay', [LaporanController::class, 'recordPayment'])->name('laporan.pay');

    Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:staff'])->group(function () {
});


require __DIR__.'/auth.php';
