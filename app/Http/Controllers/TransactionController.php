<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\TransactionPayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Category;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'unit'])->where('stock', '>', 0);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $products   = $query->get();
        $categories = Category::all();
        $customers  = Customer::orderBy('name')->get();
        $cart       = session()->get('cart', []);

        if ($request->ajax()) {
            return response()->json(['products' => $products]);
        }

        return view('transaction.index', compact('products', 'categories', 'cart', 'customers'));
    }

    public function addToCart(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $cart    = session()->get('cart', []);

        if (isset($cart[$product->id])) {
            if ($cart[$product->id]['qty'] >= $product->stock) {
                return response()->json(['success' => false, 'message' => 'Stok tidak mencukupi'], 400);
            }
            $cart[$product->id]['qty']++;
        } else {
            if ($product->stock < 1) {
                return response()->json(['success' => false, 'message' => 'Stok habis'], 400);
            }
            $cart[$product->id] = [
                'id'    => $product->id,
                'name'  => $product->name,
                'price' => $product->selling_price,
                'qty'   => 1,
            ];
        }

        session()->put('cart', $cart);

        return response()->json(['success' => true, 'cart' => session('cart')]);
    }

    public function removeFromCart(Request $request)
    {
        $productId = $request->product_id;
        $cart      = session()->get('cart', []);

        if (isset($cart[$productId])) {
            $cart[$productId]['qty']--;
            if ($cart[$productId]['qty'] <= 0) {
                unset($cart[$productId]);
            }
            session()->put('cart', $cart);
        }

        return response()->json(['success' => true, 'cart' => session('cart')]);
    }

    public function checkout(Request $request)
    {
        $cart = session()->get('cart', []);

        if (count($cart) === 0) {
            return response()->json(['message' => 'Cart kosong'], 400);
        }

        $featureKasbon  = config('features.kasbon');
        $allowedMethods = ['cash', 'transfer', 'qris'];
        if ($featureKasbon) {
            $allowedMethods[] = 'tempo';
        }

        if (!in_array($request->payment_method, $allowedMethods, true)) {
            return response()->json(['message' => 'Metode pembayaran tidak valid'], 400);
        }

        // Hitung subtotal dari session cart
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['qty'];
        }

        $discount   = (float) ($request->discount ?? 0);
        $finalTotal = max($subtotal - $discount, 0);
        $payAmount  = (float) ($request->pay_amount ?? 0);

        if ($request->payment_method === 'cash' && $payAmount < $finalTotal) {
            return response()->json(['message' => 'Uang pembayaran kurang'], 400);
        }

        $changeAmount  = $request->payment_method === 'cash' ? max($payAmount - $finalTotal, 0) : 0;
        $status        = $request->payment_method === 'tempo' ? 'unpaid' : 'paid';
        $remainingBill = $request->payment_method === 'tempo' ? $finalTotal : 0;

        // Validasi credit limit untuk tempo
        if ($request->payment_method === 'tempo' && $request->filled('customer_id')) {
            $customer = Customer::find($request->customer_id);
            if ($customer && $customer->credit_limit > 0) {
                $totalDebt = $customer->transactions()
                    ->whereIn('status', ['unpaid', 'partial'])
                    ->sum('remaining_bill');
                if (($totalDebt + $finalTotal) > $customer->credit_limit) {
                    return response()->json([
                        'message' => "Limit kredit pelanggan {$customer->name} terlampaui. " .
                            "Sisa limit: Rp " . number_format($customer->credit_limit - $totalDebt, 0, ',', '.'),
                    ], 422);
                }
            }
        }

        DB::beginTransaction();

        try {
            // Validasi stok sebelum commit
            foreach ($cart as $item) {
                $product = Product::findOrFail($item['id']);
                if ($product->stock < $item['qty']) {
                    throw new \Exception("Stok tidak mencukupi untuk {$product->name}. Sisa: {$product->stock}");
                }
            }

            // Invoice number sequential per hari
            $today         = Carbon::now()->format('Ymd');
            $countToday    = Transaction::whereDate('created_at', Carbon::today())->count();
            $invoiceNumber = 'INV-' . $today . '-' . str_pad($countToday + 1, 3, '0', STR_PAD_LEFT);

            // Simpan header transaksi
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

            // Simpan detail, kurangi stok, catat StockMovement
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

            // Simpan record pembayaran awal (kecuali tempo)
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

            // Audit log
            AuditLog::create([
                'user_id'     => Auth::id(),
                'action'      => 'TRANSAKSI_BARU',
                'description' => "Berhasil menyimpan transaksi {$invoiceNumber} senilai Rp " .
                    number_format($finalTotal, 0, ',', '.'),
                'ip_address'  => $request->ip(),
            ]);

            DB::commit();
            session()->forget('cart');

            return response()->json([
                'message'        => 'Checkout berhasil',
                'transaction_id' => $transaction->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
