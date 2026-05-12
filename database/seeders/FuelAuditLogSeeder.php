<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * FuelAuditLogSeeder — realistic fuel fill-up records for all vehicles
 * covering March and April 2026 so the Fuel Efficiency page has two
 * full periods to compare.
 *
 * total_cost is a stored/computed column — do NOT insert it.
 */
class FuelAuditLogSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('fuel_audit_logs')->truncate();

        $vehicles = DB::table('vehicles')->orderBy('vehicle_id')->get(['vehicle_id', 'VehicleType', 'Current_odometer'])->toArray();

        if (empty($vehicles)) {
            $this->command->warn('⚠️  FuelAuditLogSeeder: No vehicles found. Run VehicleSeeder first.');
            return;
        }

        // unit_price EGP per litre (April 2026 Egypt pump price ~14.50)
        $price = 14.50;

        // Per-vehicle fill-up schedule: [date, litres, odometer_reading]
        // Odometer readings are consistent with VehicleSeeder values.
        $schedule = [
            // Vehicle 0 — Toyota Hilux (light) — odometer ~45200
            0 => [
                ['2026-03-05', 42.0,  43800.00],
                ['2026-03-19', 40.5,  44300.00],
                ['2026-04-02', 41.0,  44700.00],
                ['2026-04-16', 38.5,  45000.00],
                ['2026-04-28', 43.0,  45200.00],
            ],
            // Vehicle 1 — Isuzu D-Max (heavy) — odometer ~88750
            1 => [
                ['2026-03-04', 65.0,  87200.00],
                ['2026-03-18', 62.0,  87800.00],
                ['2026-04-01', 68.0,  88200.00],
                ['2026-04-15', 60.0,  88500.00],
                ['2026-04-27', 55.0,  88750.00],
            ],
            // Vehicle 2 — Ford Transit (refrigerated) — odometer ~121300
            2 => [
                ['2026-03-06', 75.0,  119500.00],
                ['2026-03-20', 72.0,  120200.00],
                ['2026-04-03', 78.0,  120700.00],
                ['2026-04-17', 70.0,  121000.00],
                ['2026-04-29', 74.0,  121300.00],
            ],
            // Vehicle 3 — Mercedes Sprinter (heavy) — odometer ~203500
            3 => [
                ['2026-03-07', 90.0,  201000.00],
                ['2026-03-21', 88.0,  202000.00],
                ['2026-04-04', 92.0,  202800.00],
                ['2026-04-18', 85.0,  203200.00],
                ['2026-04-28', 80.0,  203500.00],
            ],
            // Vehicle 4 — Hyundai H350 (light) — odometer ~67400
            4 => [
                ['2026-03-08', 50.0,  65800.00],
                ['2026-03-22', 48.0,  66400.00],
                ['2026-04-05', 52.0,  66800.00],
                ['2026-04-19', 47.0,  67100.00],
                ['2026-04-28', 50.0,  67400.00],
            ],
            // Vehicle 5 — Mitsubishi Fuso (heavy) — odometer ~155000
            5 => [
                ['2026-03-03', 120.0, 152500.00],
                ['2026-03-17', 115.0, 153500.00],
                ['2026-04-01', 122.0, 154000.00],
                ['2026-04-15', 118.0, 154500.00],
                ['2026-04-27', 120.0, 155000.00],
            ],
            // Vehicle 6 — Nissan Urvan (light) — odometer ~34100
            6 => [
                ['2026-03-10', 35.0,  32800.00],
                ['2026-03-24', 33.0,  33300.00],
                ['2026-04-07', 36.0,  33600.00],
                ['2026-04-21', 34.0,  33900.00],
                ['2026-04-28', 35.0,  34100.00],
            ],
            // Vehicle 7 — MAN TGS (heavy) — odometer ~412000
            7 => [
                ['2026-03-02', 150.0, 409500.00],
                ['2026-03-16', 145.0, 410500.00],
                ['2026-04-01', 155.0, 411000.00],
                ['2026-04-15', 148.0, 411500.00],
                ['2026-04-27', 150.0, 412000.00],
            ],
        ];

        $logs = [];
        foreach ($schedule as $idx => $fills) {
            if (!isset($vehicles[$idx])) continue;
            $vehicleId = $vehicles[$idx]->vehicle_id;

            foreach ($fills as [$date, $litres, $odometer]) {
                $logs[] = [
                    'vehicle_id'       => $vehicleId,
                    'log_ts'           => $date . ' 07:30:00',
                    'fuel_quantity'    => $litres,
                    'unit_price'       => $price,
                    'odometer_reading' => $odometer,
                    'created_at'       => $date . ' 07:30:00',
                    'updated_at'       => $date . ' 07:30:00',
                ];
            }
        }

        DB::table('fuel_audit_logs')->insert($logs);

        $this->command->info('✅ FuelAuditLogSeeder: ' . count($logs) . ' fuel logs seeded across ' . count($schedule) . ' vehicles.');
    }
}
