<?php

namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Ringkasan Stok (Stabilitas Sistem)
        $lowStockProducts = Product::whereColumn('stock', '<=', 'min_stock')
                                    ->with(['unit', 'category'])
                                    ->get();

        // 2. Ringkasan Keuangan Hari Ini (Akurasi Data)
        $todaySales = Transaction::whereDate('created_at', Carbon::today())->sum('total_price');
        $todayTransactions = Transaction::whereDate('created_at', Carbon::today())->count();
        
        // 3. Total Piutang (Remaining Bills)
        $totalReceivables = Transaction::where('status', '!=', 'paid')->sum('remaining_bill');

        return view('dashboard', compact(
            'lowStockProducts', 
            'todaySales', 
            'todayTransactions', 
            'totalReceivables'
        ));
    }
}
