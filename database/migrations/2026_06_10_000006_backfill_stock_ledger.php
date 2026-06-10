<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Selaraskan ledger (stock_movements) dengan saldo `products.stock` saat ini.
     *
     * `products.stock` dianggap nilai tepercaya. Untuk tiap produk yang saldo
     * ledger-nya tidak sama dengan stok, dibuat satu pergerakan penyesuaian
     * ("Saldo Awal") sebesar selisihnya — sehingga SUM(in) - SUM(out) == stock
     * tanpa mengubah angka stok yang sudah ada.
     */
    public function up(): void
    {
        // user_id pada stock_movements wajib (non-nullable). Pakai user paling awal.
        $userId = DB::table('users')->min('id');
        if ($userId === null) {
            return; // tidak ada user → kemungkinan belum ada data sama sekali.
        }

        $now = now();

        // Termasuk produk yang soft-deleted agar ledger lengkap.
        foreach (DB::table('products')->get() as $product) {
            $in = (int) DB::table('stock_movements')
                ->where('product_id', $product->id)->where('type', 'in')->sum('quantity');
            $out = (int) DB::table('stock_movements')
                ->where('product_id', $product->id)->where('type', 'out')->sum('quantity');

            $ledger = $in - $out;
            $diff   = (int) $product->stock - $ledger;

            if ($diff === 0) {
                continue;
            }

            DB::table('stock_movements')->insert([
                'product_id'     => $product->id,
                'user_id'        => $userId,
                'type'           => $diff > 0 ? 'in' : 'out',
                'quantity'       => abs($diff),
                'reference_type' => 'Saldo Awal',
                'reference_id'   => $product->id,
                'description'    => 'Sinkronisasi saldo stok ke ledger',
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }
    }

    public function down(): void
    {
        // Hapus hanya pergerakan sinkronisasi yang dibuat migration ini.
        DB::table('stock_movements')->where('reference_type', 'Saldo Awal')->delete();
    }
};
