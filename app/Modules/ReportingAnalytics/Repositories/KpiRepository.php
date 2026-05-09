<?php

/**
 * @file: KpiRepository.php
 * @description: مستودع بيانات مؤشرات الأداء - Reporting & Analytics Service
 * @module: ReportingAnalytics
 * @author: Team Leader (Khalid)
 */

namespace App\Modules\ReportingAnalytics\Repositories;

use App\Modules\Shared\Repositories\BaseRepository;
use App\Modules\ReportingAnalytics\Models\KpiSnapshot;

class KpiRepository extends BaseRepository
{
    public function __construct(KpiSnapshot $model)
    {
        parent::__construct($model);
    }

    public function getForPeriod(string $periodType, string $startDate, string $endDate)
    {
        return $this->model->where('period_type', $periodType)
            ->whereBetween('period_start', [$startDate, $endDate])->get();
    }

    public function getLatestByMetric(string $metricName)
    {
        return $this->model->where('metric_name', $metricName)->latest('period_start')->first();
    }
}
