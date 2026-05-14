<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\TransactionPayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'payment_method' => ['required', 'in:cash,transfer,qris,tempo'],
            'pay_amount' => ['required', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'due_date' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
        ]);

        return DB::transaction(function () use ($validated) {
            // 1. Hitung Subtotal & Validasi Stok Terlebih Dahulu
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['id']);

                if ($product->stock < $item['qty']) {
                    throw new \Exception("Stok tidak mencukupi untuk barang: {$product->name}. Sisa stok: {$product->stock}");
                }

                $subtotal += $item['qty'] * $item['price'];
            }

            // 2. Logika Perhitungan Keuangan (Tetap Sama)
            $discount = $validated['discount'] ?? 0;
            $tax = $validated['tax'] ?? 0;
            $totalPrice = $subtotal - $discount + $tax;
            $payAmount = $validated['pay_amount'];
            $changeAmount = max(0, $payAmount - $totalPrice);
            $status = $payAmount >= $totalPrice ? 'paid' : ($payAmount > 0 ? 'partial' : 'unpaid');
            $remainingBill = $status === 'paid' ? 0 : max(0, $totalPrice - $payAmount);

            // 3. Buat Invoice Number (Tetap Sama)
            $today = Carbon::now()->format('Ymd');
            $countToday = Transaction::whereDate('created_at', Carbon::today())->count();
            $nextNumber = str_pad($countToday + 1, 3, '0', STR_PAD_LEFT);
            $invoiceNumber = "TR-{$today}-{$nextNumber}";

            // 4. Simpan Header Transaksi
            $transaction = Transaction::create([
                'invoice_number' => $invoiceNumber,
                'customer_id' => $validated['customer_id'] ?? null,
                'user_id' => Auth::id(),
                'total_price' => $totalPrice,
                'pay_amount' => $payAmount,
                'change_amount' => $changeAmount,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'payment_method' => $validated['payment_method'],
                'status' => $status,
                'remaining_bill' => $remainingBill,
                'due_date' => $validated['due_date'] ?? null,
            ]);

            // 5. Simpan Pembayaran Pertama (Initial Payment Record)
            if ($payAmount > 0) {
                TransactionPayment::create([
                    'transaction_id' => $transaction->id,
                    'user_id' => Auth::id(),
                    'amount' => $payAmount,
                    'payment_method' => $validated['payment_method'],
                    'notes' => 'Pembayaran awal saat transaksi',
                ]);
            }

            // 6. Simpan Detail & Kurangi Stok Secara Aman
            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['id']);
                $lineSubtotal = $item['qty'] * $item['price'];

                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'quantity' => $item['qty'],
                    'price_at_sale' => $item['price'],
                    'subtotal' => $lineSubtotal,
                ]);

                $product->decrement('stock', $item['qty']);

                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => Auth::id(),
                    'type' => 'out',
                    'quantity' => $item['qty'],
                    'reference_type' => 'Transaction',
                    'reference_id' => $transaction->id,
                    'description' => "Penjualan nota {$invoiceNumber}",
                ]);
            }

            // 7. Audit Log (Audit Trail Keamanan)
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'TRANSAKSI_BARU',
                'description' => "Berhasil menyimpan transaksi {$invoiceNumber} senilai " . number_format($totalPrice),
                'ip_address' => request()->ip(),
            ]);

            return response()->json(['message' => 'Transaksi berhasil!', 'invoice' => $invoiceNumber]);
        });
    }
}
