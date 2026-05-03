<?php

/**
 * Migration: create_vehicles_table
 * DDL Source: FleetOpsDB.dbo.Vehicle
 * Execution Tier: 0 (no foreign key dependencies)
 * @author Team Leader (Khalid)
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            // DDL: vehicle_id bigint IDENTITY(1,1) — PK
            $table->bigIncrements('vehicle_id');

            // DDL: VehicleModel nvarchar(100) NOT NULL
            $table->string('VehicleModel', 100);

            // DDL: VehicleType nvarchar(20) NOT NULL CHECK (light|heavy|refrigerated)
            $table->string('VehicleType', 20);

            // DDL: VehicleLicense nvarchar(50) NOT NULL UNIQUE
            $table->string('VehicleLicense', 50)->unique();

            // DDL: MaxWeightCapacity decimal(10,2) NULL
            $table->decimal('MaxWeightCapacity', 10, 2)->nullable();

            // DDL: Status nvarchar(30) DEFAULT 'Active' CHECK (Active|Maintenance|Inactive|OutOfService)
            $table->string('Status', 30)->nullable()->default('Active');

            // DDL: Current_odometer decimal(12,2) NOT NULL CHECK >= 0
            $table->decimal('Current_odometer', 12, 2);

            // DDL: MaxVolume decimal(10,2) NULL
            $table->decimal('MaxVolume', 10, 2)->nullable();

            // DDL: MarketValue int NULL
            $table->integer('MarketValue')->nullable();

            // DDL: CreatedAt datetime2 DEFAULT getdate() NULL
            $table->dateTime('CreatedAt')->nullable()->useCurrent();

            // DDL: UpdatedAt datetime2 NULL
            $table->dateTime('UpdatedAt')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
