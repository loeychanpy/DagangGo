<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Sumber kebenaran tunggal untuk stok.
 *
 * `stock_movements` adalah ledger (catatan kebenaran). `products.stock` hanyalah
 * saldo tercache yang SELALU dihitung ulang dari ledger setiap ada pergerakan.
 * Semua perubahan stok WAJIB lewat service ini supaya keduanya tidak pernah drift.
 */
class StockService
{
    /**
     * Catat satu pergerakan stok dan sinkronkan cache products.stock secara atomik.
     */
    public function record(
        Product $product,
        string $type,
        int $quantity,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $description = null,
        ?int $userId = null
    ): StockMovement {
        if (!in_array($type, ['in', 'out'], true)) {
            throw new \InvalidArgumentException("Tipe pergerakan stok tidak valid: {$type}");
        }
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Jumlah pergerakan stok harus lebih dari 0.');
        }

        return DB::transaction(function () use ($product, $type, $quantity, $referenceType, $referenceId, $description, $userId) {
            // Kunci baris produk agar saldo tidak balapan saat transaksi bersamaan.
            $locked = Product::lockForUpdate()->findOrFail($product->id);

            $movement = StockMovement::create([
                'product_id'     => $locked->id,
                'user_id'        => $userId ?? Auth::id(),
                'type'           => $type,
                'quantity'       => $quantity,
                'reference_type' => $referenceType,
                'reference_id'   => $referenceId,
                'description'    => $description,
            ]);

            // products.stock = saldo ledger (sumber kebenaran tunggal).
            $locked->stock = $this->ledgerBalance($locked->id);
            $locked->save();

            // Jaga instance pemanggil tetap sinkron.
            $product->stock = $locked->stock;

            return $movement;
        });
    }

    public function stockIn(Product $product, int $qty, ?string $refType = null, ?int $refId = null, ?string $desc = null, ?int $userId = null): StockMovement
    {
        return $this->record($product, 'in', $qty, $refType, $refId, $desc, $userId);
    }

    public function stockOut(Product $product, int $qty, ?string $refType = null, ?int $refId = null, ?string $desc = null, ?int $userId = null): StockMovement
    {
        return $this->record($product, 'out', $qty, $refType, $refId, $desc, $userId);
    }

    /**
     * Set stok ke nilai absolut dengan mencatat pergerakan selisihnya.
     * Mengembalikan movement, atau null bila tidak ada perubahan.
     */
    public function setStock(
        Product $product,
        int $target,
        ?string $refType = 'Penyesuaian',
        ?int $refId = null,
        ?string $desc = null,
        ?int $userId = null
    ): ?StockMovement {
        $diff = $target - (int) $product->stock;

        if ($diff === 0) {
            return null;
        }

        return $this->record(
            $product,
            $diff > 0 ? 'in' : 'out',
            abs($diff),
            $refType,
            $refId ?? $product->id,
            $desc,
            $userId
        );
    }

    /**
     * Saldo stok menurut ledger: SUM(in) - SUM(out).
     */
    public function ledgerBalance(int $productId): int
    {
        $in  = (int) StockMovement::where('product_id', $productId)->where('type', 'in')->sum('quantity');
        $out = (int) StockMovement::where('product_id', $productId)->where('type', 'out')->sum('quantity');

        return $in - $out;
    }

    /**
     * Hitung ulang cache products.stock dari ledger untuk satu produk (atau semua bila null).
     * Mengembalikan jumlah produk yang nilainya dikoreksi.
     */
    public function reconcile(?Product $only = null): int
    {
        $fixed = 0;

        $query = Product::query();
        if ($only) {
            $query->whereKey($only->id);
        }

        $query->chunkById(200, function ($products) use (&$fixed) {
            foreach ($products as $product) {
                $balance = $this->ledgerBalance($product->id);
                if ((int) $product->stock !== $balance) {
                    $product->stock = $balance;
                    $product->save();
                    $fixed++;
                }
            }
        });

        return $fixed;
    }
}
