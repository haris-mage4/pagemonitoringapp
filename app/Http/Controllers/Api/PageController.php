<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePageRequest;
use App\Http\Requests\UpdatePageRequest;
use App\Models\Page;
use App\Models\Website;
use App\Services\PageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function __construct(private readonly PageService $pages) {}

    public function index(Website $website): JsonResponse
    {
        return response()->json($this->pages->listForWebsite($website));
    }

    public function store(StorePageRequest $request, Website $website): JsonResponse
    {
        return response()->json($this->pages->create($website, $request->validated()), 201);
    }

    public function show(Page $page): JsonResponse
    {
        return response()->json($this->pages->find($page));
    }

    public function update(UpdatePageRequest $request, Page $page): JsonResponse
    {
        return response()->json($this->pages->update($page, $request->validated()));
    }

    public function destroy(Page $page): JsonResponse
    {
        $this->pages->delete($page);

        return response()->json(null, 204);
    }

    public function setEnabled(Request $request, Page $page): JsonResponse
    {
        $request->validate(['enabled' => ['required', 'boolean']]);

        return response()->json(
            $this->pages->setEnabled($page, $request->boolean('enabled'))
        );
    }
}
