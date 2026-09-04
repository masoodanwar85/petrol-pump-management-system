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
}
