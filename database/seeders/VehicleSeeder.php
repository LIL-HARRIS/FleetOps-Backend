<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * VehicleSeeder — أسطول من 8 مركبات حقيقية
 */
class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $vehicles = [
            ['VehicleModel' => 'Toyota Hilux',      'VehicleType' => 'light',        'VehicleLicense' => 'أ ب ج 1001', 'MaxWeightCapacity' => 1500.00, 'Status' => 'Active',      'Current_odometer' => 45200.00,  'MaxVolume' => 8.50,  'MarketValue' => 950000],
            ['VehicleModel' => 'Isuzu D-Max',       'VehicleType' => 'heavy',        'VehicleLicense' => 'أ ب ج 1002', 'MaxWeightCapacity' => 2000.00, 'Status' => 'Active',      'Current_odometer' => 88750.00,  'MaxVolume' => 9.20,  'MarketValue' => 1100000],
            ['VehicleModel' => 'Ford Transit',      'VehicleType' => 'refrigerated', 'VehicleLicense' => 'أ ب ج 1003', 'MaxWeightCapacity' => 3500.00, 'Status' => 'Active',      'Current_odometer' => 121300.00, 'MaxVolume' => 12.00, 'MarketValue' => 1350000],
            ['VehicleModel' => 'Mercedes Sprinter', 'VehicleType' => 'heavy',        'VehicleLicense' => 'أ ب ج 1004', 'MaxWeightCapacity' => 3500.00, 'Status' => 'Maintenance', 'Current_odometer' => 203500.00, 'MaxVolume' => 13.50, 'MarketValue' => 1750000],
            ['VehicleModel' => 'Hyundai H350',      'VehicleType' => 'light',        'VehicleLicense' => 'أ ب ج 1005', 'MaxWeightCapacity' => 2500.00, 'Status' => 'Active',      'Current_odometer' => 67400.00,  'MaxVolume' => 10.00, 'MarketValue' => 1200000],
            ['VehicleModel' => 'Mitsubishi Fuso',   'VehicleType' => 'heavy',        'VehicleLicense' => 'أ ب ج 1006', 'MaxWeightCapacity' => 7000.00, 'Status' => 'Active',      'Current_odometer' => 155000.00, 'MaxVolume' => 18.00, 'MarketValue' => 2100000],
            ['VehicleModel' => 'Nissan Urvan',      'VehicleType' => 'light',        'VehicleLicense' => 'أ ب ج 1007', 'MaxWeightCapacity' => 1200.00, 'Status' => 'Active',      'Current_odometer' => 34100.00,  'MaxVolume' => 7.80,  'MarketValue' => 880000],
            ['VehicleModel' => 'MAN TGS',           'VehicleType' => 'heavy',        'VehicleLicense' => 'أ ب ج 1008', 'MaxWeightCapacity' => 18000.00,'Status' => 'OutOfService','Current_odometer' => 412000.00, 'MaxVolume' => 24.00, 'MarketValue' => 3200000],
        ];

        foreach ($vehicles as $v) {
            DB::table('vehicles')->updateOrInsert(
                ['VehicleLicense' => $v['VehicleLicense']],
                array_merge($v, ['CreatedAt' => now()])
            );
        }

        $this->command->info('✅ VehicleSeeder: ' . count($vehicles) . ' vehicles ready.');
    }
}
