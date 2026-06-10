<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Selaraskan semua SKU lama (PRD-001, SMN-001, PRD0039, dst) ke pola
        // generator: 'PRD' + id 4-digit. Lihat ProductController@store.
        DB::statement("UPDATE products SET sku = CONCAT('PRD', LPAD(id, 4, '0'))");
    }

    public function down(): void
    {
        // SKU asli tidak bisa dipulihkan.
    }
};
