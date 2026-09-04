<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Expense\StoreExpenseRequest;
use App\Http\Resources\Api\V1\ExpenseResource;
use App\Models\Expense;
use App\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Services\ExpenseService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class ExpenseController extends Controller
{
    public function __construct(
        private readonly ExpenseRepositoryInterface $expenses,
        private readonly ExpenseService $service,
    ) {}

    public function index(): JsonResponse
    {
        return ApiResponse::paginated(
            $this->expenses->paginate(),
            ExpenseResource::class,
        );
    }

    public function store(StoreExpenseRequest $request): JsonResponse
    {
        $expense = $this->service->create($request->validated());

        return ApiResponse::created(new ExpenseResource($expense), 'Expense recorded.');
    }

    public function destroy(Expense $expense): JsonResponse
    {
        $this->expenses->delete($expense);

        return ApiResponse::success(null, 'Expense archived.');
    }
}
