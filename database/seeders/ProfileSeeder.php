<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * ProfileSeeder — يملأ جداول customers / drivers / dispatchers / fleet_managers / mechanics
 * بيستخدم user_id الفعلي من قاعدة البيانات بعد ما UserSeeder خلق المستخدمين
 */
class ProfileSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // ─── Fleet Managers ───────────────────────────────────────────────────
        $fmEmails = ['khalid@fleetops.com', 'samira@fleetops.com'];
        foreach ($fmEmails as $email) {
            $user = DB::table('users')->where('email', $email)->first();
            if ($user) {
                DB::table('fleet_managers')->updateOrInsert(
                    ['fleet_manager_id' => $user->user_id],
                    ['fleet_manager_id' => $user->user_id, 'created_at' => $now]
                );
            }
        }

        // ─── Dispatchers ──────────────────────────────────────────────────────
        $dispEmails = ['youssef@fleetops.com', 'nada@fleetops.com'];
        foreach ($dispEmails as $email) {
            $user = DB::table('users')->where('email', $email)->first();
            if ($user) {
                DB::table('dispatchers')->updateOrInsert(
                    ['dispatcher_id' => $user->user_id],
                    ['dispatcher_id' => $user->user_id, 'created_at' => $now]
                );
            }
        }

        // ─── Mechanics ────────────────────────────────────────────────────────
        $mechEmails = ['mohamed.mech@fleetops.com', 'ibrahim.mech@fleetops.com'];
        foreach ($mechEmails as $email) {
            $user = DB::table('users')->where('email', $email)->first();
            if ($user) {
                DB::table('mechanics')->updateOrInsert(
                    ['mechanic_id' => $user->user_id],
                    ['mechanic_id' => $user->user_id, 'created_at' => $now]
                );
            }
        }

        // ─── Drivers ──────────────────────────────────────────────────────────
        $vehicles = DB::table('vehicles')->where('status', 'Active')->pluck('vehicle_id')->toArray();
        $licenseTypes = ['light', 'heavy', 'refrigerated'];

        $drivers = [
            ['email' => 'ahmed.driver@fleetops.com',   'license_no' => 'DRV-EG-10001', 'status' => 'Available'],
            ['email' => 'omar.driver@fleetops.com',    'license_no' => 'DRV-EG-10002', 'status' => 'OnShift'],
            ['email' => 'hassan.driver@fleetops.com',  'license_no' => 'DRV-EG-10003', 'status' => 'Available'],
            ['email' => 'karim.driver@fleetops.com',   'license_no' => 'DRV-EG-10004', 'status' => 'OffShift'],
            ['email' => 'mostafa.driver@fleetops.com', 'license_no' => 'DRV-EG-10005', 'status' => 'Available'],
        ];

        foreach ($drivers as $i => $d) {
            $user = DB::table('users')->where('email', $d['email'])->first();
            if ($user) {
                // Assign vehicle only if status is Active/OnShift and vehicles available
                $vehicleId = ($d['status'] !== 'OffShift' && isset($vehicles[$i]))
                    ? $vehicles[$i]
                    : null;

                DB::table('drivers')->updateOrInsert(
                    ['driver_id' => $user->user_id],
                    [
                        'driver_id'  => $user->user_id,
                        'license_no' => $d['license_no'],
                        'license_type' => $licenseTypes[$i % count($licenseTypes)],
                        'vehicle_id' => $vehicleId,
                        'status'     => $d['status'],
                        'score'      => rand(60, 95),  // Random score 60-95
                        'created_at' => $now,
                    ]
                );
            }
        }

        // ─── Customers ────────────────────────────────────────────────────────
        $customers = [
            ['email' => 'delta@client.com',        'address' => '15 Industry Zone, Cairo',         'delivery_preference' => 'Morning'],
            ['email' => 'cairo.retail@client.com', 'address' => '22 Nasr City, Cairo',             'delivery_preference' => 'Afternoon'],
            ['email' => 'nile.express@client.com', 'address' => '7 Corniche El Nile, Giza',        'delivery_preference' => 'Morning'],
            ['email' => 'alexport@client.com',     'address' => 'Alexandria Free Zone, Alex',      'delivery_preference' => 'Any'],
        ];

        foreach ($customers as $c) {
            $user = DB::table('users')->where('email', $c['email'])->first();
            if ($user) {
                DB::table('customers')->updateOrInsert(
                    ['customer_id' => $user->user_id],
                    [
                        'customer_id'         => $user->user_id,
                        'address'             => $c['address'],
                        'delivery_preference' => $c['delivery_preference'],
                        'created_at'          => $now,
                    ]
                );
            }
        }

        $this->command->info('✅ ProfileSeeder: all role profile tables populated.');
    }
}
