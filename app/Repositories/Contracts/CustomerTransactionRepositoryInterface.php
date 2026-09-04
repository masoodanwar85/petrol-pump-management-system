<?php

namespace App\Repositories\Contracts;

use App\Models\CustomerTransaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

/**
 * @extends BaseRepositoryInterface<CustomerTransaction>
 */
interface CustomerTransactionRepositoryInterface extends BaseRepositoryInterface
{
    public function paginateForCustomer(int $customerId, int $perPage = 15): LengthAwarePaginator;

    public function creditSalesAmountBetween(Carbon $from, Carbon $to): string;
}
