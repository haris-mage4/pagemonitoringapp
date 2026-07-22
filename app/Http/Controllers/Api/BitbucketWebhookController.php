<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BitbucketDeploymentRequest;
use App\Models\Website;
use App\Services\WebhookService;
use Illuminate\Http\JsonResponse;

class BitbucketWebhookController extends Controller
{
    public function __construct(private readonly WebhookService $webhooks) {}

    public function deployment(BitbucketDeploymentRequest $request): JsonResponse
    {
        $website = Website::findOrFail($request->validated('website_id'));

        $this->webhooks->handleDeployment($website);

        return response()->json(['status' => 'accepted'], 202);
    }
}
