<?php

namespace App\Repositories\Eloquent;

use App\Models\Expense;
use App\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Support\Decimal;
use Illuminate\Support\Carbon;

class ExpenseRepository extends BaseRepository implements ExpenseRepositoryInterface
{
    public function __construct(Expense $model)
    {
        parent::__construct($model);
    }

    public function totalBetween(Carbon $from, Carbon $to): string
    {
        $total = $this->query()
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');

        return Decimal::money((string) $total);
    }
}
