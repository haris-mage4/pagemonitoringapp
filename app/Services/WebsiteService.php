<?php

namespace App\Services;

use App\Models\Scan;
use App\Models\ScanResult;
use App\Models\UptimeCheck;
use App\Models\Website;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class WebsiteService
{
    public function list(int $userId): Collection
    {
        return Website::query()
            ->where('user_id', $userId)
            ->withCount('pages')
            ->with(['uptimeChecks' => fn ($query) => $query->latest('checked_at')->limit(1)])
            ->get()
            ->each(fn (Website $website) => $website->setRelation('latestUptimeCheck', $website->uptimeChecks->first()));
    }

    /**
     * @return array<string, mixed>
     */
    public function details(Website $website): array
    {
        $performanceHistory = ScanResult::query()
            ->join('scans', 'scans.id', '=', 'scan_results.scan_id')
            ->join('pages', 'pages.id', '=', 'scans.page_id')
            ->where('pages.website_id', $website->id)
            ->whereNotNull('scan_results.performance')
            ->orderBy('scans.created_at')
            ->get(['scans.created_at as scanned_at', 'scan_results.performance as performance']);

        $pages = $website->pages()->withCount('pageErrors')->get()->map(function ($page) {
            $latestScan = Scan::where('page_id', $page->id)
                ->with('scanResult')
                ->latest('finished_at')
                ->first();

            $page->setRelation('latestScan', $latestScan);

            return $page;
        });

        $latestScan = Scan::whereIn('page_id', $website->pages()->pluck('id'))
            ->with('page', 'scanResult')
            ->latest('finished_at')
            ->first();

        $lastScannedAt = $website->pages()
            ->join('scans', 'scans.page_id', '=', 'pages.id')
            ->max('scans.created_at');

        $intervalMinutes = Website::SCHEDULE_INTERVAL_MINUTES[$website->schedule] ?? null;
        $nextScheduledScan = match (true) {
            ! $website->enabled || $intervalMinutes === null => null,
            $lastScannedAt === null => now(),
            default => Carbon::parse($lastScannedAt)->addMinutes($intervalMinutes),
        };

        $latestUptimeCheck = UptimeCheck::where('website_id', $website->id)
            ->latest('checked_at')
            ->first();

        return [
            'website' => $website,
            'pages' => $pages,
            'latest_scan' => $latestScan,
            'current_score' => $performanceHistory->last()?->performance,
            'previous_score' => $performanceHistory->slice(-2, 1)->first()?->performance,
            'performance_history' => $performanceHistory->values(),
            'next_scheduled_scan' => $nextScheduledScan,
            'latest_uptime_check' => $latestUptimeCheck,
        ];
    }

    public function create(int $userId, array $data): Website
    {
        return Website::create([...$data, 'user_id' => $userId]);
    }

    public function update(Website $website, array $data): Website
    {
        $website->update($data);

        return $website;
    }

    public function delete(Website $website): void
    {
        $website->delete();
    }

    public function setEnabled(Website $website, bool $enabled): Website
    {
        $website->update(['enabled' => $enabled]);

        return $website;
    }
}
