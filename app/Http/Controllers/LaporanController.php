<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        // Default rentang waktu: bulan ini
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());

        // Ambil data transaksi berdasarkan rentang tanggal
        $transactions = Transaction::with(['user', 'customer'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Hitung total ringkasan
        $totalSales = $transactions->sum('total_price');
        $totalReceived = $transactions->sum('pay_amount');
        $totalDebt = $transactions->sum('remaining_bill');

        return view('laporan.index', compact('transactions', 'totalSales', 'totalReceived', 'totalDebt', 'startDate', 'endDate'));
    }
}