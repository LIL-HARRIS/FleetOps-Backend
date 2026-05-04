<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CashLedgerSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // Get all drivers and orders
        $drivers = DB::table('drivers')->pluck('driver_id')->toArray();
        $orders = DB::table('order')->pluck('OrderID')->toArray();

        if (empty($drivers) || empty($orders)) {
            $this->command->warn('⚠️ No drivers or orders found. Skipping CashLedgerSeeder.');
            return;
        }

        $paymentMethods = ['cash', 'card', 'digital_wallet', 'credit'];
        $statuses = ['pending', 'collected', 'failed', 'refunded'];

        $count = 0;
        foreach (array_slice($orders, 0, 15) as $orderId) {
            $driverId = $drivers[array_rand($drivers)];
            $paymentMethod = $paymentMethods[array_rand($paymentMethods)];
            $paymentStatus = $statuses[array_rand($statuses)];
            $amountCollected = rand(50, 500) + rand(0, 99) / 100;

            DB::table('cash_ledger')->updateOrInsert(
                [
                    'order_id' => $orderId,
                ],
                [
                    'driver_id'              => $driverId,
                    'amount_collected'       => $amountCollected,
                    'payment_method'         => $paymentMethod,
                    'payment_status'         => $paymentStatus,
                    'transaction_ts'         => now()->toDateTimeString(),
                    'handed_over_to_company' => $paymentStatus === 'collected' ? rand(0, 1) : null,
                    'created_at'             => $now,
                    'updated_at'             => $now,
                ]
            );

            $count++;
        }

        $this->command->info("✅ CashLedgerSeeder: $count cash ledger entries created.");
    }
}
