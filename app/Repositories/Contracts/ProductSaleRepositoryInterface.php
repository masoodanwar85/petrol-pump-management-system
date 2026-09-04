<?php

namespace App\Repositories\Contracts;

use App\Models\ProductSale;
use Illuminate\Support\Carbon;

/**
 * @extends BaseRepositoryInterface<ProductSale>
 */
interface ProductSaleRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @return array{amount: string, cost: string, profit: string}
     */
    public function totalsBetween(Carbon $from, Carbon $to): array;
}
