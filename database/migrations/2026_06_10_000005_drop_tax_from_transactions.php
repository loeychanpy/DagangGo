<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kolom `tax` tidak pernah diisi (selalu 0) dan tidak ada fitur pajak.
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('tax');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('tax', 15, 2)->default(0)->after('discount');
        });
    }
};
