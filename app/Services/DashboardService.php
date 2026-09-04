<?php

namespace App\Services;

use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Repositories\Contracts\CustomerTransactionRepositoryInterface;
use App\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Repositories\Contracts\ProductSaleRepositoryInterface;
use App\Repositories\Contracts\SaleRepositoryInterface;
use App\Repositories\Contracts\ShiftRepositoryInterface;
use App\Repositories\Contracts\TankRepositoryInterface;
use App\Repositories\Contracts\TankTransactionRepositoryInterface;
use App\Support\Decimal;
use Illuminate\Support\Carbon;

class DashboardService
{
    public function __construct(
        private readonly SaleRepositoryInterface $sales,
        private readonly ProductSaleRepositoryInterface $productSales,
        private readonly TankRepositoryInterface $tanks,
        private readonly TankTransactionRepositoryInterface $tankTransactions,
        private readonly CustomerRepositoryInterface $customers,
        private readonly CustomerTransactionRepositoryInterface $customerTransactions,
        private readonly ExpenseRepositoryInterface $expenses,
        private readonly ShiftRepositoryInterface $shifts,
        private readonly AlertService $alerts,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function today(): array
    {
        $from = Carbon::today()->startOfDay();
        $to = Carbon::today()->endOfDay();

        $fuel = $this->sales->totalsBetween($from, $to);
        $products = $this->productSales->totalsBetween($from, $to);
        $purchaseCost = $this->tankTransactions->purchaseCostBetween(
            $from->toDateString(),
            $to->toDateString(),
        );
        $expenses = $this->expenses->totalBetween($from, $to);
        $creditSales = $this->customerTransactions->creditSalesAmountBetween($from, $to);

        $fuelProfit = $fuel['profit'];
        $productProfit = $products['profit'];
        $profitToday = Decimal::subtract(
            Decimal::add($fuelProfit, $productProfit, 2),
            $expenses,
            2,
        );

        return [
            'date' => $from->toDateString(),
            'today_sales_amount' => $fuel['amount'],
            'today_liters' => $fuel['liters'],
            'sales_by_fuel_type' => $this->sales->combinedByFuelTypeBetween($from, $to),
            'sales_by_nozzle' => $this->sales->isolatedByNozzleBetween($from, $to),
            'today_product_sales_amount' => $products['amount'],
            'today_credit_sales_amount' => $creditSales,
            'today_expenses' => $expenses,
            'today_purchase_cost' => $purchaseCost,
            'profit_today' => $profitToday,
            'fuel_profit_today' => $fuelProfit,
            'product_profit_today' => $productProfit,
            'credit_outstanding' => $this->customers->outstandingTotal(),
            'open_shift' => $this->shifts->currentOpen(),
            'tank_levels' => $this->tanks->all(['fuelType'])->map(fn ($tank) => [
                'id' => $tank->id,
                'name' => $tank->name,
                'fuel_type' => $tank->fuelType?->name,
                'capacity' => $tank->capacity,
                'current_stock' => $tank->current_stock,
                'fill_percentage' => $tank->fillPercentage(),
                'low_level_threshold' => $tank->low_level_threshold,
                'is_low' => $tank->isBelowThreshold(),
            ])->values(),
            'alerts' => $this->alerts->active(),
        ];
    }
}
