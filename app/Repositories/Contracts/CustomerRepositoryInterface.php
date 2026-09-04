<?php

namespace App\Repositories\Contracts;

use App\Models\Customer;

/**
 * @extends BaseRepositoryInterface<Customer>
 */
interface CustomerRepositoryInterface extends BaseRepositoryInterface
{
    public function outstandingTotal(): string;
}
