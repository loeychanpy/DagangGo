<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained(); // Siapa yang mencatat pergerakan ini (Staf/Owner)
            
            // Jenis pergerakan: 'in' (masuk) atau 'out' (keluar)
            $table->enum('type', ['in', 'out'])->index();
            
            // Jumlah barang yang bergerak
            $table->integer('quantity');
            
            // Referensi (Opsional: ID Transaksi atau ID Pembelian)
            $table->string('reference_type')->nullable(); // Contoh: 'Penjualan', 'Restok', 'Penyesuaian'
            $table->unsignedBigInteger('reference_id')->nullable()->index();
            
            // Catatan tambahan
            $table->string('description')->nullable(); // Contoh: "Terjual ke Bpk. Budi", "Barang rusak kena air"
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
