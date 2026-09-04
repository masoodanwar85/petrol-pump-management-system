<?php

namespace App\Repositories\Contracts;

use App\Models\Sale;
use Illuminate\Support\Carbon;

/**
 * @extends BaseRepositoryInterface<Sale>
 */
interface SaleRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @return array{amount: string, liters: string, cost: string, profit: string}
     */
    public function totalsBetween(Carbon $from, Carbon $to): array;

    /**
     * Station sales rolled up by fuel type (petrol / diesel).
     *
     * @return array<int, array{fuel_type_id: int, fuel_type: string, code: string, liters_sold: string, total_amount: string, profit: string}>
     */
    public function combinedByFuelTypeBetween(Carbon $from, Carbon $to): array;

    /**
     * Isolated sales per nozzle.
     *
     * @return array<int, array{nozzle_id: int, label: string, pump: string|null, side: string, fuel_type_id: int, fuel_type: string|null, liters_sold: string, total_amount: string, profit: string}>
     */
    public function isolatedByNozzleBetween(Carbon $from, Carbon $to): array;
}
