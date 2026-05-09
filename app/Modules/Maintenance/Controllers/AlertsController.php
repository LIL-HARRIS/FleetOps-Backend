<?php

namespace App\Modules\Maintenance\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Modules\Maintenance\Services\AlertsService;

class AlertsController extends Controller
{
    protected AlertsService $alertsService;

    public function __construct(AlertsService $alertsService)
    {
        $this->alertsService = $alertsService;
    }

    /**
     * GET alerts-odometer
     * Vehicles exceeding maintenance thresholds.
     */
    public function odometerAlerts(): JsonResponse
    {
        try {
            $data = $this->alertsService->getOdometerAlerts();

            return response()->json([
                'success' => true,
                'message' => "تم جلب البيانات بنجاح",
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "فشل جلب البيانات",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET alerts-insurance
     * Insurance policies expiring within 30 days.
     */
    public function insuranceAlerts(): JsonResponse
    {
        try {
            $data = $this->alertsService->getInsuranceAlerts();

            return response()->json([
                'success' => true,
                'message' => "تم جلب البيانات بنجاح",
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "فشل جلب البيانات",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET alerts-inspection
     * Overdue annual inspections.
     */
    public function inspectionAlerts(): JsonResponse
    {
        try {
            $data = $this->alertsService->getInspectionAlerts();

            return response()->json([
                'success' => true,
                'message' => "تم جلب البيانات بنجاح",
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "فشل جلب البيانات",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET alerts-parts
     * Part lifecycle alerts based on installation date and usage.
     */
    public function partsAlerts(): JsonResponse
    {
        try {
            $data = $this->alertsService->getPartsAlerts();

            return response()->json([
                'success' => true,
                'message' => "تم جلب البيانات بنجاح",
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "فشل جلب البيانات",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * PATCH alerts-insurance-renew
     * Updates policy expiry date.
     */
    public function renewInsurance(int $id, Request $request): JsonResponse
    {
        try {
            $data = $this->alertsService->renewInsurance($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => "تم التجديد بنجاح",
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "فشل التجديد",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * PATCH alerts-inspection-complete
     * Logs a new inspection record.
     */
    public function completeInspection(int $id, Request $request): JsonResponse
    {
        try {
            $data = $this->alertsService->completeInspection($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => "تم التسجيل بنجاح",
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "فشل التسجيل",
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
