<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * OrderSeeder — طلبات توصيل
 * OrderID ليس IDENTITY — لازم نحدده يدوياً
 */
class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $drivers   = DB::table('drivers')->pluck('driver_id')->toArray();
        $customers = DB::table('customers')->pluck('customer_id')->toArray();
        $vehicles  = DB::table('vehicles')->pluck('vehicle_id')->toArray();

        if (empty($drivers) || empty($customers)) {
            $this->command->warn('⚠️  OrderSeeder: No drivers or customers found.');
            return;
        }

        $orders = [
            [
                'OrderID'             => 1001,
                'DriverID(FK)'        => $drivers[0],
                'CustomerID(FK)'      => $customers[0],
                'vehicle_id(FK)'       => $vehicles[0] ?? null,
                'TransactionID(FK)'   => null,
                'Status'              => 'Assigned',
                'ETA'                 => '10:30',
                'PromisedWindow'      => '2026-04-28 10:30:00',
                'Priority'            => 85,
                'Type'                => 'Express',
                'Price'               => 850,
                'digital_signature'   => 'SIG-A001',
                'Delivery_preference' => 'Morning',
                'Payment_method'      => 'Cash',
                'Perishable'          => 0,
                'Weight'              => 6,
                'Volume'              => 10,
                'LiveTrackingLink'    => 'http://fleetops.com/track/1001',
                'DeliveryTimeWindow'  => 2.5,
                'Longitude'           => 31.235711,
                'Latitude'            => 30.044419,
                'Area'                => 'Downtown',
            ],
            [
                'OrderID'             => 1002,
                'DriverID(FK)'        => $drivers[1] ?? $drivers[0],
                'CustomerID(FK)'      => $customers[1] ?? $customers[0],
                'vehicle_id(FK)'       => $vehicles[1] ?? null,
                'TransactionID(FK)'   => null,
                'Status'              => 'Out for Delivery',
                'ETA'                 => '14:00',
                'PromisedWindow'      => '2026-04-28 14:00:00',
                'Priority'            => 50,
                'Type'                => 'Normal',
                'Price'               => 1200,
                'digital_signature'   => null,
                'Delivery_preference' => 'Afternoon',
                'Payment_method'      => 'Card',
                'Perishable'          => 1,
                'Weight'              => 18,
                'Volume'              => 20,
                'LiveTrackingLink'    => 'http://fleetops.com/track/1002',
                'DeliveryTimeWindow'  => 4.0,
                'Longitude'           => 31.258900,
                'Latitude'            => 30.062600,
                'Area'                => 'Nasr City',
            ],
            [
                'OrderID'             => 1003,
                'DriverID(FK)'        => $drivers[2] ?? $drivers[0],
                'CustomerID(FK)'      => $customers[2] ?? $customers[0],
                'vehicle_id(FK)'       => $vehicles[2] ?? null,
                'TransactionID(FK)'   => null,
                'Status'              => 'Pending',
                'ETA'                 => '09:00',
                'PromisedWindow'      => '2026-04-29 09:00:00',
                'Priority'            => 20,
                'Type'                => 'Low',
                'Price'               => 450,
                'digital_signature'   => null,
                'Delivery_preference' => 'Morning',
                'Payment_method'      => 'Cash',
                'Perishable'          => 0,
                'Weight'              => 2,
                'Volume'              => 5,
                'LiveTrackingLink'    => null,
                'DeliveryTimeWindow'  => 8.0,
                'Longitude'           => 31.200100,
                'Latitude'            => 30.013100,
                'Area'                => 'Maadi',
            ],
            [
                'OrderID'             => 1004,
                'DriverID(FK)'        => $drivers[0],
                'CustomerID(FK)'      => $customers[3] ?? $customers[0],
                'vehicle_id(FK)'       => $vehicles[0] ?? null,
                'TransactionID(FK)'   => null,
                'Status'              => 'Assigned',
                'ETA'                 => '16:00',
                'PromisedWindow'      => '2026-04-29 16:00:00',
                'Priority'            => 95,
                'Type'                => 'Express',
                'Price'               => 3200,
                'digital_signature'   => 'SIG-A004',
                'Delivery_preference' => 'Any',
                'Payment_method'      => 'Card',
                'Perishable'          => 0,
                'Weight'              => 95,
                'Volume'              => 150,
                'LiveTrackingLink'    => 'http://fleetops.com/track/1004',
                'DeliveryTimeWindow'  => 2.0,
                'Longitude'           => 31.222200,
                'Latitude'            => 30.033300,
                'Area'                => 'Zamalek',
            ],
            [
                'OrderID'             => 1005,
                'DriverID(FK)'        => $drivers[1] ?? $drivers[0],
                'CustomerID(FK)'      => $customers[0],
                'vehicle_id(FK)'       => $vehicles[1] ?? null,
                'TransactionID(FK)'   => null,
                'Status'              => 'Cancelled',
                'ETA'                 => null,
                'PromisedWindow'      => null,
                'Priority'            => 10,
                'Type'                => 'Low',
                'Price'               => 300,
                'digital_signature'   => null,
                'Delivery_preference' => 'Morning',
                'Payment_method'      => 'Cash',
                'Perishable'          => 0,
                'Weight'              => 1,
                'Volume'              => 1,
                'LiveTrackingLink'    => null,
                'DeliveryTimeWindow'  => null,
                'Longitude'           => null,
                'Latitude'            => null,
                'Area'                => null,
            ],
        ];

        foreach ($orders as $o) {
            $exists = DB::table('order')->where('OrderID', $o['OrderID'])->exists();
            if (!$exists) {
                // Adjust status if not in allowed CK_Orders_Status list (Cancelled is not in the new schema)
                if ($o['Status'] === 'Cancelled') {
                    $o['Status'] = 'Returned'; // Mapping old Cancelled to new Returned or Failed
                }
                
                DB::table('order')->insert(array_merge($o, [
                    'Created_at' => now(),
                    'UpdatedAt' => now()
                ]));
            }
        }

        $this->command->info('✅ OrderSeeder: ' . count($orders) . ' orders ready (parcels removed).');
    }
}
