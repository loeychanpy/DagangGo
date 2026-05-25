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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('unit_id')->constrained()->onDelete('restrict');
            $table->string('sku')->unique(); // Kode barang unik
            $table->string('name')->index(); // Nama barang, diindeks untuk pencarian cepat
            $table->decimal('purchase_price', 15, 2); // Harga (Akurasi Data: gunakan decimal, bukan integer)
            $table->decimal('selling_price', 15, 2); // Harga jual
            $table->integer('stock')->default(0); // Stok barang
            $table->integer('min_stock')->default(5); // Alert jika stok mau habis
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
