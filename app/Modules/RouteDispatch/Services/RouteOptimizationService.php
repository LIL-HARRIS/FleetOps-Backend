<?php

/**
 * @file: RouteOptimizationService.php
 * @description: خدمة تحسين المسارات - TSP, ETA, Clustering (RD-02/04/05/06)
 * @module: RouteDispatch
 * @author: Team Leader (Khalid)
 */

namespace App\Modules\RouteDispatch\Services;

use App\Modules\OrderManagement\Repositories\OrderRepository;
use App\Modules\RouteDispatch\Repositories\RouteRepository;
use App\Modules\RouteDispatch\Repositories\VehicleRepository;
use Exception;

class RouteOptimizationService
{
    protected RouteRepository $routeRepository;
    protected OrderRepository $orderRepository;
    protected VehicleRepository $vehicleRepository;

    public function __construct(
        RouteRepository $routeRepository,
        OrderRepository $orderRepository,
        VehicleRepository $vehicleRepository
    )
    {
        $this->routeRepository = $routeRepository;
        $this->orderRepository = $orderRepository;
        $this->vehicleRepository = $vehicleRepository;
    }

    /**
     * تحسين ترتيب المحطات (TSP Heuristic - RD-04 / fn06)
     * @param int $routeId
     * @return array  reordered stops with updated sequences
     * @throws Exception
     */
    public function optimizeStopSequence(int $routeId): array
    {
        // TODO: Optimize route stop sequence
        // 1. Get route with stops: $route = $this->routeRepository->getRouteWithStops($routeId)
        // 2. Extract stop coordinates
        // 3. Apply TSP Nearest Neighbor heuristic:
        //    - Start from warehouse/origin
        //    - At each step, go to the nearest unvisited stop
        //    - OR call Google Routes API for optimized waypoints
        // 4. Reorder stops by new sequence
        // 5. Update each stop's sequence in DB
        // 6. Recalculate ETAs for all stops
        // 7. Increment route version
        // 8. Return reordered stops array
    }

    /**
     * حساب ETA لكل محطة (RD-05 / fn05)
     * @param int $routeId
     * @param \DateTime $startTime
     * @return array  stops with updated ETAs
     */
    public function calculateETAs(int $routeId, \DateTime $startTime): array
    {
        // TODO: Calculate ETAs
        // 1. Get route stops in sequence order
        // 2. For each stop:
        //    ETA = previous_departure + (distance / avg_speed) + stop_duration_min
        //    OR use Google Routes API response times
        // 3. Check if any ETA exceeds promised_window_end → fire DeliveryWindowViolation event
        // 4. Update stop ETAs in DB
        // 5. Return updated stops
    }

    /**
     * التجميع الجغرافي للطلبات (RD-02 / fn02)
     * @param array $orderIds
     * @return array clusters in shape: [{color, zone, orders: [...]}]
     */
    public function clusterOrders(array $orderIds): array
    {
        $requestedOrderIds = array_values(array_unique(array_map('intval', $orderIds)));

        if ($requestedOrderIds === []) {
            return [];
        }

        $orders = $this->orderRepository->findByIds($requestedOrderIds)
            ->load([
                'customer' => fn ($query) => $query->select('customer_id', 'address'),
                'customer.user' => fn ($query) => $query->select('user_id', 'name'),
            ]);

        $ordersByArea = [];

        foreach ($orders as $order) {
            $area = trim((string) ($order->Area ?? ''));
            $zoneKey = $area !== '' ? mb_strtolower($area) : 'unknown';

            if (!array_key_exists($zoneKey, $ordersByArea)) {
                $ordersByArea[$zoneKey] = [];
            }

            $ordersByArea[$zoneKey][] = $order;
        }

        $palette = [
            '#f59e0b',
            '#10b981',
            '#3b82f6',
            '#ef4444',
            '#06b6d4',
            '#84cc16',
            '#f97316',
            '#6366f1',
            '#14b8a6',
            '#eab308',
        ];

        $clusters = [];
        $colorIndex = 0;

        foreach ($ordersByArea as $zoneKey => $zoneOrders) {
            if ($zoneKey === 'unknown') {
                $unknownClusterCount = max(1, (int) ceil(sqrt(count($zoneOrders))));
                $chunkSize = (int) ceil(count($zoneOrders) / $unknownClusterCount);
                $unknownChunks = array_chunk($zoneOrders, max(1, $chunkSize));

                foreach ($unknownChunks as $index => $chunk) {
                    $clusters[] = [
                        'color' => $palette[$colorIndex % count($palette)],
                        'zone' => 'unknown-' . ($index + 1),
                        'orders' => array_map(
                            static fn ($order) => $order->toArray(),
                            $chunk
                        ),
                    ];
                    $colorIndex++;
                }

                continue;
            }

            $clusters[] = [
                'color' => $palette[$colorIndex % count($palette)],
                'zone' => $zoneKey,
                'orders' => array_map(
                    static fn ($order) => $order->toArray(),
                    $zoneOrders
                ),
            ];
            $colorIndex++;
        }

        return $clusters;
    }

    /**
     * إدراج طلب عاجل في مسار نشط (RD-06 / fn07)
     * @param int $routeId
     * @param int $urgentOrderId
     * @return array  updated route stops
     * @throws Exception
     */
    public function insertUrgentOrder(int $routeId, int $urgentOrderId): array
    {
        // TODO: Emergency Express Insertion
        // 1. Get active route with stops
        // 2. Get urgent order coordinates
        // 3. Find best insertion point (minimizes additional distance)
        //    - Try inserting after each existing stop
        //    - Choose the position with minimum extra distance
        // 4. Insert stop at chosen position
        // 5. Resequence remaining stops
        // 6. Recalculate ETAs
        // 7. Increment version
        // 8. Return updated stops
    }

    /**
     * إعادة توزيع الطلبات عند تعطل مركبة (RD-07 / fn04)
     * @param int $brokenRouteId
     * @param array $availableRouteIds
     * @return array  redistribution result per route
     * @throws Exception
     */
    public function redistributeOrders(int $brokenRouteId, array $availableRouteIds): array
    {
        // TODO: Breakdown redistribution
        // 1. Get all incomplete stops from broken route
        // 2. Check capacity of available routes/vehicles
        // 3. Distribute orders to available routes (load-balanced)
        // 4. Recalculate ETAs for all affected routes
        // 5. Notify customers of updated ETAs
        // 6. Update broken route status to 'cancelled'
        // 7. Return redistribution summary
    }

    /**
     * التحقق من سعة التحميل (RD-03 / fn03)
     * @param array $clusters
     * @return array<int, array<string, mixed>>
     */
    public function checkLoadCapacity(array $clusters): array
    {
        $results = [];

        foreach ($clusters as $cluster) {
            $vehicleId = (int) ($cluster['vehicle_id'] ?? 0);
            $orders = is_array($cluster['orders'] ?? null) ? $cluster['orders'] : [];
            $vehicle = $this->vehicleRepository->findById($vehicleId);

            if ($vehicle === null) {
                $results[] = [
                    'color' => $cluster['color'] ?? null,
                    'zone' => $cluster['zone'] ?? null,
                    'vehicle_id' => $vehicleId,
                    'vehicle' => null,
                    'valid' => false,
                    'message' => 'Vehicle not found.',
                    'summary' => [
                        'orders_count' => count($orders),
                        'weight_used' => 0,
                        'volume_used' => 0,
                        'max_weight_capacity' => null,
                        'max_volume_capacity' => null,
                        'weight_usage_percent' => null,
                        'volume_usage_percent' => null,
                        'fits_weight' => false,
                        'fits_volume' => false,
                    ],
                    'orders' => $this->normalizeOrders($orders),
                ];

                continue;
            }

            $weightUsed = 0.0;
            $volumeUsed = 0.0;

            foreach ($orders as $order) {
                $weightUsed += (float) ($order['Weight'] ?? 0);
                $volumeUsed += (float) ($order['Volume'] ?? 0);
            }

            $maxWeightCapacity = (float) ($vehicle->MaxWeightCapacity ?? 0);
            $maxVolumeCapacity = (float) ($vehicle->MaxVolume ?? 0);
            $weightUsagePercent = $maxWeightCapacity > 0 ? round(($weightUsed / $maxWeightCapacity) * 100, 2) : null;
            $volumeUsagePercent = $maxVolumeCapacity > 0 ? round(($volumeUsed / $maxVolumeCapacity) * 100, 2) : null;
            $fitsWeight = $maxWeightCapacity > 0 && $weightUsed <= $maxWeightCapacity;
            $fitsVolume = $maxVolumeCapacity > 0 && $volumeUsed <= $maxVolumeCapacity;

            $results[] = [
                'color' => $cluster['color'] ?? null,
                'zone' => $cluster['zone'] ?? null,
                'vehicle_id' => $vehicleId,
                'vehicle' => [
                    'vehicle_id' => $vehicle->vehicle_id,
                    'VehicleModel' => $vehicle->VehicleModel,
                    'VehicleType' => $vehicle->VehicleType,
                    'MaxWeightCapacity' => $vehicle->MaxWeightCapacity,
                    'MaxVolume' => $vehicle->MaxVolume,
                    'Status' => $vehicle->Status,
                ],
                'valid' => $fitsWeight && $fitsVolume,
                'message' => ($fitsWeight && $fitsVolume)
                    ? 'Cluster fits within vehicle capacity.'
                    : 'Cluster exceeds vehicle capacity.',
                'summary' => [
                    'orders_count' => count($orders),
                    'weight_used' => round($weightUsed, 2),
                    'volume_used' => round($volumeUsed, 2),
                    'max_weight_capacity' => $maxWeightCapacity,
                    'max_volume_capacity' => $maxVolumeCapacity,
                    'weight_usage_percent' => $weightUsagePercent,
                    'volume_usage_percent' => $volumeUsagePercent,
                    'fits_weight' => $fitsWeight,
                    'fits_volume' => $fitsVolume,
                ],
                'orders' => $this->normalizeOrders($orders),
            ];
        }

        return $results;
    }

    /**
     * Normalize order payloads for the capacity check response.
     *
     * @param array $orders
     * @return array<int, array<string, mixed>>
     */
    private function normalizeOrders(array $orders): array
    {
        return array_map(static function (array $order): array {
            return [
                'OrderID' => $order['OrderID'] ?? null,
                'Weight' => isset($order['Weight']) ? (float) $order['Weight'] : null,
                'Volume' => isset($order['Volume']) ? (float) $order['Volume'] : null,
                'Status' => $order['Status'] ?? null,
                'Area' => $order['Area'] ?? null,
                'customer' => $order['customer'] ?? null,
            ];
        }, $orders);
    }
}
