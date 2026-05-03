<?php

/**
 * @file AuditLogRepository.php
 * @description مستودع سجل المراجعة — يكتب فقط (immutable) إلى جدول audit_logs
 * @module LoggingAudit
 * @author Team Leader (Khalid)
 */

namespace App\Modules\LoggingAudit\Repositories;

use App\Modules\Shared\Repositories\BaseRepository;
use App\Modules\LoggingAudit\Models\AuditLog;

class AuditLogRepository extends BaseRepository
{
    /** Fields masked before persisting to protect PII */
    protected array $piiFields = [
        'password',
        'password_confirmation',
        'phone',
        'phone_no',
        'email',
        'remember_token',
        'fcm_token',
        'token',
        'secret',
    ];

    public function __construct(AuditLog $model)
    {
        parent::__construct($model);
    }

    /**
     * إنشاء سجل مراجعة جديد (Write-only / Immutable)
     */
    public function writeLog(array $data): AuditLog
    {
        // Mask PII before persisting
        $data['before_state'] = $this->maskPII($data['before_state'] ?? null);
        $data['after_state'] = $this->maskPII($data['after_state'] ?? null);
        $data['created_at'] = now();

        return $this->model->create($data);
    }

    /**
     * جلب سجلات كيان معين
     */
    public function getForEntity(string $entityType, int $entityId, int $perPage = 20)
    {
        return $this->model
            ->forEntity($entityType, $entityId)
            ->latest('created_at')
            ->paginate($perPage);
    }

    /**
     * جلب سجلات مستخدم معين
     */
    public function getForUser(int $userId, int $perPage = 20)
    {
        return $this->model
            ->forUser($userId)
            ->latest('created_at')
            ->paginate($perPage);
    }

    /**
     * بحث في سجلات المراجعة بفلاترة متعددة
     */
    public function searchLogs(array $filters, int $perPage = 20)
    {
        $query = $this->model->newQuery();

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (!empty($filters['entity_type'])) {
            $query->where('entity_type', $filters['entity_type']);
        }
        if (!empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // التعديل الجديد: دعم مربع البحث (بالـ ID أو داخل محتوى الـ JSON)
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('audit_id', $search)
                    ->orWhere('before_state', 'LIKE', "%{$search}%")
                    ->orWhere('after_state', 'LIKE', "%{$search}%");
            });
        }

        return $query->latest('created_at')->paginate($perPage);
    }

    /**
     * إخفاء حقول PII من المصفوفة قبل الحفظ
     */
    protected function maskPII(?array $data): ?array
    {
        if ($data === null) {
            return null;
        }
        foreach ($this->piiFields as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = '***MASKED***';
            }
        }
        return $data;
    }
}
