<?php

namespace App\Services;

use App\Models\Expense;
use App\Repositories\Contracts\ExpenseRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class ExpenseService
{
    public function __construct(
        private readonly ExpenseRepositoryInterface $expenses,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Expense
    {
        $data['created_by'] = Auth::id();

        return $this->expenses->create($data);
    }
}
