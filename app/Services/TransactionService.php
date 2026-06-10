<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\TransactionPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function checkout(Request $request, array $cart): Transaction
    {
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['qty'];
        }

        $discount      = (float) ($request->discount ?? 0);
        $finalTotal    = max($subtotal - $discount, 0);
        $payAmount     = (float) ($request->pay_amount ?? 0);
        $changeAmount  = $request->payment_method === 'cash' ? max($payAmount - $finalTotal, 0) : 0;
        $status        = $request->payment_method === 'tempo' ? 'unpaid' : 'paid';
        $remainingBill = $request->payment_method === 'tempo' ? $finalTotal : 0;

        DB::beginTransaction();

        try {
            foreach ($cart as $item) {
                $product = Product::findOrFail($item['id']);
                if ($product->stock < $item['qty']) {
                    throw new \Exception("Stok tidak mencukupi untuk {$product->name}. Sisa: {$product->stock}");
                }
            }

            $today         = Carbon::now()->format('Ymd');
            $countToday    = Transaction::whereDate('created_at', Carbon::today())->count();
            $invoiceNumber = 'INV-' . $today . '-' . str_pad($countToday + 1, 3, '0', STR_PAD_LEFT);

            $transaction = Transaction::create([
                'invoice_number' => $invoiceNumber,
                'customer_id'    => $request->filled('customer_id') ? $request->customer_id : null,
                'user_id'        => Auth::id(),
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'tax'            => 0,
                'total_price'    => $finalTotal,
                'pay_amount'     => $payAmount,
                'change_amount'  => $changeAmount,
                'payment_method' => $request->payment_method,
                'status'         => $status,
                'remaining_bill' => $remainingBill,
                'due_date'       => $request->payment_method === 'tempo' ? $request->due_date : null,
            ]);

            foreach ($cart as $item) {
                $product      = Product::lockForUpdate()->findOrFail($item['id']);
                $lineSubtotal = $item['price'] * $item['qty'];

                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id'     => $product->id,
                    'quantity'       => $item['qty'],
                    'price_at_sale'  => $item['price'],
                    'subtotal'       => $lineSubtotal,
                ]);

                $product->decrement('stock', $item['qty']);

                StockMovement::create([
                    'product_id'     => $product->id,
                    'user_id'        => Auth::id(),
                    'type'           => 'out',
                    'quantity'       => $item['qty'],
                    'reference_type' => 'Transaction',
                    'reference_id'   => $transaction->id,
                    'description'    => "Penjualan nota {$invoiceNumber}",
                ]);
            }

            if ($request->payment_method !== 'tempo') {
                $proofPhotoPath = null;
                if ($request->hasFile('proof_photo')) {
                    $proofPhotoPath = $request->file('proof_photo')->store('payment-proofs', 'public');
                }

                TransactionPayment::create([
                    'transaction_id'   => $transaction->id,
                    'user_id'          => Auth::id(),
                    'amount'           => $finalTotal,
                    'payment_method'   => $request->payment_method,
                    'reference_number' => $request->input('reference_number'),
                    'notes'            => $proofPhotoPath,
                ]);
            }

            AuditLog::create([
                'user_id'     => Auth::id(),
                'action'      => 'TRANSAKSI_BARU',
                'description' => "Berhasil menyimpan transaksi {$invoiceNumber} senilai Rp " .
                    number_format($finalTotal, 0, ',', '.'),
                'ip_address'  => $request->ip(),
            ]);

            DB::commit();

            return $transaction;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
