<?php

/**
 * @file Driver.php
 * @description Eloquent Model for the drivers table — AuthIdentity Module
 * @module AuthIdentity
 * @table drivers
 * @author Team Leader (Khalid)
 */

namespace App\Modules\AuthIdentity\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Modules\RouteDispatch\Models\Vehicle;
use App\Modules\RouteDispatch\Models\Route;
use App\Modules\OrderManagement\Models\Order;
use App\Modules\OrderManagement\Models\Parcel;
use App\Modules\OrderManagement\Models\PreTripInspection;
use App\Modules\ReportingAnalytics\Models\IncidentReport;
use App\Modules\ReportingAnalytics\Models\CashLedger;

class Driver extends Model
{
    use HasFactory;

    public const LICENSE_TYPES = ['light', 'heavy', 'refrigerated'];

    protected $table      = 'drivers';
    protected $primaryKey = 'driver_id';
    protected $keyType    = 'int';
    public $incrementing  = false; // PK is also a FK — no auto-increment

    // Only CreatedAt in DDL
    const UPDATED_AT = null;
    const CREATED_AT = 'created_at';

    /** @var array<string> */
    protected $fillable = [
        'driver_id',        // Set by application (mirrors user_id)
        'license_no',
        'license_type',
        'vehicle_id',
        'status',
        'score',
        'created_at',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'score'        => 'integer',
        'license_type' => 'string',
        'created_at'   => 'datetime',
    ];

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeAvailable($query)
    {
        return $query->where('status', 'Available');
    }

    public function scopeOnShift($query)
    {
        return $query->where('status', 'OnShift');
    }

    public function scopeByScore($query, int $minScore = 0)
    {
        return $query->where('score', '>=', $minScore);
    }

    public function scopeByLicenseType($query, string $licenseType)
    {
        return $query->where('license_type', $licenseType);
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    /** The base user account for this driver */
    public function user()
    {
        return $this->belongsTo(User::class, 'driver_id', 'user_id');
    }

    /** The vehicle currently assigned to this driver */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'vehicle_id');
    }

    /** Routes assigned to this driver */
    public function routes()
    {
        return $this->hasMany(Route::class, 'driver_id', 'driver_id');
    }

    /** Orders assigned to this driver */
    public function orders()
    {
        return $this->hasMany(Order::class, 'driver_id', 'driver_id');
    }

    /** Parcels assigned to this driver */
    public function parcels()
    {
        return $this->hasMany(Parcel::class, 'driver_id', 'driver_id');
    }

    /** Pre-trip inspections performed by this driver */
    public function preTripInspections()
    {
        return $this->hasMany(PreTripInspection::class, 'driver_id', 'driver_id');
    }

    /** Incident reports filed by this driver */
    public function incidentReports()
    {
        return $this->hasMany(IncidentReport::class, 'driver_id', 'driver_id');
    }

    /** Cash ledger transactions recorded by this driver */
    public function cashLedgerEntries()
    {
        return $this->hasMany(CashLedger::class, 'driver_id', 'driver_id');
    }
}
