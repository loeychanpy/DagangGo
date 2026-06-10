<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Services\TransactionService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(private TransactionService $transactionService) {}

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

        $allowedMethods = ['cash', 'transfer', 'qris'];
        if (config('features.kasbon')) {
            $allowedMethods[] = 'tempo';
        }

        if (!in_array($request->payment_method, $allowedMethods, true)) {
            return response()->json(['message' => 'Metode pembayaran tidak valid'], 400);
        }

        $subtotal   = array_sum(array_map(fn($item) => $item['price'] * $item['qty'], $cart));
        $discount   = (float) ($request->discount ?? 0);
        $finalTotal = max($subtotal - $discount, 0);
        $payAmount  = (float) ($request->pay_amount ?? 0);

        if ($request->payment_method === 'cash' && $payAmount < $finalTotal) {
            return response()->json(['message' => 'Uang pembayaran kurang'], 400);
        }

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

        try {
            $transaction = $this->transactionService->checkout($request, $cart);
            session()->forget('cart');

            return response()->json([
                'message'        => 'Checkout berhasil',
                'transaction_id' => $transaction->id,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
