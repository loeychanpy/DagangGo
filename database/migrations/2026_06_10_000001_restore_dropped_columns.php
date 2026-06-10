<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Restore columns dropped by the deleted migrations
        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('subtotal', 15, 2)->after('total_price')->default(0);
            $table->decimal('pay_amount', 15, 2)->after('subtotal')->default(0);
            $table->decimal('change_amount', 15, 2)->after('pay_amount')->default(0);
            $table->decimal('remaining_bill', 15, 2)->after('change_amount')->default(0);
        });

        Schema::table('transaction_details', function (Blueprint $table) {
            $table->decimal('subtotal', 15, 2)->after('price_at_sale')->default(0);
        });

        // Backfill transaction_details.subtotal from quantity * price_at_sale
        DB::statement('UPDATE transaction_details SET subtotal = quantity * price_at_sale');

        // Backfill transactions.subtotal = total_price + discount
        DB::statement('UPDATE transactions SET subtotal = total_price + discount');

        // Backfill pay_amount = total paid via transaction_payments
        DB::statement('
            UPDATE transactions t
            SET pay_amount = COALESCE(
                (SELECT SUM(amount) FROM transaction_payments WHERE transaction_id = t.id),
                0
            )
        ');

        // Backfill remaining_bill = total_price - pay_amount for unpaid/partial, 0 for paid
        DB::statement("
            UPDATE transactions
            SET remaining_bill = CASE
                WHEN status = 'paid' THEN 0
                ELSE GREATEST(total_price - pay_amount, 0)
            END
        ");

        // change_amount cannot be recovered — leave as 0
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'pay_amount', 'change_amount', 'remaining_bill']);
        });

        Schema::table('transaction_details', function (Blueprint $table) {
            $table->dropColumn('subtotal');
        });
    }
};
