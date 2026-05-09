<?php

namespace App\Modules\ReportingAnalytics\Services;

use App\Modules\ReportingAnalytics\Repositories\CashLedgerRepository;
use App\Modules\ReportingAnalytics\Models\CashLedger;
use Illuminate\Support\Facades\DB;
use Exception;

class CashLedgerService
{
    protected CashLedgerRepository $repository;

    public function __construct(CashLedgerRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get COD reconciliation summary and data for a route.
     *
     * @param int $routeId
     * @return array
     */
    public function getReconciliationSummary(int $routeId): array
    {
        $orders = $this->repository->getReconciliationDataForRoute($routeId);

        $totalExpectedAmount = 0.0;
        $expectedDeliveryCount = 0;
        $totalCollectedAmount = 0.0;
        $validatedCount = 0;

        $ordersList = [];

        foreach ($orders as $order) {
            if (strcasecmp($order->Payment_method, 'cash') === 0) {
                $expectedAmount = (float) $order->Price;
                $totalExpectedAmount += $expectedAmount;
                $expectedDeliveryCount++;

                $collectedAmount = 0.0;
                if ($order->cashLedgerEntry) {
                    $collectedAmount = (float) $order->cashLedgerEntry->amount_collected;
                    $totalCollectedAmount += $collectedAmount;
                    $validatedCount++;
                }

                $customerName = 'Unknown';
                if ($order->customer && $order->customer->user) {
                    $customerName = $order->customer->user->name;
                }

                $ordersList[] = [
                    'order_id' => $order->OrderID,
                    'customer_name' => $customerName,
                    'expected_amount' => $expectedAmount,
                    'collected_amount' => $collectedAmount,
                ];
            }
        }

        $discrepancy = $totalExpectedAmount - $totalCollectedAmount;

        return [
            'summary' => [
                'total_expected' => $totalExpectedAmount,
                'expected_delivery_count' => $expectedDeliveryCount,
                'total_collected' => $totalCollectedAmount,
                'validated_count' => $validatedCount,
                'discrepancy' => $discrepancy,
            ],
            'orders' => $ordersList,
        ];
    }

    /**
     * Submit reconciliation data.
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function submitReconciliation(array $data): array
    {
        DB::beginTransaction();
        try {
            $routeId = $data['route_id'];
            $collectedAmounts = $data['collected_amounts'] ?? [];
            
            // Loop through each submitted collected amount and create/update cash ledger
            foreach ($collectedAmounts as $oId => $amount) {
                CashLedger::updateOrCreate(
                    ['order_id' => $oId],
                    [
                        'amount_collected' => $amount,
                        'payment_status' => 'collected',
                        'handed_over_to_company' => true,
                        'payment_method' => 'COD',
                        'transaction_ts' => now(),
                    ]
                );
            }

            // Note: Proof files and notes are accessible via $data['proof_file'] and $data['notes'].
            // Depending on the existing structure, they would be saved and stored in a DiscrepancyReport model.

            DB::commit();

            return [
                'success' => true,
                'message' => 'Reconciliation submitted successfully.',
            ];
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
