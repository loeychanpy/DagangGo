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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->timestamps(); 
            $table->index('created_at'); // Tambahkan index secara eksplisit
            $table-> foreignId('customer_id')->nullable()->constrained()->nullOnDelete(); // Pelanggan (opsional)
            $table->string('invoice_number')->unique();
            $table->foreignId('user_id')->constrained(); // Kasir yang melayani
            $table->decimal('total_price', 15, 2);
            $table->decimal('pay_amount', 15, 2);
            $table->decimal('change_amount', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->enum('payment_method', [
                'cash',
                'transfer',
                'qris',
                'tempo'
            ])->default('cash');

            $table->enum('status', [
                'paid',
                'partial',
                'unpaid'
            ])->default('paid');

            $table->decimal('remaining_bill', 15, 2)->default(0);

            $table->date('due_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
