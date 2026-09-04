<?php

namespace App\Services;

use App\Enums\CustomerTransactionType;
use App\Exceptions\BusinessException;
use App\Models\Customer;
use App\Models\CustomerTransaction;
use App\Models\Shift;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Repositories\Contracts\CustomerTransactionRepositoryInterface;
use App\Repositories\Contracts\FuelRateRepositoryInterface;
use App\Repositories\Contracts\ShiftRepositoryInterface;
use App\Support\Decimal;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customers,
        private readonly CustomerTransactionRepositoryInterface $transactions,
        private readonly ShiftRepositoryInterface $shifts,
        private readonly FuelRateRepositoryInterface $rates,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Customer
    {
        return $this->customers->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Customer $customer, array $data): Customer
    {
        return $this->customers->update($customer, $data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function recordCreditSale(Customer $customer, array $data): CustomerTransaction
    {
        if (! $customer->is_active) {
            throw new BusinessException('This customer is inactive.');
        }

        $shift = $this->resolveOpenShift($data['shift_id'] ?? null);
        $liters = Decimal::of($data['liters']);
        $rate = $this->rates->activeAt((int) $data['fuel_type_id'], $shift->start_time);

        if (! $rate) {
            throw new BusinessException('No active fuel rate is available for this credit sale.');
        }

        $amount = isset($data['amount'])
            ? Decimal::money($data['amount'])
            : Decimal::money(Decimal::multiply($liters, $rate->rate, 4));

        $this->assertWithinCreditLimit($customer, $amount);

        return DB::transaction(function () use ($customer, $data, $shift, $liters, $amount): CustomerTransaction {
            return $this->transactions->create([
                'customer_id' => $customer->id,
                'type' => CustomerTransactionType::Sale,
                'amount' => $amount,
                'date' => $data['date'] ?? Carbon::now()->toDateString(),
                'shift_id' => $shift->id,
                'fuel_type_id' => $data['fuel_type_id'],
                'liters' => $liters,
                'notes' => $data['notes'] ?? null,
                'created_by' => Auth::id(),
            ])->load(['customer', 'fuelType', 'shift']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function recordPayment(Customer $customer, array $data): CustomerTransaction
    {
        $amount = Decimal::money($data['amount']);

        if (Decimal::compare($amount, '0', 2) <= 0) {
            throw new BusinessException('Payment amount must be greater than zero.');
        }

        $outstanding = $customer->outstandingBalance();

        if (Decimal::compare($amount, $outstanding, 2) > 0) {
            throw new BusinessException("Payment ({$amount}) exceeds outstanding balance ({$outstanding}).");
        }

        $shift = isset($data['shift_id'])
            ? $this->resolveOpenShift($data['shift_id'])
            : $this->shifts->currentOpen();

        return $this->transactions->create([
            'customer_id' => $customer->id,
            'type' => CustomerTransactionType::Payment,
            'amount' => $amount,
            'date' => $data['date'] ?? Carbon::now()->toDateString(),
            'shift_id' => $shift?->id,
            'notes' => $data['notes'] ?? null,
            'created_by' => Auth::id(),
        ])->load(['customer', 'shift']);
    }

    private function resolveOpenShift(?int $shiftId): Shift
    {
        $shift = $shiftId
            ? $this->shifts->findOrFail($shiftId)
            : $this->shifts->currentOpen();

        if (! $shift) {
            throw new BusinessException('An open shift is required to record a credit sale.');
        }

        if (! $shift->isOpen()) {
            throw new BusinessException('Credit sales can only be recorded against an open shift.');
        }

        return $shift;
    }

    private function assertWithinCreditLimit(Customer $customer, string $amount): void
    {
        $nextOutstanding = Decimal::add($customer->outstandingBalance(), $amount, 2);

        if (Decimal::compare($nextOutstanding, $customer->credit_limit, 2) > 0) {
            throw new BusinessException(
                "This sale would exceed the customer's credit limit of {$customer->credit_limit}. Outstanding would become {$nextOutstanding}.",
            );
        }
    }
}
