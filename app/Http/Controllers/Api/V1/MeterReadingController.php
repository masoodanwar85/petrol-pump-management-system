<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\BusinessException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\MeterReading\StoreClosingReadingRequest;
use App\Http\Requests\Api\V1\MeterReading\StoreOpeningReadingRequest;
use App\Http\Resources\Api\V1\MeterReadingResource;
use App\Models\Shift;
use App\Repositories\Contracts\MeterReadingRepositoryInterface;
use App\Repositories\Contracts\ShiftRepositoryInterface;
use App\Services\MeterReadingService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class MeterReadingController extends Controller
{
    public function __construct(
        private readonly MeterReadingRepositoryInterface $readings,
        private readonly ShiftRepositoryInterface $shifts,
        private readonly MeterReadingService $service,
    ) {}

    public function index(): JsonResponse
    {
        $shiftId = request()->integer('shift_id');

        if ($shiftId) {
            return ApiResponse::success(
                MeterReadingResource::collection($this->readings->forShift($shiftId)),
            );
        }

        return ApiResponse::paginated(
            $this->readings->paginate(perPage: 30, with: ['nozzle.pump', 'nozzle.fuelType', 'shift']),
            MeterReadingResource::class,
        );
    }

    public function opening(StoreOpeningReadingRequest $request): JsonResponse
    {
        $shift = $this->resolveShift($request->validated('shift_id'));
        $reading = $this->service->recordSingleOpening($shift, $request->validated());

        return ApiResponse::success(new MeterReadingResource($reading), 'Opening reading recorded.');
    }

    public function closing(StoreClosingReadingRequest $request): JsonResponse
    {
        $shift = $this->resolveShift($request->validated('shift_id'));
        $reading = $this->service->recordSingleClosing($shift, $request->validated());

        return ApiResponse::success(new MeterReadingResource($reading), 'Closing reading recorded.');
    }

    private function resolveShift(?int $shiftId): Shift
    {
        $shift = $shiftId
            ? $this->shifts->findOrFail($shiftId)
            : $this->shifts->currentOpen();

        if (! $shift) {
            throw new BusinessException('No open shift found. Start a shift first.');
        }

        return $shift;
    }
}
