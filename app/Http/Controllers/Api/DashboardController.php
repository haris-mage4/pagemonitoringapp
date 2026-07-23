<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TrendRequest;
use App\Services\MetricsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function __construct(private readonly MetricsService $metrics) {}

    public function summary(Request $request): JsonResponse
    {
        return response()->json($this->metrics->dashboardSummary($request->user()->id));
    }

    public function trend(TrendRequest $request, string $metric): JsonResponse
    {
        $data = $this->metrics->trend(
            $request->user()->id,
            $metric,
            $request->validated('range'),
            $request->filled('from') ? Carbon::parse($request->validated('from')) : null,
            $request->filled('to') ? Carbon::parse($request->validated('to')) : null,
        );

        return response()->json($data);
    }
}
