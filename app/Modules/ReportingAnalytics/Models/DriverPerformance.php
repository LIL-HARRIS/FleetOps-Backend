<?php

/**
 * @file DriverPerformance.php
 * @description Eloquent Model for the driver_performance table — ReportingAnalytics Module
 * @module ReportingAnalytics
 * @table driver_performance
 * @author Team Leader (Khalid)
 */

namespace App\Modules\ReportingAnalytics\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Modules\AuthIdentity\Models\Driver;

class DriverPerformance extends Model
{
    use HasFactory;

    protected $table = 'driver_performance';
    protected $primaryKey = 'performance_id';
    protected $keyType = 'int';
    public $incrementing = true;

    // Timestamps enabled
    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /** @var array<string> */
    protected $fillable = [
        'driver_id',
        'period_start',
        'period_end',
        'total_trips_assigned',
        'completed_trips',
        'failed_trips',
        'cancelled_trips',
        'on_time_deliveries',
        'late_deliveries',
        'on_time_delivery_pct',
        'total_distance_km',
        'avg_speed_kmh',
        'total_fuel_litres',
        'fuel_per_100km',
        'incident_count',
        'speeding_events',
        'customer_complaints',
        'customer_compliments',
        'avg_customer_rating',
        'total_active_hours',
        'idle_hours',
        'overtime_hours',
        'created_at',
        'updated_at',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'period_start'            => 'date',
        'period_end'              => 'date',
        'total_trips_assigned'    => 'integer',
        'completed_trips'         => 'integer',
        'failed_trips'            => 'integer',
        'cancelled_trips'         => 'integer',
        'on_time_deliveries'      => 'integer',
        'late_deliveries'         => 'integer',
        'on_time_delivery_pct'    => 'float',
        'total_distance_km'       => 'float',
        'avg_speed_kmh'           => 'float',
        'total_fuel_litres'       => 'float',
        'fuel_per_100km'          => 'float',
        'incident_count'          => 'integer',
        'speeding_events'         => 'integer',
        'customer_complaints'     => 'integer',
        'customer_compliments'    => 'integer',
        'avg_customer_rating'     => 'float',
        'total_active_hours'      => 'float',
        'idle_hours'              => 'float',
        'overtime_hours'          => 'float',
        'created_at'              => 'datetime',
        'updated_at'              => 'datetime',
    ];

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeForDriver($query, int $driverId)
    {
        return $query->where('driver_id', $driverId);
    }

    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('period_start', [$startDate, $endDate]);
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    /** The driver this performance record is for */
    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'driver_id');
    }
}
