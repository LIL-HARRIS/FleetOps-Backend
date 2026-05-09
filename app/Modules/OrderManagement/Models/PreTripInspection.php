<?php

/**
 * @file PreTripInspection.php
 * @description Eloquent Model for the pre_trip_inspections table — OrderManagement Module
 * @module OrderManagement
 * @table pre_trip_inspections
 * @author Team Leader (Khalid)
 */

namespace App\Modules\OrderManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Modules\AuthIdentity\Models\Driver;
use App\Modules\RouteDispatch\Models\Vehicle;

class PreTripInspection extends Model
{
    use HasFactory;

    protected $table      = 'pre_trip_inspections';
    protected $primaryKey = 'inspection_id';
    protected $keyType    = 'int';
    public $incrementing  = true;

    // No timestamps in DDL (has inspection_ts instead)
    public $timestamps = false;

    /**
     * DB columns that can be mass-assigned.
     * Mirrors: pre_trip_inspections DDL (inspection_id is PK/auto, is_success is computed).
     *
     * @var array<string>
     */
    protected $fillable = [
        'driver_id',         // FK → drivers.driver_id
        'vehicle_id',        // FK → vehicles.vehicle_id
        'inspection_ts',     // datetimeoffset — defaults to now() in DB
        'odometer_reading',  // decimal(12,2), >= 0
        'fuel_level',        // tinyint 0–100
        'tires_ok',          // derived: pressure_tread_depth && wheel_nut_security && sidewall_condition && spare_tire
        'brakes_ok',         // derived: service_brake_test && parking_brake_engagement && air_leakage_check
        'lights_ok',         // derived: headlights_indicators && brake_tail_lights && reflectors_markers
        'fluids_ok',         // derived: documents_ok (insurance + registration + route_manifest)
                             //          AND engine_ok  (mirror_adjustments + wipers_fluid + emergency_kit)
        // is_success — PERSISTED computed column (tires_ok & brakes_ok & lights_ok & fluids_ok), DO NOT fill
    ];

    /**
     * Attribute casting — keeps PHP types aligned with DB column types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'inspection_ts'    => 'datetime',
        'odometer_reading' => 'float',
        'fuel_level'       => 'integer',
        'tires_ok'         => 'boolean',
        'brakes_ok'        => 'boolean',
        'lights_ok'        => 'boolean',
        'fluids_ok'        => 'boolean',
        'is_success'       => 'boolean',  // computed/stored — read-only
    ];

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Returns true if all four aggregate checks passed.
     * Mirrors the DB computed column: is_success.
     */
    public function allChecksPassed(): bool
    {
        return $this->tires_ok
            && $this->brakes_ok
            && $this->lights_ok
            && $this->fluids_ok;
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    /** The driver who performed this inspection */
    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'driver_id');
    }

    /** The vehicle that was inspected */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'vehicle_id');
    }
}
