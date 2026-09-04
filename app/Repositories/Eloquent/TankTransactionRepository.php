<?php

namespace App\Repositories\Eloquent;

use App\Enums\TankTransactionType;
use App\Models\TankTransaction;
use App\Repositories\Contracts\TankTransactionRepositoryInterface;
use App\Support\Decimal;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TankTransactionRepository extends BaseRepository implements TankTransactionRepositoryInterface
{
    public function __construct(TankTransaction $model)
    {
        parent::__construct($model);
    }

    public function paginateForTank(int $tankId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->where('tank_id', $tankId)
            ->latest('date')
            ->latest('id')
            ->paginate($perPage);
    }

    public function purchaseCostBetween(string $from, string $to): string
    {
        $total = $this->query()
            ->where('type', TankTransactionType::Purchase)
            ->whereBetween('date', [$from, $to])
            ->sum('total_cost');

        return Decimal::money((string) $total);
    }
}
