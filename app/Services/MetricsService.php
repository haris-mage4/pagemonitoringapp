<?php

namespace App\Services;

use App\Models\Scan;
use App\Models\ScanResult;
use App\Models\Website;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class MetricsService
{
    private const TREND_METRICS = ['performance', 'lcp', 'cls', 'tbt'];

    /**
     * @return array<string, mixed>
     */
    public function dashboardSummary(): array
    {
        $lastScan = Scan::with('page.website', 'scanResult')->latest('finished_at')->first();

        return [
            'total_websites' => Website::count(),
            'last_scan' => $lastScan,
            'failed_scans' => Scan::where('status', 'failed')->count(),
            'average_performance' => round(
                (float) ScanResult::whereNotNull('performance')->avg('performance'),
                1
            ),
            'recent_activity' => Scan::with('page.website', 'scanResult')
                ->latest('finished_at')
                ->limit(10)
                ->get(),
        ];
    }

    /**
     * @return Collection<int, array{scanned_at: string, value: mixed}>
     */
    public function trend(string $metric, string $range, ?Carbon $from = null, ?Carbon $to = null): Collection
    {
        if (! in_array($metric, self::TREND_METRICS, true)) {
            throw new \InvalidArgumentException("Unsupported trend metric [{$metric}].");
        }

        [$from, $to] = $this->resolveRange($range, $from, $to);

        return ScanResult::query()
            ->join('scans', 'scans.id', '=', 'scan_results.scan_id')
            ->whereBetween('scans.created_at', [$from, $to])
            ->orderBy('scans.created_at')
            ->get(['scans.created_at as scanned_at', "scan_results.{$metric} as value"])
            ->map(fn ($row) => [
                'scanned_at' => $row->scanned_at,
                'value' => $row->value,
            ]);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolveRange(string $range, ?Carbon $from, ?Carbon $to): array
    {
        return match ($range) {
            '24h' => [now()->subDay(), now()],
            '7d' => [now()->subDays(7), now()],
            '30d' => [now()->subDays(30), now()],
            'custom' => [
                $from ?? throw new \InvalidArgumentException('`from` is required for a custom range.'),
                $to ?? throw new \InvalidArgumentException('`to` is required for a custom range.'),
            ],
            default => throw new \InvalidArgumentException("Unsupported range [{$range}]."),
        };
    }
}
