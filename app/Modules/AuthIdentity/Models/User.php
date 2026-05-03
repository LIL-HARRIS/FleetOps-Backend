<?php

/**
 * @file User.php
 * @description Eloquent Model for the users table — AuthIdentity Module
 * @module AuthIdentity
 * @table users
 * @author Team Leader (Khalid)
 */

namespace App\Modules\AuthIdentity\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $table = 'users';
    protected $primaryKey = 'user_id';
    protected $keyType = 'int';
    public $incrementing = true;

    // Eloquent uses created_at / updated_at by default — matches DDL columns
    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /** @var array<string> */
    protected $fillable = [
        'email',
        'password',   // DDL: PasswordHash — normalized for Laravel Auth
        'name',
        'phone_no',
        'role',
        'is_active',
    ];

    /** @var array<string> */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    // ─── Helper Methods ───────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    // ─── Relationships — Role Profiles ────────────────────────────────────────

    /** The customer profile linked to this user (role = Customer) */
    public function customer()
    {
        return $this->hasOne(Customer::class, 'customer_id', 'user_id');
    }

    /** The driver profile linked to this user (role = Driver) */
    public function driver()
    {
        return $this->hasOne(Driver::class, 'driver_id', 'user_id');
    }

    /** The dispatcher profile linked to this user (role = Dispatcher) */
    public function dispatcher()
    {
        return $this->hasOne(Dispatcher::class, 'dispatcher_id', 'user_id');
    }

    /** The fleet manager profile linked to this user (role = FleetManager) */
    public function fleetManager()
    {
        return $this->hasOne(FleetManager::class, 'fleet_manager_id', 'user_id');
    }

    /** The mechanic profile linked to this user (role = Mechanic) */
    public function mechanic()
    {
        return $this->hasOne(Mechanic::class, 'mechanic_id', 'user_id');
    }


}
