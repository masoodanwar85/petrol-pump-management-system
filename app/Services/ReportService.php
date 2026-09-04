<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Shift;
use App\Repositories\Contracts\CustomerTransactionRepositoryInterface;
use App\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Repositories\Contracts\ProductSaleRepositoryInterface;
use App\Repositories\Contracts\SaleRepositoryInterface;
use App\Repositories\Contracts\TankTransactionRepositoryInterface;
use App\Support\Decimal;
use Illuminate\Support\Carbon;

class ReportService
{
    public function __construct(
        private readonly SaleRepositoryInterface $sales,
        private readonly ProductSaleRepositoryInterface $productSales,
        private readonly TankTransactionRepositoryInterface $tankTransactions,
        private readonly ExpenseRepositoryInterface $expenses,
        private readonly CustomerTransactionRepositoryInterface $customerTransactions,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function daily(?string $date = null): array
    {
        $day = $date ? Carbon::parse($date) : Carbon::today();
        $from = $day->copy()->startOfDay();
        $to = $day->copy()->endOfDay();

        $fuel = $this->sales->totalsBetween($from, $to);
        $products = $this->productSales->totalsBetween($from, $to);
        $purchaseCost = $this->tankTransactions->purchaseCostBetween($from->toDateString(), $to->toDateString());
        $expenseTotal = $this->expenses->totalBetween($from, $to);
        $creditSales = $this->customerTransactions->creditSalesAmountBetween($from, $to);

        $shifts = Shift::query()
            ->with(['user', 'sales.nozzle.pump', 'sales.fuelType', 'meterReadings.nozzle'])
            ->whereBetween('start_time', [$from, $to])
            ->orderBy('start_time')
            ->get();

        $salesByFuel = Sale::query()
            ->with('fuelType')
            ->whereHas('shift', fn ($query) => $query->whereBetween('start_time', [$from, $to]))
            ->get()
            ->groupBy('fuel_type_id')
            ->map(function ($rows) {
                return [
                    'fuel_type' => $rows->first()->fuelType?->name,
                    'liters_sold' => Decimal::of((string) $rows->sum('liters_sold')),
                    'total_amount' => Decimal::money((string) $rows->sum('total_amount')),
                    'total_cost' => Decimal::money((string) $rows->sum('total_cost')),
                    'profit' => Decimal::money((string) $rows->sum('profit')),
                ];
            })
            ->values();

        return [
            'date' => $day->toDateString(),
            'fuel' => [
                'sales_amount' => $fuel['amount'],
                'liters_sold' => $fuel['liters'],
                'cogs' => $fuel['cost'],
                'profit' => $fuel['profit'],
                'purchase_cost' => $purchaseCost,
                'profit_vs_purchases' => Decimal::subtract($fuel['amount'], $purchaseCost, 2),
                'by_fuel_type' => $salesByFuel,
            ],
            'products' => [
                'sales_amount' => $products['amount'],
                'cost' => $products['cost'],
                'profit' => $products['profit'],
            ],
            'credit_sales_amount' => $creditSales,
            'expected_cash' => Decimal::subtract($fuel['amount'], $creditSales, 2),
            'expenses' => $expenseTotal,
            'net_profit' => Decimal::subtract(
                Decimal::add($fuel['profit'], $products['profit'], 2),
                $expenseTotal,
                2,
            ),
            'shifts' => $shifts,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function profit(?string $fromDate = null, ?string $toDate = null): array
    {
        $from = $fromDate ? Carbon::parse($fromDate)->startOfDay() : Carbon::today()->startOfDay();
        $to = $toDate ? Carbon::parse($toDate)->endOfDay() : Carbon::today()->endOfDay();

        $fuel = $this->sales->totalsBetween($from, $to);
        $products = $this->productSales->totalsBetween($from, $to);
        $purchaseCost = $this->tankTransactions->purchaseCostBetween($from->toDateString(), $to->toDateString());
        $expenseTotal = $this->expenses->totalBetween($from, $to);

        return [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'fuel_profit' => [
                'sales' => $fuel['amount'],
                'cogs_weighted_average' => $fuel['cost'],
                'profit_weighted_average' => $fuel['profit'],
                'purchase_cost' => $purchaseCost,
                'profit_vs_purchases' => Decimal::subtract($fuel['amount'], $purchaseCost, 2),
            ],
            'product_profit' => [
                'sales' => $products['amount'],
                'cost' => $products['cost'],
                'profit' => $products['profit'],
            ],
            'expenses' => $expenseTotal,
            'net_profit' => Decimal::subtract(
                Decimal::add($fuel['profit'], $products['profit'], 2),
                $expenseTotal,
                2,
            ),
        ];
    }
}
