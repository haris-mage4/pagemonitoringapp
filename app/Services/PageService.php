<?php

namespace App\Services;

use App\Models\Page;
use App\Models\PageError;
use App\Models\Scan;
use App\Models\Website;
use Illuminate\Database\Eloquent\Collection;

class PageService
{
    public function listForWebsite(Website $website): Collection
    {
        return $website->pages()->get();
    }

    /**
     * @return array<string, mixed>
     */
    public function details(Page $page): array
    {
        $page->load('website');

        $scanHistory = Scan::where('page_id', $page->id)
            ->with('scanResult')
            ->latest('finished_at')
            ->get();

        $latestScan = $scanHistory->first();

        $performanceHistory = $scanHistory
            ->reverse()
            ->values()
            ->map(fn (Scan $scan) => [
                'scanned_at' => $scan->created_at,
                'performance' => $scan->scanResult?->performance,
            ])
            ->filter(fn ($row) => $row['performance'] !== null)
            ->values();

        return [
            'page' => $page,
            'latest_scan' => $latestScan,
            'scan_history' => $scanHistory,
            'performance_history' => $performanceHistory,
            'raw_report' => $latestScan?->scanResult?->raw_json,
            'page_errors' => PageError::where('page_id', $page->id)->latest('last_seen_at')->get(),
        ];
    }

    public function create(Website $website, array $data): Page
    {
        return $website->pages()->create($data);
    }

    public function update(Page $page, array $data): Page
    {
        $page->update($data);

        return $page;
    }

    public function delete(Page $page): void
    {
        $page->delete();
    }

    public function setEnabled(Page $page, bool $enabled): Page
    {
        $page->update(['enabled' => $enabled]);

        return $page;
    }
}
