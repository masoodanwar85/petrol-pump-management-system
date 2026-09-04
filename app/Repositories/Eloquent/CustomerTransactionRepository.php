<?php

namespace App\Repositories\Eloquent;

use App\Enums\CustomerTransactionType;
use App\Models\CustomerTransaction;
use App\Repositories\Contracts\CustomerTransactionRepositoryInterface;
use App\Support\Decimal;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class CustomerTransactionRepository extends BaseRepository implements CustomerTransactionRepositoryInterface
{
    public function __construct(CustomerTransaction $model)
    {
        parent::__construct($model);
    }

    public function paginateForCustomer(int $customerId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->with(['fuelType', 'shift'])
            ->where('customer_id', $customerId)
            ->latest('date')
            ->latest('id')
            ->paginate($perPage);
    }

    public function creditSalesAmountBetween(Carbon $from, Carbon $to): string
    {
        $total = $this->query()
            ->where('type', CustomerTransactionType::Sale)
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');

        return Decimal::money((string) $total);
    }
}
