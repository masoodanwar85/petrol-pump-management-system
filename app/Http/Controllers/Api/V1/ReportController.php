<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Report\DailyReportRequest;
use App\Http\Requests\Api\V1\Report\ProfitReportRequest;
use App\Http\Resources\Api\V1\ShiftResource;
use App\Services\ReportService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reports,
    ) {}

    public function daily(DailyReportRequest $request): JsonResponse
    {
        $report = $this->reports->daily($request->validated('date'));
        $report['shifts'] = ShiftResource::collection($report['shifts']);

        return ApiResponse::success($report);
    }

    public function profit(ProfitReportRequest $request): JsonResponse
    {
        return ApiResponse::success(
            $this->reports->profit($request->validated('from'), $request->validated('to')),
        );
    }
}
