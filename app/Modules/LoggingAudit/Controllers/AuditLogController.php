<?php

/**
 * @file: AuditLogController.php
 * @description: متحكم سجل المراجعة - قراءة وتصدير السجلات (LA-01)
 * @module: LoggingAudit
 * @author: Team Leader (Khalid)
 */

namespace App\Modules\LoggingAudit\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\LoggingAudit\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * البحث في سجلات المراجعة
     * GET /api/v1/audit/logs
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // استقبال كل الفلاتر اللي في الشاشة
            $filters = $request->only([
                'user_id',
                'entity_type',
                'action',
                'date_from',
                'date_to',
                'search'
            ]);
            $perPage = (int) $request->get('per_page', 15);

            $logs = $this->auditService->searchLogs($filters, $perPage);

            return response()->json([
                'success' => true,
                'data' => $logs
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * سجل مراجعة كيان معين
     * GET /api/v1/audit/entity/{entityType}/{entityId}
     */
    public function entityTrail(string $entityType, int $entityId, Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->get('per_page', 20);
            $trail = $this->auditService->getEntityAuditTrail($entityType, $entityId, $perPage);

            return response()->json([
                'success' => true,
                'data' => $trail
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * تصدير سجلات المراجعة إلى CSV
     * GET /api/v1/audit/logs/export
     */
    public function export(Request $request)
    {
        // نجلب السجلات (بدون Pagination لغرض التصدير - كحد أقصى 1000 سجل)
        $filters = $request->only(['user_id', 'entity_type', 'action', 'date_from', 'date_to']);
        $logs = $this->auditService->searchLogs($filters, 1000);

        // إنشاء محتوى CSV مبسط
        $csvData = "Log ID,User ID,Action,Entity Type,Entity ID,Date\n";
        foreach ($logs as $log) {
            $csvData .= "{$log->audit_id},{$log->user_id},{$log->action},{$log->entity_type},{$log->entity_id},{$log->created_at}\n";
        }

        // إرجاع الملف كتحميل
        return response($csvData)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="audit_logs.csv"');
    }
}
