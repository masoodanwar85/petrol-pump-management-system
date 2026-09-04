<?php

namespace App\Repositories\Contracts;

use App\Models\TankTransaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * @extends BaseRepositoryInterface<TankTransaction>
 */
interface TankTransactionRepositoryInterface extends BaseRepositoryInterface
{
    public function paginateForTank(int $tankId, int $perPage = 15): LengthAwarePaginator;

    public function purchaseCostBetween(string $from, string $to): string;
}
