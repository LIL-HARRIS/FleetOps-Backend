<?php

namespace App\Modules\Maintenance\Services;

use Illuminate\Support\Facades\DB;
use App\Modules\ReportingAnalytics\Models\IncidentReport;
use App\Modules\Maintenance\Models\MaintenanceAssignment;

class EmergencyDispatchService
{
    public function getActiveIncidents(): array
    {
        // Fetching incidents from the 'incident_reports' table.
        // You can add ->where('type', 'Breakdown') or similar if needed.
        return IncidentReport::with(['vehicle', 'driver'])
            ->orderBy('incident_ts', 'desc')
            ->get()
            ->toArray();
    }

    public function getIncidentDetails(int $id)
    {
        // Return detailed data for a specific incident.
        return IncidentReport::with(['vehicle', 'driver'])->find($id);
    }

    public function getNearbyMechanics(int $id): array
    {
        // Return list of mechanics. In a real scenario, this would use geospatial query.
        $mechanics = \App\Modules\AuthIdentity\Models\User::where('role', 'Mechanic')->get();
        
        $result = [];
        $distances = [2.4, 5.1, 8.7, 12.3]; // mock distances
        $etas = ['10 min', '15 min', '25 min', '40 min'];
        $index = 0;

        foreach ($mechanics as $mechanic) {
            $words = explode(' ', $mechanic->name);
            $initials = count($words) > 1 ? strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1)) : strtoupper(substr($mechanic->name, 0, 2));
            $result[] = [
                'id' => $mechanic->user_id,
                'name' => $mechanic->name,
                'phone' => $mechanic->phone_no ?? '+20 100 000 0000',
                'status' => 'Available', // Mock status
                'specialty' => 'General', // Mock specialty
                'distance' => $distances[$index % count($distances)] . ' km',
                'eta' => $etas[$index % count($etas)],
                'initials' => $initials,
                'avatarType' => 'avatar-' . (($index % 4) + 1), // Optional CSS class mapping
            ];
            $index++;
        }

        return $result;
    }

    public function dispatchMechanic(int $incidentId, array $data)
    {
        // Using a transaction ensures that creating the work order and updating the mechanic status 
        // either both succeed or both fail safely.
        return DB::transaction(function () use ($incidentId, $data) {
            if (!isset($data['mechanic_id'])) {
                throw new \InvalidArgumentException("mechanic_id is required.");
            }

            // 1. Fetch the incident to get the vehicle and details
            $incident = IncidentReport::findOrFail($incidentId);

            // 2. Change mechanic's status
            // Note: Currently, the `mechanics` table doesn't have a status field.
            // In a real scenario, we might update a 'status' column on User or Driver model,
            // or infer availability dynamically from active MaintenanceAssignments.

            // 3. Create an emergency WorkOrder / MaintenanceAssignment
            $assignment = MaintenanceAssignment::create([
                'vehicle_id'       => $incident->vehicle_id,
                'mechanic_id'      => $data['mechanic_id'],
                'fleet_manager_id' => auth()->id() ?? 1, // Fallback to 1 if auth is not available
                'service_type'     => 'emergency',
                'priority'         => 'critical',
                'status'           => 'assigned',
                'issue'            => "Emergency Dispatch for Incident #{$incidentId} - Type: {$incident->type}",
            ]);

            return $assignment;
        });
    }
}
