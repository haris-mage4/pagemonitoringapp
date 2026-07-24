<?php

namespace App\Services;

use App\Models\Page;
use App\Models\PageError;
use App\Models\Scan;
use App\Models\ScanResult;
use App\Models\Website;
use Illuminate\Support\Collection;

class MetricsService
{
    private const TREND_METRICS = ['performance', 'lcp', 'cls', 'tbt'];

    private const TREND_SCAN_LIMIT = 5;

    /**
     * @return array<string, mixed>
     */
    public function dashboardSummary(int $userId): array
    {
        $lastScan = Scan::with('page.website', 'scanResult')
            ->whereHas('page.website', fn ($query) => $query->where('user_id', $userId))
            ->latest('finished_at')
            ->first();

        return [
            'total_websites' => Website::where('user_id', $userId)->count(),
            'last_scan' => $lastScan,
            'failed_scans' => Scan::where('status', 'failed')
                ->whereHas('page.website', fn ($query) => $query->where('user_id', $userId))
                ->count(),
            'average_performance' => round(
                (float) ScanResult::whereNotNull('performance')
                    ->whereHas('scan.page.website', fn ($query) => $query->where('user_id', $userId))
                    ->avg('performance'),
                1
            ),
            'recent_activity' => Scan::with('page.website', 'scanResult')
                ->whereHas('page.website', fn ($query) => $query->where('user_id', $userId))
                ->latest('finished_at')
                ->limit(10)
                ->get(),
            'recent_page_errors' => PageError::with('page.website')
                ->whereHas('page.website', fn ($query) => $query->where('user_id', $userId))
                ->latest('last_seen_at')
                ->limit(10)
                ->get(),
            'pages' => Page::with('website')
                ->withCount('pageErrors')
                ->whereHas('website', fn ($query) => $query->where('user_id', $userId))
                ->get()
                ->map(function (Page $page) {
                    $latestScan = Scan::where('page_id', $page->id)
                        ->with('scanResult')
                        ->latest('finished_at')
                        ->first();

                    $page->setRelation('latestScan', $latestScan);

                    return $page;
                })
                ->values(),
        ];
    }

    /**
     * Last N scans for a metric (count-based, not date-range based), chronological.
     *
     * @return Collection<int, array{scanned_at: string, value: mixed}>
     */
    public function trend(int $userId, string $metric): Collection
    {
        if (! in_array($metric, self::TREND_METRICS, true)) {
            throw new \InvalidArgumentException("Unsupported trend metric [{$metric}].");
        }

        return ScanResult::query()
            ->join('scans', 'scans.id', '=', 'scan_results.scan_id')
            ->join('pages', 'pages.id', '=', 'scans.page_id')
            ->join('websites', 'websites.id', '=', 'pages.website_id')
            ->where('websites.user_id', $userId)
            ->orderByDesc('scans.created_at')
            ->limit(self::TREND_SCAN_LIMIT)
            ->get(['scans.created_at as scanned_at', "scan_results.{$metric} as value"])
            ->reverse()
            ->values()
            ->map(fn ($row) => [
                'scanned_at' => $row->scanned_at,
                'value' => $row->value,
            ]);
    }
}
