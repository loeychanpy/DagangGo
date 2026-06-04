<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use Illuminate\Http\Request;
use App\Models\Transaction;

class DeliveryController extends Controller
{
    public function store(Request $request, Transaction $transaction)
    {
        $request->validate([
            'shipping_address' => 'required|string|max:500',
            'driver_name'      => 'nullable|string|max:100',
            'license_plate'    => 'nullable|string|max:20',
        ]);

        Delivery::updateOrCreate(
            ['transaction_id' => $transaction->id],
            [
                'shipping_address' => $request->shipping_address,
                'driver_name'      => $request->driver_name,
                'license_plate'    => $request->license_plate,
                'status'           => 'pending',
            ]
        );

        return response()->json([
            'print_url' => route('transactions.surat-jalan', $transaction->id),
        ]);
    }

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
