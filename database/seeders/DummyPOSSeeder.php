<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\TransactionPayment;
use App\Models\Unit;
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
        $kg = Unit::firstOrCreate(['short_name' => 'kg'], ['name' => 'Kilogram']);
        $sak = Unit::firstOrCreate(['short_name' => 'sak'], ['name' => 'Sak']);
        $semen = Category::firstOrCreate(['name' => 'Semen']);
        $cat = Category::firstOrCreate(['name' => 'Cat']);

        // 2. BUAT PRODUK CONTOH
        $product1 = Product::firstOrCreate([
            'sku' => 'SMN-001',
        ], [
            'category_id' => $semen->id,
            'unit_id' => $sak->id,
            'name' => 'Semen Tiga Roda 40kg',
            'purchase_price' => 55000,
            'selling_price' => 62000,
            'stock' => 50,
            'min_stock' => 5,
        ]);

        $product2 = Product::firstOrCreate([
            'sku' => 'CAT-001',
        ], [
            'category_id' => $cat->id,
            'unit_id' => $kg->id,
            'name' => 'Cat Avian Putih 1kg',
            'purchase_price' => 40000,
            'selling_price' => 48000,
            'stock' => 20,
            'min_stock' => 5,
        ]);

        // 3. BUAT CUSTOMER CONTOH
        $customer = Customer::firstOrCreate([
            'name' => 'Toko Mandiri',
        ], [
            'phone' => '081234567890',
            'address' => 'Jl. Merdeka No. 12',
        ]);

        // 4. PASTIKAN USER OWNER TERSEDIA
        $owner = User::firstOrCreate([
            'email' => 'owner@MaterialPOS.com',
        ], [
            'name' => 'Owner',
            'password' => bcrypt('password'),
            'role' => 'owner',
        ]);

        $today = Carbon::today();

        // TRANSAKSI LUNAS DENGAN DELIVERY DAN PEMBAYARAN
        $trx1 = Transaction::firstOrCreate([
            'invoice_number' => 'TR-'.$today->format('Ymd').'-001',
        ], [
            'customer_id' => $customer->id,
            'user_id' => $owner->id,
            'total_price' => 124000,
            'pay_amount' => 150000,
            'change_amount' => 26000,
            'subtotal' => 124000,
            'discount' => 0,
            'tax' => 0,
            'payment_method' => 'cash',
            'status' => 'paid',
            'remaining_bill' => 0,
        ]);

        TransactionDetail::firstOrCreate([
            'transaction_id' => $trx1->id,
            'product_id' => $product1->id,
        ], [
            'quantity' => 2,
            'price_at_sale' => 62000,
            'subtotal' => 124000,
        ]);

        TransactionPayment::firstOrCreate([
            'transaction_id' => $trx1->id,
            'user_id' => $owner->id,
            'amount' => 150000,
        ], [
            'payment_method' => 'cash',
            'reference_number' => null,
            'notes' => 'Bayar tunai penuh',
        ]);

        Delivery::firstOrCreate([
            'transaction_id' => $trx1->id,
        ], [
            'driver_name' => 'Pak Budi',
            'license_plate' => 'B 1234 XY',
            'shipping_address' => 'Jl. Sudirman No. 45, Jakarta',
            'status' => 'delivered',
        ]);

        $product1->decrement('stock', 2);

        // TRANSAKSI TEMPO / PIUTANG DENGAN DELIVERY
        $trx2 = Transaction::firstOrCreate([
            'invoice_number' => 'TR-'.$today->format('Ymd').'-002',
        ], [
            'customer_id' => $customer->id,
            'user_id' => $owner->id,
            'total_price' => 816000,
            'pay_amount' => 0,
            'change_amount' => 0,
            'subtotal' => 816000,
            'discount' => 0,
            'tax' => 0,
            'payment_method' => 'tempo',
            'status' => 'unpaid',
            'remaining_bill' => 816000,
        ]);

        TransactionDetail::firstOrCreate([
            'transaction_id' => $trx2->id,
            'product_id' => $product2->id,
        ], [
            'quantity' => 17,
            'price_at_sale' => 48000,
            'subtotal' => 816000,
        ]);

        Delivery::firstOrCreate([
            'transaction_id' => $trx2->id,
        ], [
            'driver_name' => 'Pak Joko',
            'license_plate' => 'D 5678 ZQ',
            'shipping_address' => 'Jl. Pahlawan No. 8, Bandung',
            'status' => 'pending',
        ]);

        $product2->decrement('stock', 17);
    }
}
