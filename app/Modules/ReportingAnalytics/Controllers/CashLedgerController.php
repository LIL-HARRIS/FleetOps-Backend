<?php

namespace App\Modules\ReportingAnalytics\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ReportingAnalytics\Services\CashLedgerService;
use App\Modules\ReportingAnalytics\Requests\SubmitReconciliationRequest;
use Illuminate\Http\JsonResponse;

class CashLedgerController extends Controller
{
    protected CashLedgerService $cashLedgerService;

    public function __construct(CashLedgerService $cashLedgerService)
    {
        $this->cashLedgerService = $cashLedgerService;
    }

    /**
     * Get reconciliation summary for a route.
     * GET /api/v1/analytics/reconciliation/summary/{routeId}
     *
     * @param int $routeId
     * @return JsonResponse
     */
    public function getSummary(int $routeId): JsonResponse
    {
        try {
            $data = $this->cashLedgerService->getReconciliationSummary($routeId);
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Submit COD reconciliation.
     * POST /api/v1/analytics/reconciliation/submit
     *
     * @param SubmitReconciliationRequest $request
     * @return JsonResponse
     */
    public function submitReconciliation(SubmitReconciliationRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $result = $this->cashLedgerService->submitReconciliation($validated);
            
            return response()->json($result);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
