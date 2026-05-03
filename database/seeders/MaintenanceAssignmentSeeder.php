<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * MaintenanceAssignmentSeeder — أوامر صيانة حقيقية مع parts usage
 */
class MaintenanceAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $vehicles     = DB::table('vehicles')->pluck('vehicle_id', 'VehicleModel')->toArray();
        $fleetMgrs    = DB::table('fleet_managers')->pluck('fleet_manager_id')->toArray();
        $mechanics    = DB::table('mechanics')->pluck('mechanic_id')->toArray();
        $parts        = DB::table('inventory')->pluck('part_id', 'part_name')->toArray();

        if (empty($fleetMgrs) || empty($mechanics)) {
            $this->command->warn('⚠️  MaintenanceAssignmentSeeder: No fleet managers or mechanics found.');
            return;
        }

        $assignments = [
            [
                'vehicle_id'      => $vehicles['Mercedes Sprinter'] ?? array_values($vehicles)[0],
                'fleet_manager_id'=> $fleetMgrs[0],
                'mechanic_id'     => $mechanics[0],
                'service_type'    => 'engine_repair',
                'priority'        => 'high',
                'status'          => 'in_progress',
                'issue'           => 'Engine overheating at high RPM. Coolant leak detected near the radiator hose.',
            ],
            [
                'vehicle_id'      => $vehicles['MAN TGS'] ?? array_values($vehicles)[1] ?? array_values($vehicles)[0],
                'fleet_manager_id'=> $fleetMgrs[0],
                'mechanic_id'     => $mechanics[1] ?? $mechanics[0],
                'service_type'    => 'brake_service',
                'priority'        => 'critical',
                'status'          => 'open',
                'issue'           => 'Rear brake pads worn below minimum thickness. Brake disc scoring observed.',
            ],
            [
                'vehicle_id'      => $vehicles['Toyota Hilux'] ?? array_values($vehicles)[0],
                'fleet_manager_id'=> $fleetMgrs[1] ?? $fleetMgrs[0],
                'mechanic_id'     => $mechanics[0],
                'service_type'    => 'oil_change',
                'priority'        => 'medium',
                'status'          => 'completed',
                'issue'           => 'Routine 10,000 km oil & filter change.',
            ],
            [
                'vehicle_id'      => $vehicles['Ford Transit'] ?? array_values($vehicles)[0],
                'fleet_manager_id'=> $fleetMgrs[1] ?? $fleetMgrs[0],
                'mechanic_id'     => null,
                'service_type'    => 'electrical',
                'priority'        => 'low',
                'status'          => 'open',
                'issue'           => 'Intermittent dashboard warning light. Needs diagnostic scan.',
            ],
        ];

        $assignmentIds = [];
        foreach ($assignments as $a) {
            $exists = DB::table('maintenance_assignments')
                ->where('vehicle_id', $a['vehicle_id'])
                ->where('service_type', $a['service_type'])
                ->where('status', $a['status'])
                ->exists();

            if (!$exists) {
                $id = DB::table('maintenance_assignments')->insertGetId(
                    array_merge($a, ['created_at' => now(), 'updated_at' => now()])
                );
                $assignmentIds[] = $id;
            }
        }

        // ─── Maintenance Parts Used ────────────────────────────────────────────
        if (!empty($assignmentIds) && !empty($parts)) {
            $oilFilterId   = $parts['Engine Oil Filter']  ?? null;
            $brakeDiscId   = $parts['Brake Discs (Rear)'] ?? null;
            $brakePadId    = $parts['Brake Pads (Front)'] ?? null;
            $coolantId     = $parts['Radiator Coolant 5L']?? null;

            $usages = [];

            if (isset($assignmentIds[0]) && $coolantId) {
                $usages[] = ['log_id' => $assignmentIds[0], 'part_id' => $coolantId, 'quantity_used' => 5, 'unit_cost' => 55.00];
            }
            if (isset($assignmentIds[1]) && $brakeDiscId) {
                $usages[] = ['log_id' => $assignmentIds[1], 'part_id' => $brakeDiscId, 'quantity_used' => 2, 'unit_cost' => 620.00];
            }
            if (isset($assignmentIds[1]) && $brakePadId) {
                $usages[] = ['log_id' => $assignmentIds[1], 'part_id' => $brakePadId, 'quantity_used' => 1, 'unit_cost' => 350.00];
            }
            if (isset($assignmentIds[2]) && $oilFilterId) {
                $usages[] = ['log_id' => $assignmentIds[2], 'part_id' => $oilFilterId, 'quantity_used' => 1, 'unit_cost' => 45.00];
            }

            if (!empty($usages)) {
                DB::table('maintenance_parts_used')->insert($usages);
            }
        }

        $this->command->info('✅ MaintenanceAssignmentSeeder: ' . count($assignments) . ' assignments + parts usage ready.');
    }
}
