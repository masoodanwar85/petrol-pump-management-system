<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Shift\EndShiftRequest;
use App\Http\Requests\Api\V1\Shift\StartShiftRequest;
use App\Http\Resources\Api\V1\ShiftResource;
use App\Models\Shift;
use App\Repositories\Contracts\ShiftRepositoryInterface;
use App\Services\ShiftService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class ShiftController extends Controller
{
    public function __construct(
        private readonly ShiftRepositoryInterface $shifts,
        private readonly ShiftService $service,
    ) {}

    public function index(): JsonResponse
    {
        return ApiResponse::paginated(
            $this->shifts->paginate(perPage: 15, with: ['user', 'closedByUser']),
            ShiftResource::class,
        );
    }

    public function show(Shift $shift): JsonResponse
    {
        $shift->load([
            'user',
            'closedByUser',
            'meterReadings.nozzle.pump',
            'meterReadings.nozzle.fuelType',
            'sales.fuelType',
            'sales.nozzle',
        ]);

        return ApiResponse::success(new ShiftResource($shift));
    }

    public function current(): JsonResponse
    {
        $shift = $this->shifts->currentOpen();

        if (! $shift) {
            return ApiResponse::success(null, 'No open shift.');
        }

        return ApiResponse::success(new ShiftResource($shift));
    }

    public function start(StartShiftRequest $request): JsonResponse
    {
        $shift = $this->service->start($request->user(), $request->validated());

        return ApiResponse::created(new ShiftResource($shift), 'Shift started.');
    }

    public function end(EndShiftRequest $request, Shift $shift): JsonResponse
    {
        $shift = $this->service->end($shift, $request->user(), $request->validated());

        return ApiResponse::success(new ShiftResource($shift), 'Shift closed. Sales have been generated.');
    }
}
