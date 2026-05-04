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
            $this->command->warn('OrderSeeder: No drivers or customers found.');
            return;
        }

        $statuses = ['Pending', 'Assigned', 'InTransit', 'Out for Delivery', 'Delivered', 'Failed', 'Returned'];
        $types = ['Normal', 'Express', 'Low'];
        $deliveryPreferences = ['Morning', 'Afternoon', 'Evening', 'Any'];
        $paymentMethods = ['Cash', 'Card', 'Wallet'];
        $areas = ['Downtown', 'Nasr City', 'Maadi', 'Zamalek', 'Heliopolis', 'Dokki', '6th of October'];

        $startOrderId = 1001;
        $ordersToGenerate = 100;
        $endOrderId = $startOrderId + $ordersToGenerate - 1;

        $existingOrderIds = DB::table('order')
            ->whereBetween('OrderID', [$startOrderId, $endOrderId])
            ->pluck('OrderID')
            ->toArray();

        $existingOrderLookup = array_flip($existingOrderIds);
        $orders = [];

        for ($index = 0; $index < $ordersToGenerate; $index++) {
            $orderId = $startOrderId + $index;

            if (isset($existingOrderLookup[$orderId])) {
                continue;
            }

            $status = $statuses[array_rand($statuses)];
            $type = $types[array_rand($types)];
            $deliveryPreference = $deliveryPreferences[array_rand($deliveryPreferences)];
            $paymentMethod = $paymentMethods[array_rand($paymentMethods)];
            $area = $areas[array_rand($areas)];

            $hour = mt_rand(8, 22);
            $minuteOptions = [0, 15, 30, 45];
            $minute = $minuteOptions[array_rand($minuteOptions)];
            $eta = sprintf('%02d:%02d', $hour, $minute);

            $promisedWindow = now()->copy()
                ->addDays(mt_rand(0, 14))
                ->setTime($hour, $minute, 0);

            $isFinished = in_array($status, ['Delivered', 'Failed', 'Returned'], true);
            $createdAt = now()->copy()->subDays(mt_rand(0, 20))->subMinutes(mt_rand(0, 1200));

            $orders[] = [
                'OrderID'             => $orderId,
                'DriverID(FK)'        => $drivers[$index % count($drivers)],
                'CustomerID(FK)'      => $customers[$index % count($customers)],
                'vehicle_id(FK)'      => empty($vehicles) ? null : $vehicles[$index % count($vehicles)],
                'TransactionID(FK)'   => null,
                'Status'              => $status,
                'ETA'                 => $eta,
                'PromisedWindow'      => $promisedWindow,
                'Priority'            => mt_rand(0, 100),
                'Type'                => $type,
                'Price'               => mt_rand(150, 6000),
                'digital_signature'   => $isFinished ? sprintf('SIG%06d', $orderId) : null,
                'Delivery_preference' => $deliveryPreference,
                'Payment_method'      => $paymentMethod,
                'Perishable'          => mt_rand(0, 1),
                'Weight'              => mt_rand(1, 50),
                'Volume'              => mt_rand(1, 5),
                'LiveTrackingLink'    => 'http://fleetops.com/track/' . $orderId,
                'DeliveryTimeWindow'  => mt_rand(1, 10),
                'Longitude'           => mt_rand(31150000, 31320000) / 1000000,
                'Latitude'            => mt_rand(29950000, 30200000) / 1000000,
                'Area'                => $area,
                'Created_at'          => $createdAt,
                'UpdatedAt'           => $createdAt->copy()->addMinutes(mt_rand(5, 720)),
                'DeliveredAt'         => $isFinished ? $promisedWindow->copy()->addHours(mt_rand(1, 6)) : null,
            ];
        }

        if (!empty($orders)) {
            foreach (array_chunk($orders, 50) as $chunk) {
                DB::table('order')->insert($chunk);
            }
        }

        $this->command->info('OrderSeeder: seeded ' . count($orders) . ' new orders (target: 100).');
    }
}
