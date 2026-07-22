<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWebsiteRequest;
use App\Http\Requests\UpdateWebsiteRequest;
use App\Jobs\ScanWebsiteJob;
use App\Models\Website;
use App\Services\WebsiteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebsiteController extends Controller
{
    public function __construct(private readonly WebsiteService $websites) {}

    public function index(): JsonResponse
    {
        return response()->json($this->websites->list());
    }

    public function store(StoreWebsiteRequest $request): JsonResponse
    {
        return response()->json($this->websites->create($request->validated()), 201);
    }

    public function show(Website $website): JsonResponse
    {
        return response()->json($this->websites->details($website));
    }

    public function update(UpdateWebsiteRequest $request, Website $website): JsonResponse
    {
        return response()->json($this->websites->update($website, $request->validated()));
    }

    public function destroy(Website $website): JsonResponse
    {
        $this->websites->delete($website);

        return response()->json(null, 204);
    }

    public function setEnabled(Request $request, Website $website): JsonResponse
    {
        $request->validate(['enabled' => ['required', 'boolean']]);

        return response()->json(
            $this->websites->setEnabled($website, $request->boolean('enabled'))
        );
    }

    public function scan(Website $website): JsonResponse
    {
        ScanWebsiteJob::dispatch($website, 'manual');

        return response()->json(['status' => 'accepted'], 202);
    }
}
