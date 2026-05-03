<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('order', function (Blueprint $table) {
            $table->foreign('TransactionID(FK)', 'FK_Order_CashLedger')
                  ->references('transaction_id')->on('cash_ledger')->noActionOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order', function (Blueprint $table) {
            $table->dropForeign('FK_Order_CashLedger');
        });
    }
};
