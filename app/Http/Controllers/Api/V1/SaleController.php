<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\SaleResource;
use App\Repositories\Contracts\SaleRepositoryInterface;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class SaleController extends Controller
{
    public function __construct(
        private readonly SaleRepositoryInterface $sales,
    ) {}

    public function index(): JsonResponse
    {
        return ApiResponse::paginated(
            $this->sales->paginate(perPage: 30, with: ['fuelType', 'nozzle.pump', 'shift']),
            SaleResource::class,
        );
    }
}
