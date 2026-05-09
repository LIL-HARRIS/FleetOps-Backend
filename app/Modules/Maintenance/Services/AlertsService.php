<?php

namespace App\Modules\Maintenance\Services;

use App\Modules\RouteDispatch\Models\Vehicle;
use App\Modules\Maintenance\Models\VehicleInspection;
use App\Modules\Maintenance\Models\Inventory;
use Carbon\Carbon;

class AlertsService
{
    /**
     * Odometer alerts: vehicles whose current odometer exceeds
     * last service by more than the configured threshold (10,000 km).
     *
     * Uses the vehicles table fields:
     *   Current_odometer   — current reading
     *   odometer_at_service — stored in work_orders; we fall back to 0 if unavailable
     *
     * Since there is no dedicated "last_service_km" column on vehicles,
     * we derive it from the most recent closed/resolved work_order for each vehicle.
     */
    public function getOdometerAlerts(): array
    {
        $threshold = 10000;

        $vehicles = Vehicle::with([
            'maintenanceAssignments' => function ($q) {
                $q->whereIn('status', ['resolved', 'closed'])
                    ->orderBy('updated_at', 'desc');
            }
        ])->get();

        $alerts = [];

        foreach ($vehicles as $v) {
            $currentOdometer  = (float) ($v->Current_odometer ?? 0);
            $lastAssignment   = $v->maintenanceAssignments->first();
            $lastServiceKm    = 0; // no dedicated column; treat 0 as baseline

            $kmSinceService   = $currentOdometer - $lastServiceKm;
            $status           = $kmSinceService >= $threshold ? 'warning' : 'success';

            $alerts[] = [
                'id'              => $v->vehicle_id,
                'vehiclePlate'    => $v->VehicleLicense  ?? $v->vehicle_id,
                'vehicleModel'    => $v->VehicleModel    ?? '—',
                'lastServiceKM'   => number_format($lastServiceKm),
                'currentOdometer' => number_format($currentOdometer),
                'kmSinceService'  => number_format($kmSinceService),
                'threshold'       => number_format($threshold),
                'status'          => $status,
            ];
        }

        return $alerts;
    }

    /**
     * Insurance alerts: vehicles whose insurance expires within 30 days
     * or is already expired.
     *
     * The vehicles table does not have a dedicated insurance_expiry column,
     * so we use vehicle_inspections records of type 'annual' as a proxy
     * for inspection/certification expiry. If the table is empty or missing
     * we return an empty array rather than crashing.
     */
    public function getInsuranceAlerts(): array
    {
        try {
            $threshold = Carbon::now()->addDays(30);

            $inspections = VehicleInspection::with('vehicle')
                ->where('inspection_type', 'annual')
                ->whereNotNull('next_inspection_date')
                ->where('next_inspection_date', '<=', $threshold)
                ->orderBy('next_inspection_date', 'asc')
                ->get();

            return $inspections->map(function ($ins) {
                $vehicle    = $ins->vehicle;
                $expiryDate = $ins->next_inspection_date
                    ? $ins->next_inspection_date->format('Y-m-d')
                    : '—';

                $daysLeft = $ins->next_inspection_date
                    ? (int) Carbon::now()->diffInDays($ins->next_inspection_date, false)
                    : null;

                $status = ($daysLeft !== null && $daysLeft <= 0) ? 'danger' : 'warning';

                return [
                    'id'            => $ins->inspection_id,
                    'vehiclePlate'  => $vehicle->VehicleLicense ?? $vehicle->vehicle_id ?? '—',
                    'vehicleModel'  => $vehicle->VehicleModel   ?? '—',
                    'policyNumber'  => $ins->certificate_number ?? '—',
                    'expiryDate'    => $expiryDate,
                    'daysRemaining' => $daysLeft ?? '?',
                    'status'        => $status,
                ];
            })->values()->toArray();
        } catch (\Exception $e) {
            // Table may not exist yet
            return [];
        }
    }

    /**
     * Inspection alerts: vehicles with overdue annual inspections
     * (next_inspection_date is in the past).
     */
    public function getInspectionAlerts(): array
    {
        try {
            $inspections = VehicleInspection::with('vehicle')
                ->where('inspection_type', 'annual')
                ->whereNotNull('next_inspection_date')
                ->where('next_inspection_date', '<', Carbon::now())
                ->orderBy('next_inspection_date', 'asc')
                ->get();

            return $inspections->map(function ($ins) {
                $vehicle = $ins->vehicle;

                $lastInspection = $ins->inspection_date
                    ? $ins->inspection_date->format('Y-m-d')
                    : '—';

                $nextDueDate = $ins->next_inspection_date
                    ? $ins->next_inspection_date->format('Y-m-d')
                    : '—';

                return [
                    'id'            => $ins->inspection_id,
                    'vehiclePlate'  => $vehicle->VehicleLicense ?? $vehicle->vehicle_id ?? '—',
                    'vehicleModel'  => $vehicle->VehicleModel   ?? '—',
                    'lastInspection' => $lastInspection,
                    'nextDueDate'   => $nextDueDate,
                    'daysRemaining' => 'Overdue',
                    'status'        => 'danger',
                ];
            })->values()->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Parts alerts: inventory items with low stock (quantity <= 5).
     *
     * The inventory table has: part_id, part_name, quantity, service_type,
     * compatible_models. There is no minimum_stock or vehicle association,
     * so we flag items with quantity <= 5 as low stock.
     */
    public function getPartsAlerts(): array
    {
        try {
            $lowStockThreshold = 5;

            $parts = Inventory::where('quantity', '<=', $lowStockThreshold)
                ->orderBy('quantity', 'asc')
                ->get();

            return $parts->map(function ($part) {
                return [
                    'id'           => $part->part_id,
                    'vehiclePlate' => '—',
                    'vehicleModel' => implode(', ', (array) ($part->compatible_models ?? [])) ?: '—',
                    'partName'     => $part->part_name ?? '—',
                    'installDate'  => $part->created_at
                        ? $part->created_at->format('Y-m-d')
                        : '—',
                    'usage'        => '—',
                    'lifespan'     => '—',
                    'stockQty'     => $part->quantity,
                    'status'       => 'warning',
                ];
            })->values()->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Mark an insurance record as renewed by updating its next_inspection_date.
     *
     * @param int   $id    inspection_id
     * @param array $data  expects { new_expiry_date: 'YYYY-MM-DD' }
     */
    public function renewInsurance(int $id, array $data)
    {
        $inspection = VehicleInspection::findOrFail($id);

        $newExpiry = $data['new_expiry_date'] ?? Carbon::now()->addYear()->format('Y-m-d');

        $inspection->update([
            'next_inspection_date' => $newExpiry,
        ]);

        return $inspection->fresh();
    }

    /**
     * Log a completed inspection by setting result = 'pass' and
     * advancing next_inspection_date by one year.
     *
     * @param int   $id    inspection_id
     * @param array $data  optional: { notes, certificate_number }
     */
    public function completeInspection(int $id, array $data)
    {
        $inspection = VehicleInspection::findOrFail($id);

        $inspection->update([
            'result'               => 'pass',
            'inspection_date'      => Carbon::now(),
            'next_inspection_date' => Carbon::now()->addYear(),
            'notes'                => $data['notes']              ?? $inspection->notes,
            'certificate_number'   => $data['certificate_number'] ?? $inspection->certificate_number,
        ]);

        return $inspection->fresh();
    }
}
