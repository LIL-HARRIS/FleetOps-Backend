<?php

/**
 * @file: DispatchService.php
 * @description: خدمة التعيين والتوزيع - ربط السائقين بالمسارات (RD-01 / fn01)
 * @module: RouteDispatch
 * @author: Team Leader (Khalid)
 */

namespace App\Modules\RouteDispatch\Services;

use App\Modules\OrderManagement\Repositories\OrderRepository;
use App\Modules\RouteDispatch\Repositories\RouteRepository;
use App\Modules\RouteDispatch\Repositories\VehicleRepository;
use Exception;
use Illuminate\Validation\ValidationException;

class DispatchService
{
    protected RouteRepository $routeRepository;
    protected VehicleRepository $vehicleRepository;
    protected OrderRepository $orderRepository;

    public function __construct(
        RouteRepository $routeRepository,
        VehicleRepository $vehicleRepository,
        OrderRepository $orderRepository
    )
    {
        $this->routeRepository   = $routeRepository;
        $this->vehicleRepository = $vehicleRepository;
        $this->orderRepository   = $orderRepository;
    }

    /**
     * تعيين سائق ومركبة لمسار (RD-01 / fn01)
     * @param int $routeId
     * @param int $driverId
     * @param int $vehicleId
     * @return mixed  updated Route
     * @throws Exception
     */
    public function assignDriverAndVehicle(int $routeId, int $driverId, int $vehicleId)
    {
        // TODO: Assign driver and vehicle to route
        // 1. Get route: must be in 'planned' status
        // 2. Get vehicle: must be 'available'
        // 3. Get driver: must be active, role = 'driver'
        // 4. Validate license match (RD-09 / fn08):
        //    vehicle->required_license_type === driver->license_type OR driver has heavy license for any vehicle
        // 5. Check driver not already active on another route
        // 6. Update route: driver_id, vehicle_id
        // 7. Update vehicle status to 'in_service' (tentative — confirmed on startRoute)
        // 8. Fire event: DriverAssigned
        // 9. Return updated route
    }

    /**
     * التحقق من تطابق الرخصة (RD-09 / fn08)
     * @param string $vehicleType  (light | heavy | refrigerated)
     * @param string $licenseType  (light | heavy)
     * @return bool
     */
    public function isLicenseCompatible(string $vehicleType, string $licenseType): bool
    {
        // TODO: Check license compatibility
        // Heavy license can drive light vehicles
        // Light license can only drive light vehicles
        // Heavy license required for: heavy, refrigerated
        // if ($licenseType === 'heavy') return true; // heavy can drive everything
        // return $vehicleType === 'light'; // light license only for light vehicles
    }

    /**
     * التحقق من أن السائق غير مشغول
     * @param int $driverId
     * @return bool  true = available
     */
    public function isDriverAvailable(int $driverId): bool
    {
        // TODO: Check driver has no active route
        // $activeRoute = $this->routeRepository->getDriverActiveRoute($driverId);
        // return $activeRoute === null;
    }

    /**
     * إعادة توزيع الطلبات عند تعطل مركبة (RD-07 / fn04)
     * @param int $brokenRouteId
     * @param array $availableRouteIds
     * @return array redistribution summary
     * @throws Exception
     */
    public function redistributeOnBreakdown(int $brokenRouteId, array $availableRouteIds): array
    {
        // TODO: Redistribute stops from broken route
        // 1. Get remaining stops from broken route (status = pending)
        // 2. Validate at least one available route exists
        // 3. Check capacity of each available route's vehicle
        // 4. Distribute stops load-balanced across available routes
        // 5. Recalculate ETAs for each affected route
        // 6. Set broken route status to 'cancelled'
        // 7. Notify customers of new ETAs via NotificationService
        // 8. Return redistribution summary per route
    }
    
    
    /**
     * حساب درجة أولوية الطلبات باستخدام ثلاثة عوامل:
     * 1. نوع الطلب - Express, Normal, Low (35%)
     * 2. السلع القابلة للتلف (35%)
     * 3. نافذة الالتزام بالتسليم (30%)
     *
     * @param array $orderIds
     * @return array Collection of full orders with priority_score and factors
     */
    public function calculatePriorityScores(array $orderIds): array
    {
        $requestedOrderIds = array_values(array_unique(array_map('intval', $orderIds)));
        $orders = $this->orderRepository->findByIds($requestedOrderIds);

        $foundOrderIds = $orders->pluck('OrderID')->map(fn ($orderId) => (int) $orderId)->all();
        $missingOrderIds = array_values(array_diff($requestedOrderIds, $foundOrderIds));

        if ($missingOrderIds !== []) {
            throw ValidationException::withMessages([
                'order_ids' => ['The following order ids were not found: ' . implode(', ', $missingOrderIds)],
            ]);
        }

        $nonPendingOrderIds = $orders
            ->filter(fn ($order) => $order->Status !== 'Pending')
            ->pluck('OrderID')
            ->map(fn ($orderId) => (int) $orderId)
            ->all();

        if ($nonPendingOrderIds !== []) {
            throw ValidationException::withMessages([
                'order_ids' => ['The following order ids are not pending: ' . implode(', ', $nonPendingOrderIds)],
            ]);
        }

        $config = config('priority');
        $weights = $config['weights'] ?? [];
        $ordersWithScores = [];

        foreach ($orders as $order) {
            // Factor 1: Order Type (35%)
            $orderTypeScore = $this->calculateOrderTypeScore($order, $config);

            // Factor 2: Perishable Goods (35%)
            $perishableScore = $this->calculatePerishableScore($order, $config);

            // Factor 3: Delivery Window Urgency (30%)
            $deliveryWindowScore = $this->calculateDeliveryWindowScore($order, $config);

            // Weighted total priority score
            $totalScore = 
                ($orderTypeScore * ($weights['order_type'] ?? 0.35)) +
                ($perishableScore * ($weights['perishable_goods'] ?? 0.35)) +
                ($deliveryWindowScore * ($weights['delivery_window'] ?? 0.30));

            // Add priority score to order
            $order->priority_score = max(0, min(100, (int) $totalScore));
            $order->priority_factors = [
                'order_type' => $order->Type ?? 'Normal',
                'order_type_score' => (int) $orderTypeScore,
                'perishable_score' => (int) $perishableScore,
                'delivery_window_score' => (int) $deliveryWindowScore,
            ];

            $ordersWithScores[] = $order;
        }

        return $ordersWithScores;
    }

    /**
     * عامل نوع الطلب
     * Express → أولوية عالية (95)
     * Normal → أولوية متوسطة (50)
     * Low → أولوية منخفضة (25)
     * 
     * @param mixed $order
     * @param array $config
     * @return int Score 0-100
     */
    private function calculateOrderTypeScore($order, array $config): int
    {
        if (!($config['order_type']['enabled'] ?? true)) {
            return 50; // Default medium
        }

        $type = $order->Type ?? 'Normal';
        $typeScores = $config['order_type']['types'] ?? [
            'Express' => 95,
            'Normal' => 50,
            'Low' => 25,
        ];

        return $typeScores[$type] ?? 50;
    }

    /**
     * عامل السلع القابلة للتلف
     * إذا كانت السلع قابلة للتلف (Perishable = true)، تحصل على درجة أساسية عالية
     * 
     * @param mixed $order
     * @param array $config
     * @return int Score 0-100
     */
    private function calculatePerishableScore($order, array $config): int
    {
        if (!($config['perishable']['enabled'] ?? true)) {
            return 0;
        }

        if ((bool) ($order->Perishable ?? false)) {
            return $config['perishable']['base_score'] ?? 80;
        }

        return 0;
    }

    /**
     * عامل نافذة الالتزام بالتسليم
     * كلما اقتربت نافذة التسليم من الوقت الحالي، ارتفعت الدرجة
     * 
     * @param mixed $order
     * @param array $config
     * @return int Score 0-100
     */
    private function calculateDeliveryWindowScore($order, array $config): int
    {
        if (!($config['delivery_window']['enabled'] ?? true)) {
            return 50; // Default medium priority
        }

        $promisedWindow = $order->PromisedWindow;

        if (!$promisedWindow) {
            return 50; // Default if no deadline specified
        }

        $now = now();
        $hoursRemaining = $now->diffInHours($promisedWindow, false);

        // If deadline has passed, it's critical
        if ($hoursRemaining <= 0) {
            return 100;
        }

        $thresholds = $config['delivery_window']['hours_to_deadline'] ?? [];

        if ($hoursRemaining <= 2) {
            return $thresholds['within_2_hours'] ?? 100;
        } elseif ($hoursRemaining <= 4) {
            return $thresholds['within_4_hours'] ?? 85;
        } elseif ($hoursRemaining <= 8) {
            return $thresholds['within_8_hours'] ?? 70;
        } elseif ($hoursRemaining <= 24) {
            return $thresholds['within_24_hours'] ?? 50;
        }

        return $thresholds['beyond_24_hours'] ?? 30;
    }
}
