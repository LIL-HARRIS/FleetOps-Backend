<?php

/**
 * @file: InspectionController.php
 * @description: متحكم فحص ما قبل الرحلة - Order Management Service (fn12)
 * @module: OrderManagement
 * @author: Team Leader (Khalid)
 */

namespace App\Modules\OrderManagement\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InspectionController extends Controller
{
    /**
     * تسجيل فحص ما قبل الرحلة
     * POST /api/v1/orders/inspections
     */
    public function store(Request $request): JsonResponse
    {
        // 1. Validate granular checkbox fields that mirror the Pre-Trip Inspection UI
        $validated = $request->validate([
            // Identity fields
            'driver_id'    => 'required|integer',
            'vehicle_id'   => 'required|integer',
            'route_id'     => 'required|integer',

            // Tires (4 sub-checks)
            'pressure_tread_depth'     => 'required|boolean',
            'wheel_nut_security'       => 'required|boolean',
            'sidewall_condition'       => 'required|boolean',
            'spare_tire'               => 'required|boolean',

            // Brakes (3 sub-checks)
            'service_brake_test'       => 'required|boolean',
            'parking_brake_engagement' => 'required|boolean',
            'air_leakage_check'        => 'required|boolean',

            // Lights (3 sub-checks)
            'headlights_indicators'    => 'required|boolean',
            'brake_tail_lights'        => 'required|boolean',
            'reflectors_markers'       => 'required|boolean',

            // Documents (3 sub-checks)
            'insurance_verification'   => 'required|boolean',
            'registration_receipt'     => 'required|boolean',
            'route_manifest'           => 'required|boolean',

            // Cabin / Other (3 sub-checks)
            'mirror_adjustments'       => 'required|boolean',
            'wipers_fluid'             => 'required|boolean',
            'emergency_kit_check'      => 'required|boolean',

            // Meter readings
            'odometer_reading'         => 'required|numeric|min:0',
            'fuel_level'               => 'required|integer|min:0|max:100',
        ]);

        // 2. Derive aggregate category booleans from sub-checks
        $tires_ok     = $validated['pressure_tread_depth']
                     && $validated['wheel_nut_security']
                     && $validated['sidewall_condition']
                     && $validated['spare_tire'];

        $brakes_ok    = $validated['service_brake_test']
                     && $validated['parking_brake_engagement']
                     && $validated['air_leakage_check'];

        $lights_ok    = $validated['headlights_indicators']
                     && $validated['brake_tail_lights']
                     && $validated['reflectors_markers'];

        $documents_ok = $validated['insurance_verification']
                     && $validated['registration_receipt']
                     && $validated['route_manifest'];

        $engine_ok    = $validated['mirror_adjustments']
                     && $validated['wipers_fluid']
                     && $validated['emergency_kit_check'];

        // 3. Calculate 'passed' = all aggregate category checks are true

        $passed = $tires_ok && $brakes_ok && $lights_ok && $documents_ok && $engine_ok;

        // 4. If !passed → driver cannot start route (return warning)
        if (!$passed) {
            return response()->json([
                'success' => false,
                'message' => 'Pre-trip inspection failed. Driver cannot start route.',
                'route_can_start' => false
            ], 400);
        }

        // 5. Create inspection record — only DB columns from pre_trip_inspections table
        //    route_id / documents_ok / engine_ok / passed have no DB column → not persisted
        $inspection = \App\Modules\OrderManagement\Models\PreTripInspection::create([
            'driver_id'        => $validated['driver_id'],
            'vehicle_id'       => $validated['vehicle_id'],
            // inspection_ts — defaults to sysdatetimeoffset() in DB, no need to set
            'odometer_reading' => $validated['odometer_reading'],
            'fuel_level'       => $validated['fuel_level'],
            'tires_ok'         => $tires_ok,
            'brakes_ok'        => $brakes_ok,
            'lights_ok'        => $lights_ok,
            'fluids_ok'        => $documents_ok && $engine_ok, // DB has one boolean for "documents + cabin" group
            // is_success is a PERSISTED computed column — DB calculates it automatically
        ]);

        // 6. Return inspection with 'route_can_start' flag
        return response()->json([
            'success' => true,
            'message' => 'Pre-trip inspection recorded successfully.',
            'route_can_start' => true,
            'data'    => $inspection
        ], 201);
    }

    /**
     * جلب فحوصات مركبة معينة
     * GET /api/v1/orders/inspections/vehicle/{vehicleId}
     */
    public function forVehicle(int $vehicleId): JsonResponse
    {
        // TODO: return pre-trip inspections for vehicle (paginated)
    }

    /**
     * جلب أحدث فحص قبل الرحلة لمسار معين
     * GET /api/v1/orders/inspections/route/{routeId}
     */
    public function forRoute(int $routeId): JsonResponse
    {
        // TODO: return latest inspection for given route
    }
}
