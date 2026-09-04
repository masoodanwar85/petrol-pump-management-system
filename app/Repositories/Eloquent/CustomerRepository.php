<?php

namespace App\Repositories\Eloquent;

use App\Enums\CustomerTransactionType;
use App\Models\Customer;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Support\Decimal;
use Illuminate\Support\Facades\DB;

class CustomerRepository extends BaseRepository implements CustomerRepositoryInterface
{
    public function __construct(Customer $model)
    {
        parent::__construct($model);
    }

    public function outstandingTotal(): string
    {
        $row = DB::table('customer_transactions')
            ->whereNull('deleted_at')
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN type = ? THEN amount ELSE 0 END), 0) as sales',
                [CustomerTransactionType::Sale->value],
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN type = ? THEN amount ELSE 0 END), 0) as payments',
                [CustomerTransactionType::Payment->value],
            )
            ->first();

        return Decimal::subtract((string) ($row->sales ?? 0), (string) ($row->payments ?? 0), 2);
    }
}
