<?php

/**
 * @file: SystemLogController.php
 * @description: متحكم السجلات النظامية - عرض وبحث (LA-02)
 * @module: LoggingAudit
 * @author: Team Leader (Khalid)
 */

namespace App\Modules\LoggingAudit\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\LoggingAudit\Repositories\SystemLogRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SystemLogController extends Controller
{
    protected SystemLogRepository $systemLogRepository;

    public function __construct(SystemLogRepository $systemLogRepository)
    {
        $this->systemLogRepository = $systemLogRepository;
    }

    /**
     * جلب السجلات النظامية مع فلتر
     * GET /api/v1/audit/system-logs
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->get('per_page', 50);
            $filters = $request->only(['level', 'channel']);

            // جلب البيانات من الـ Repository
            $logs = $this->systemLogRepository->search($filters)->latest('created_at')->paginate($perPage);

            return response()->json(['success' => true, 'data' => $logs], 200);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * جلب الأخطاء الحرجة فقط
     * GET /api/v1/audit/system-logs/errors
     */
    public function errors(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->get('per_page', 50);
            $logs = $this->systemLogRepository->getErrors($perPage);
            return response()->json(['success' => true, 'data' => $logs], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * جلب سجلات قناة معينة
     * GET /api/v1/audit/system-logs/channel/{channel}
     */
    public function byChannel(string $channel, Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->get('per_page', 50);
            $logs = $this->systemLogRepository->getByChannel($channel, $perPage);
            return response()->json(['success' => true, 'data' => $logs], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * إحصاءات السجلات (عدد لكل level/channel)
     * GET /api/v1/audit/system-logs/stats
     */
    public function stats(): JsonResponse
    {
        try {
            // إحصائيات مبسطة لمعرفة حجم الأخطاء مقابل كل العمليات
            $stats = [
                'total_logs' => $this->systemLogRepository->count(),
                'total_errors' => $this->systemLogRepository->getModel()->whereIn('level', ['error', 'critical'])->count(),
                'security_events' => $this->systemLogRepository->getModel()->where('channel', 'security')->count(),
            ];

            return response()->json(['success' => true, 'data' => $stats], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
