<?php

namespace App\Console\Commands;

use App\Services\StockService;
use Illuminate\Console\Command;

class ReconcileStock extends Command
{
    protected $signature = 'stock:reconcile';

    protected $description = 'Hitung ulang cache products.stock dari ledger stock_movements (sumber kebenaran).';

    public function handle(StockService $stockService): int
    {
        $fixed = $stockService->reconcile();

        if ($fixed === 0) {
            $this->info('Stok sudah konsisten dengan ledger. Tidak ada yang dikoreksi.');
        } else {
            $this->warn("{$fixed} produk dikoreksi agar cocok dengan ledger.");
        }

        return self::SUCCESS;
    }
}
