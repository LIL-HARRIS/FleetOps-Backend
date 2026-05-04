<?php

/**
 * Migration: create_drivers_table
 * DDL Source: FleetOpsDB.dbo.Driver
 * Execution Tier: 1 — FK → users, vehicles
 * @author Team Leader (Khalid)
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $table) {
            // DDL: DriverID bigint NOT NULL — PK IS the FK (no auto-increment)
            $table->unsignedBigInteger('driver_id');
            $table->primary('driver_id');

            // DDL: LicenseNo nvarchar(50) NOT NULL UNIQUE
            $table->string('license_no', 50)->unique();

            // License type: light | heavy | refrigerated
            $table->enum('license_type', ['light', 'heavy', 'refrigerated']);

            // DDL: VehicleID bigint NULL FK → vehicles ON DELETE CASCADE
            $table->unsignedBigInteger('vehicle_id')->nullable();

            // DDL: Status nvarchar(30) DEFAULT 'Available' CHECK (Available|OnShift|OffShift|Busy|Break)
            $table->enum('status', ['Available', 'OnShift', 'OffShift', 'Busy', 'Break'])
                  ->default('Available')
                  ->nullable();

            // DDL: Score int 0-100
            $table->integer('score')->default(0);

            // DDL: CreatedAt datetime2 DEFAULT getdate() NULL (no UpdatedAt in DDL)
            $table->dateTime('created_at')->nullable();

            // DDL: FK__Drivers__DriverI → users.user_id ON DELETE CASCADE
            $table->foreign('driver_id')
                  ->references('user_id')
                  ->on('users')
                  ->cascadeOnDelete();

            // DDL: FK__Drivers__Vehicle → vehicles.vehicle_id ON DELETE CASCADE
            $table->foreign('vehicle_id')
                  ->references('vehicle_id')
                  ->on('vehicles')
                  ->cascadeOnDelete()
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
