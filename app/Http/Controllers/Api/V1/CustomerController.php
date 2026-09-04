<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Customer\StoreCreditSaleRequest;
use App\Http\Requests\Api\V1\Customer\StoreCustomerRequest;
use App\Http\Requests\Api\V1\Customer\StorePaymentRequest;
use App\Http\Requests\Api\V1\Customer\UpdateCustomerRequest;
use App\Http\Resources\Api\V1\CustomerResource;
use App\Http\Resources\Api\V1\CustomerTransactionResource;
use App\Models\Customer;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Repositories\Contracts\CustomerTransactionRepositoryInterface;
use App\Services\CustomerService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customers,
        private readonly CustomerTransactionRepositoryInterface $transactions,
        private readonly CustomerService $service,
    ) {}

    public function index(): JsonResponse
    {
        return ApiResponse::paginated(
            $this->customers->paginate(),
            CustomerResource::class,
        );
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = $this->service->create($request->validated());

        return ApiResponse::created(new CustomerResource($customer), 'Customer created.');
    }

    public function show(Customer $customer): JsonResponse
    {
        return ApiResponse::success(new CustomerResource($customer));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): JsonResponse
    {
        $customer = $this->service->update($customer, $request->validated());

        return ApiResponse::success(new CustomerResource($customer), 'Customer updated.');
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $this->customers->delete($customer);

        return ApiResponse::success(null, 'Customer archived.');
    }

    public function ledger(Customer $customer): JsonResponse
    {
        return ApiResponse::paginated(
            $this->transactions->paginateForCustomer($customer->id),
            CustomerTransactionResource::class,
        );
    }

    public function creditSale(StoreCreditSaleRequest $request, Customer $customer): JsonResponse
    {
        $entry = $this->service->recordCreditSale($customer, $request->validated());

        return ApiResponse::created(new CustomerTransactionResource($entry), 'Credit sale recorded.');
    }

    public function payment(StorePaymentRequest $request, Customer $customer): JsonResponse
    {
        $entry = $this->service->recordPayment($customer, $request->validated());

        return ApiResponse::created(new CustomerTransactionResource($entry), 'Payment recorded.');
    }
}
