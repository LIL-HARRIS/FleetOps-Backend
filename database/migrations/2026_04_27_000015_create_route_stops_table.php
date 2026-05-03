<?php

/**
 * Migration: create_route_stops_table
 * DDL Source: FleetOpsDB.dbo.RouteStop
 * Execution Tier: 4 — FK → routes (CASCADE), orders
 * @author Team Leader (Khalid)
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_stops', function (Blueprint $table) {
            // DDL: StopID bigint IDENTITY(1,1) — PK
            $table->bigIncrements('stop_id');

            // DDL: RouteID bigint NOT NULL FK → routes ON DELETE CASCADE
            $table->unsignedBigInteger('route_id');

            // DDL: StopNo int NOT NULL
            $table->integer('stop_no');

            // DDL: OrderID bigint NULL FK → orders (no cascade in DDL)
            $table->unsignedBigInteger('order_id')->nullable();

            // DDL: ETA datetime2 NULL
            $table->dateTime('eta')->nullable();

            // DDL: ActualArrivalTime datetime2 NULL
            $table->dateTime('actual_arrival_time')->nullable();

            // DDL: Latitude decimal(10,8) NULL
            $table->decimal('latitude', 10, 8)->nullable();

            // DDL: Longitude decimal(11,8) NULL
            $table->decimal('longitude', 11, 8)->nullable();

            // DDL: UNIQUE (RouteID, StopNo)
            $table->unique(['route_id', 'stop_no'], 'uk_route_stop');

            // FK → routes.route_id ON DELETE CASCADE
            $table->foreign('route_id')
                  ->references('route_id')
                  ->on('routes')
                  ->cascadeOnDelete();

            // FK → Order.OrderID (no cascade in DDL)
            $table->foreign('order_id')
                  ->references('OrderID')
                  ->on('order')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_stops');
    }
};
