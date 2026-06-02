<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;

class DeliveryController extends Controller
{
    public function printSuratJalan(Transaction $transaction)
    {
        // Pastikan kita meload relasi detail barang dan pengirimannya
        $transaction->load(['details.product', 'delivery']);

        // Jika tidak ada data pengiriman, tolak akses
        if (!$transaction->delivery) {
            return redirect()->back()->withErrors('Transaksi ini tidak memiliki data pengiriman / Surat Jalan.');
        }

        // Tampilkan halaman khusus cetak
        return view('deliveries.surat_jalan', compact('transaction'));
    }
}
