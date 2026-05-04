<?php

/**
 * Migration: create_cash_ledger_table
 * DDL Source: FleetOpsDB.dbo.CashLedger
 * Execution Tier: 4 — FK → drivers (OrderID has no FK constraint in DDL)
 * @author Team Leader (Khalid)
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_ledger', function (Blueprint $table) {
            // DDL: TransactionID bigint IDENTITY(1,1) — PK
            $table->bigIncrements('transaction_id');

            // DDL: OrderID bigint NOT NULL — NOTE: No FK constraint defined in the source DDL.
            // TODO: Add FK to orders if referential integrity is required.
            $table->unsignedBigInteger('order_id');

            // DDL: DriverID bigint NOT NULL FK → drivers (FK_CL_Driver, no cascade)
            $table->unsignedBigInteger('driver_id');

            // DDL: AmountCollected decimal(14,4) DEFAULT 0 NOT NULL
            $table->decimal('amount_collected', 14, 4)->default(0);

            // DDL: PaymentMethod nvarchar(20) NOT NULL — CK_CL_PayMethod
            $table->enum('payment_method', ['cash', 'card', 'digital_wallet', 'credit']);

            // DDL: PaymentStatus nvarchar(20) DEFAULT 'pending' NOT NULL — CK_CL_PayStatus
            $table->enum('payment_status', ['pending', 'collected', 'failed', 'refunded'])
                  ->default('pending');

            // DDL: TransactionTs datetimeoffset(3) DEFAULT sysdatetimeoffset() NOT NULL
            $table->timestampTz('transaction_ts', 3)->useCurrent();

            // DDL: Handed_over_to_company bit NULL
            $table->boolean('handed_over_to_company')->nullable();

            // DDL: CreatedAt and UpdatedAt datetime2(7)
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            // FK_CL_Driver → drivers.driver_id (no cascade in DDL)
            $table->foreign('driver_id')->references('driver_id')->on('drivers');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_ledger');
    }
};
