<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaction_payments', function (Blueprint $table) {
            $table->string('proof_photo')->nullable()->after('reference_number');
        });

        // Pindahkan path bukti yang sebelumnya ditumpuk di kolom `notes`
        // ke kolom `proof_photo`. `notes` dikembalikan untuk catatan teks.
        DB::statement("UPDATE transaction_payments SET proof_photo = notes WHERE notes LIKE 'payment-proofs/%'");
        DB::statement("UPDATE transaction_payments SET notes = NULL WHERE notes LIKE 'payment-proofs/%'");
    }

    public function down(): void
    {
        // Kembalikan path ke notes sebelum kolom dihapus.
        DB::statement("UPDATE transaction_payments SET notes = proof_photo WHERE proof_photo IS NOT NULL");

        Schema::table('transaction_payments', function (Blueprint $table) {
            $table->dropColumn('proof_photo');
        });
    }
};
