<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * FuelAuditLogSeeder — سجلات تعبئة وقود حقيقية لكل المركبات
 */
class FuelAuditLogSeeder extends Seeder
{
    public function run(): void
    {
        $vehicles = DB::table('vehicles')->pluck('vehicle_id')->toArray();

        if (empty($vehicles)) {
            $this->command->warn('⚠️  FuelAuditLogSeeder: No vehicles found.');
            return;
        }

        $logs = [
            ['vehicle_id' => $vehicles[0], 'log_ts' => '2026-04-25 08:15:00', 'fuel_quantity' => 45.00, 'unit_price' => 14.5000, 'odometer_reading' => 45050.00],
            ['vehicle_id' => $vehicles[0], 'log_ts' => '2026-04-27 07:30:00', 'fuel_quantity' => 38.50, 'unit_price' => 14.5000, 'odometer_reading' => 45200.00],
            ['vehicle_id' => $vehicles[1], 'log_ts' => '2026-04-24 09:00:00', 'fuel_quantity' => 60.00, 'unit_price' => 14.5000, 'odometer_reading' => 88600.00],
            ['vehicle_id' => $vehicles[1], 'log_ts' => '2026-04-27 06:00:00', 'fuel_quantity' => 55.00, 'unit_price' => 14.5000, 'odometer_reading' => 88750.00],
            ['vehicle_id' => $vehicles[2], 'log_ts' => '2026-04-26 11:00:00', 'fuel_quantity' => 70.00, 'unit_price' => 14.5000, 'odometer_reading' => 121200.00],
            ['vehicle_id' => $vehicles[4] ?? $vehicles[0], 'log_ts' => '2026-04-26 08:00:00', 'fuel_quantity' => 50.00, 'unit_price' => 14.5000, 'odometer_reading' => 67300.00],
            ['vehicle_id' => $vehicles[5] ?? $vehicles[0], 'log_ts' => '2026-04-23 07:00:00', 'fuel_quantity' => 120.00,'unit_price' => 14.5000, 'odometer_reading' => 154900.00],
            ['vehicle_id' => $vehicles[6] ?? $vehicles[0], 'log_ts' => '2026-04-25 15:30:00', 'fuel_quantity' => 35.00, 'unit_price' => 14.5000, 'odometer_reading' => 34050.00],
        ];

        // total_cost is a computed/stored column — do not insert it
        DB::table('fuel_audit_logs')->insert($logs);

        $this->command->info('✅ FuelAuditLogSeeder: ' . count($logs) . ' fuel logs ready.');
    }
}
