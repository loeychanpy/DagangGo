<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DummyPOSSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       // 1. BUAT SATUAN & KATEGORI
        $kg = Unit::create(['name' => 'Kilogram', 'short_name' => 'kg']);
        $sak = Unit::create(['name' => 'Sak', 'short_name' => 'sak']);
        $semen = Category::create(['name' => 'Semen']);
        $cat = Category::create(['name' => 'Cat']);

        // 2. BUAT PRODUK CONTOH
        $product1 = Product::create([
            'category_id' => $semen->id,
            'unit_id' => $sak->id,
            'sku' => 'SMN-001',
            'name' => 'Semen Tiga Roda 40kg',
            'purchase_price' => 55000,
            'selling_price' => 62000,
            'stock' => 50,
            'min_stock' => 5
        ]);

        $product2 = Product::create([
            'category_id' => $cat->id,
            'unit_id' => $kg->id,
            'sku' => 'CAT-001',
            'name' => 'Cat Avian Putih 1kg',
            'purchase_price' => 40000,
            'selling_price' => 48000,
            'stock' => 20,
            'min_stock' => 5
        ]);

        // ==========================================
        // 3. TESTING LOGIKA TRANSAKSI & DASHBOARD
        // ==========================================
        $owner = User::where('role', 'owner')->first();
        $today = Carbon::today();

        if ($owner) {
            // TRANSAKSI 1: Lunas (Beli 2 Semen Cash)
            // Harga = 2 x 62.000 = 124.000
            $trx1 = Transaction::create([
                'invoice_number' => 'TR-'.$today->format('Ymd').'-001',
                'user_id' => $owner->id,
                'total_price' => 124000,
                'pay_amount' => 150000, // Bayar pakai uang 150rb
                'change_amount' => 26000, // Kembalian 26rb
                'subtotal' => 124000,
                'payment_method' => 'cash',
                'status' => 'paid',
                'remaining_bill' => 0,
            ]);

            TransactionDetail::create([
                'transaction_id' => $trx1->id,
                'product_id' => $product1->id,
                'quantity' => 2,
                'price_at_sale' => 62000,
                'subtotal' => 124000,
            ]);
            $product1->decrement('stock', 2); // Sisa stok: 48


            // TRANSAKSI 2: Tempo / Ngutang (Beli 17 Cat)
            // Harga = 17 x 48.000 = 816.000 (Stok akan sisa 3, memicu peringatan merah!)
            $trx2 = Transaction::create([
                'invoice_number' => 'TR-'.$today->format('Ymd').'-002',
                'user_id' => $owner->id,
                'total_price' => 816000,
                'pay_amount' => 0, // Belum bayar sama sekali
                'change_amount' => 0,
                'subtotal' => 816000,
                'payment_method' => 'tempo',
                'status' => 'unpaid',
                'remaining_bill' => 816000, // Piutang tercatat
            ]);

            TransactionDetail::create([
                'transaction_id' => $trx2->id,
                'product_id' => $product2->id,
                'quantity' => 17,
                'price_at_sale' => 48000,
                'subtotal' => 816000,
            ]);
            $product2->decrement('stock', 17); // Sisa stok: 3 (Di bawah min_stock 5)
        }
    }
}
