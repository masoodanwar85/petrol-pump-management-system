<?php

namespace App\Repositories\Contracts;

use App\Models\Expense;
use Illuminate\Support\Carbon;

/**
 * @extends BaseRepositoryInterface<Expense>
 */
interface ExpenseRepositoryInterface extends BaseRepositoryInterface
{
    public function totalBetween(Carbon $from, Carbon $to): string;
}
