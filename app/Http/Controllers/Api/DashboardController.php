<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MetricsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private readonly MetricsService $metrics) {}

    public function summary(Request $request): JsonResponse
    {
        return response()->json($this->metrics->dashboardSummary($request->user()->id));
    }

    public function trend(Request $request, string $metric): JsonResponse
    {
        return response()->json($this->metrics->trend($request->user()->id, $metric));
    }
}
