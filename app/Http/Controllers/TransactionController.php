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
use App\Models\Category;

class TransactionController extends Controller
{   
    #Fungsi untuk menampilkan halaman transaksi (POS)
    public function index(Request $request)
    {
        $query = Product::with([
            'category',
            'unit'
        ])
        ->where('stock','>',0);

        // Search produk
        if($request->filled('search'))
        {
            $query->where(
                'name',
                'like',
                '%'.$request->search.'%'
            );
        }

        // Filter kategori
        if($request->filled('category'))
        {
            $query->where(
                'category_id',
                $request->category
            );
        }

        $products = $query->get();
        $categories = Category::all();
        $cart=session()->get(
            'cart',
            []
        );
        
        // Jika request dari fetch()
        if($request->ajax())
        {
            return response()->json([
                'products'=>$products
            ]);
        }
        return view(
            'transaction.index',
            compact(
                'products',
                'categories',
                'cart'
            )
        );
    }

    #Fungsi untuk menambahkan produk ke keranjang (Session)
    public function addToCart(Request $request)
    {
        $product = Product::findOrFail(
            $request->product_id
        );

        $cart = session()->get(
            'cart',
            []
        );

        if(isset($cart[$product->id])){
            if($cart[$product->id]['qty'] >= $product->stock)
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Stok tidak mencukupi'
                ],400);
            }
            $cart[$product->id]['qty']++;

        }else{
            if($product->stock < 1)
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Stok habis'
                ],400);
            }
            $cart[$product->id]=[
                'id'=>$product->id,
                'name'=>$product->name,
                'price'=>$product->selling_price,
                'qty'=>1
            ];
        }

        session()->put(
            'cart',
            $cart
        );

        return response()->json([
            'success' => true,
            'cart' => session('cart')
        ]);
    }

    #FUngsi untuk mengurangi jumlah produk di keranjang atau menghapusnya jika qty <=0
    public function removeFromCart(Request $request)
    {
        $productId = $request->product_id;

        $cart = session()->get(
            'cart',
            []
        );

        // Pastikan item ada
        if(isset($cart[$productId]))
        {
            // Kurangi qty
            $cart[$productId]['qty']--;

            // Jika qty <=0 hapus item
            if(
                $cart[$productId]['qty']
                <=0
            )
            {
                unset(
                    $cart[$productId]
                );
            }

            // Simpan ulang session
            session()->put(
                'cart',
                $cart
            );
        }

        return response()->json([
            'success' => true,
            'cart' => session('cart')
        ]);
    }

    #Fungsi untuk menyimpan transaksi baru ke database
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

    public function checkout(Request $request)
    {
        $cart = session()->get('cart',[]);

        if(count($cart)===0)
        {
            return response()->json(['message'=>'Cart kosong'],400);
        }

        $subtotal = 0;
        foreach($cart as $item)
        {
            $subtotal += $item['price'] * $item['qty'];
        }
        $discount = $request->discount ?? 0;
        $finalTotal = $subtotal - $discount;
        $payAmount = $request->pay_amount ?? 0;

        $featureKasbon = config('features.kasbon');
        $allowedMethods = ['cash', 'transfer', 'qris'];
        if ($featureKasbon) {
            $allowedMethods[] = 'tempo';
        }

        if (!in_array($request->payment_method, $allowedMethods, true)) {
            return response()->json([
                'message' => 'Metode pembayaran tidak valid'
            ], 400);
        }

        if ($request->payment_method === 'tempo' && !$featureKasbon) {
            return response()->json([
                'message' => 'Metode Tempo tidak tersedia'
            ], 400);
        }

        $changeAmount = $request->payment_method === 'cash' ? max($payAmount - $finalTotal, 0) : 0;
        // Generate invoice
        $invoiceNumber = 'INV-' . time();
        // Status pembayaran
        $status = $request->payment_method === 'tempo' ? 'unpaid' : 'paid';
        // Remaining bill
        $remainingBill = $request->payment_method === 'tempo' ? $finalTotal : 0;

        if (
            $request->payment_method === 'cash'
            &&
            $payAmount < $finalTotal
        )
        {
            return response()->json([
                'message'=>'Uang pembayaran kurang'
            ],400);
        }
        
        DB::beginTransaction();

        try{
            // Save transaction
            $transaction = Transaction::create([
                'invoice_number'=>$invoiceNumber,
                'user_id'=>auth()->id(),
                'subtotal'=>$subtotal,
                'discount'=>$discount,
                'tax'=>0,
                'total_price'=>$finalTotal,
                'pay_amount'=>$payAmount,
                'change_amount'=>$changeAmount,
                'payment_method'=>$request->payment_method,
                'status'=>$status,
                'remaining_bill'=>$remainingBill,
                'due_date'=>$request->payment_method === 'tempo' ? $request->due_date : null
            ]);

            // Save details
            foreach($cart as $item)
            {
                TransactionDetail::create([
                    'transaction_id'=>$transaction->id,
                    'product_id'=>$item['id'],
                    'quantity'=>$item['qty'],
                    'price_at_sale'=>$item['price'],
                    'subtotal'=>$item['price'] * $item['qty']
                ]);
                // Reduce stock
                Product::find($item['id'])->decrement('stock', $item['qty']);
            }
            // Save payment
            if($request->payment_method !== 'tempo')
            {
                TransactionPayment::create([
                    'transaction_id'=>$transaction->id,
                    'user_id'=>auth()->id(),
                    'amount'=>$finalTotal,
                    'payment_method'=>$request->payment_method
                ]);
            }
            DB::commit();
            session()->forget('cart');
            return response()->json([
                'message'=>
                'Checkout berhasil'

            ]);

        }
        catch(\Exception $e){

            DB::rollBack();

            return response()->json([

                'message'=>$e->getMessage()

            ],500);

        }

    }
}
