<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DriverPerformanceSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // Get all drivers
        $drivers = DB::table('drivers')->pluck('driver_id')->toArray();

        if (empty($drivers)) {
            $this->command->warn('⚠️ No drivers found. Skipping DriverPerformanceSeeder.');
            return;
        }

        // Create performance records for last 12 periods (months)
        $periods = [
            ['start' => '2026-01-01', 'end' => '2026-01-31'],
            ['start' => '2026-02-01', 'end' => '2026-02-28'],
            ['start' => '2026-03-01', 'end' => '2026-03-31'],
            ['start' => '2026-04-01', 'end' => '2026-04-30'],
            ['start' => '2026-05-01', 'end' => '2026-05-03'],
        ];

        foreach ($drivers as $driverId) {
            foreach ($periods as $period) {
                $totalTrips = rand(8, 25);
                $completedTrips = rand((int)($totalTrips * 0.7), $totalTrips);
                $failedTrips = rand(0, (int)($totalTrips * 0.1));
                $cancelledTrips = $totalTrips - $completedTrips - $failedTrips;
                $onTimeDeliveries = rand((int)($completedTrips * 0.75), $completedTrips);
                $lateDeliveries = $completedTrips - $onTimeDeliveries;

                $onTimeDeliveryPct = $completedTrips > 0
                    ? round(($onTimeDeliveries / $completedTrips) * 100, 2)
                    : 0;

                $totalDistanceKm = rand(150, 450);
                $totalFuelLitres = rand(20, 60);
                $fuelPer100km = $totalDistanceKm > 0
                    ? round(($totalFuelLitres / $totalDistanceKm) * 100, 2)
                    : 0;

                $avgSpeedKmh = rand(40, 80);
                $incidentCount = rand(0, 2);
                $speedingEvents = rand(0, 5);
                $customerComplaints = rand(0, 3);
                $customerCompliments = rand(2, 8);
                $avgCustomerRating = round(rand(35, 50) / 10, 2);
                $totalActiveHours = round($totalDistanceKm / $avgSpeedKmh, 2);
                $idleHours = round($totalActiveHours * 0.15, 2);
                $overtimeHours = round($totalActiveHours * 0.05, 2);

                DB::table('driver_performance')->updateOrInsert(
                    [
                        'driver_id'    => $driverId,
                        'period_start' => $period['start'],
                    ],
                    [
                        'period_end'             => $period['end'],
                        'total_trips_assigned'   => $totalTrips,
                        'completed_trips'        => $completedTrips,
                        'failed_trips'           => $failedTrips,
                        'cancelled_trips'        => $cancelledTrips,
                        'on_time_deliveries'     => $onTimeDeliveries,
                        'late_deliveries'        => $lateDeliveries,
                        'on_time_delivery_pct'   => $onTimeDeliveryPct,
                        'total_distance_km'      => $totalDistanceKm,
                        'avg_speed_kmh'          => $avgSpeedKmh,
                        'total_fuel_litres'      => $totalFuelLitres,
                        'fuel_per_100km'         => $fuelPer100km,
                        'incident_count'         => $incidentCount,
                        'speeding_events'        => $speedingEvents,
                        'customer_complaints'    => $customerComplaints,
                        'customer_compliments'   => $customerCompliments,
                        'avg_customer_rating'    => $avgCustomerRating,
                        'total_active_hours'     => $totalActiveHours,
                        'idle_hours'             => $idleHours,
                        'overtime_hours'         => $overtimeHours,
                        'created_at'             => $now,
                        'updated_at'             => $now,
                    ]
                );
            }
        }

        $this->command->info('✅ DriverPerformanceSeeder: ' . (count($drivers) * count($periods)) . ' performance records created.');
    }
}
