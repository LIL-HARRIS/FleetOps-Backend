<?php

/**
 * Migration: create_driver_performance_table
 * DDL Source: FleetOpsDB.dbo.DriverPerformance
 * Execution Tier: 2 — FK → drivers
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_performance', function (Blueprint $table) {
            // DDL: PerformanceID bigint IDENTITY(1,1) — PK
            $table->bigIncrements('performance_id');

            // DDL: DriverID bigint NOT NULL FK → drivers.driver_id ON DELETE CASCADE
            $table->unsignedBigInteger('driver_id');

            // DDL: PeriodStart date NOT NULL
            $table->date('period_start');

            // DDL: PeriodEnd date NOT NULL
            $table->date('period_end');

            // DDL: Trip counts (int DEFAULT 0)
            $table->integer('total_trips_assigned')->default(0);
            $table->integer('completed_trips')->default(0);
            $table->integer('failed_trips')->default(0);
            $table->integer('cancelled_trips')->default(0);

            // DDL: Delivery metrics (int DEFAULT 0)
            $table->integer('on_time_deliveries')->default(0);
            $table->integer('late_deliveries')->default(0);

            // DDL: OnTimeDeliveryPct decimal(5,2) NULL
            $table->decimal('on_time_delivery_pct', 5, 2)->nullable();

            // DDL: Distance & Fuel (decimal DEFAULT 0)
            $table->decimal('total_distance_km', 10, 2)->default(0);
            $table->decimal('avg_speed_kmh', 6, 2)->nullable();
            $table->decimal('total_fuel_litres', 10, 2)->default(0);
            $table->decimal('fuel_per_100km', 6, 2)->nullable();

            // DDL: Incident & Behavior metrics (int DEFAULT 0)
            $table->integer('incident_count')->default(0);
            $table->integer('speeding_events')->default(0);
            $table->integer('customer_complaints')->default(0);
            $table->integer('customer_compliments')->default(0);

            // DDL: AvgCustomerRating decimal(3,2) NULL
            $table->decimal('avg_customer_rating', 3, 2)->nullable();

            // DDL: Activity hours (decimal DEFAULT 0)
            $table->decimal('total_active_hours', 8, 2)->default(0);
            $table->decimal('idle_hours', 8, 2)->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);

            // Timestamps
            $table->timestamps();

            // FK_DP_Driver → drivers.driver_id ON DELETE CASCADE
            $table->foreign('driver_id')
                  ->references('driver_id')
                  ->on('drivers')
                  ->cascadeOnDelete();

            // UQ_DP_DriverPeriod — UNIQUE (driver_id, period_start)
            $table->unique(['driver_id', 'period_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_performance');
    }
};
