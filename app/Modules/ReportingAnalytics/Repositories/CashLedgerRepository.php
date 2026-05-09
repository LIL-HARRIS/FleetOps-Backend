<?php

namespace App\Modules\ReportingAnalytics\Repositories;

use App\Modules\Shared\Repositories\BaseRepository;
use App\Modules\ReportingAnalytics\Models\CashLedger;
use App\Modules\OrderManagement\Models\Order;

class CashLedgerRepository extends BaseRepository
{
    public function __construct(CashLedger $model)
    {
        parent::__construct($model);
    }
    
    /**
     * Get orders for a specific route and their cash ledger entries for COD reconciliation.
     *
     * @param int $routeId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getReconciliationDataForRoute(int $routeId)
    {
        return Order::whereHas('routeStops', function ($query) use ($routeId) {
            $query->where('route_id', $routeId);
        })->with([
            'customer.user', 
            'cashLedgerEntry'
        ])->get();
    }
}
