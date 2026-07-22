<?php

namespace App\Services;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;

class LighthouseService
{
    /**
     * Run a mobile Lighthouse scan against a URL.
     *
     * @return array{success: bool, metrics: array<string, mixed>|null, raw: array<string, mixed>|null, exit_code: int, error_message: string|null}
     */
    public function scan(string $url): array
    {
        $command = [
            config('pagespeed.lighthouse_path'),
            $url,
            '--output=json',
            '--output-path=stdout',
            '--only-categories=performance,accessibility,best-practices,seo',
            '--chrome-flags=--headless=new --no-sandbox',
            '--chrome-path='.config('pagespeed.chrome_path'),
        ];

        $result = Process::timeout((int) config('pagespeed.scan_timeout'))->run($command);

        if (! $result->successful()) {
            Log::warning('Lighthouse scan failed', [
                'url' => $url,
                'exit_code' => $result->exitCode(),
                'error' => $result->errorOutput(),
            ]);

            return [
                'success' => false,
                'metrics' => null,
                'raw' => null,
                'exit_code' => $result->exitCode() ?? 1,
                'error_message' => trim($result->errorOutput()) ?: 'Lighthouse process failed with no error output.',
            ];
        }

        $raw = json_decode($result->output(), true);

        if (! is_array($raw)) {
            return [
                'success' => false,
                'metrics' => null,
                'raw' => null,
                'exit_code' => $result->exitCode(),
                'error_message' => 'Could not parse Lighthouse JSON output.',
            ];
        }

        return [
            'success' => true,
            'metrics' => $this->extractMetrics($raw),
            'raw' => $raw,
            'exit_code' => $result->exitCode(),
            'error_message' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    private function extractMetrics(array $raw): array
    {
        $categories = $raw['categories'] ?? [];
        $audits = $raw['audits'] ?? [];

        $categoryScore = fn (string $key) => isset($categories[$key]['score'])
            ? (int) round($categories[$key]['score'] * 100)
            : null;

        $auditValue = fn (string $key) => $audits[$key]['numericValue'] ?? null;

        return [
            'performance' => $categoryScore('performance'),
            'accessibility' => $categoryScore('accessibility'),
            'seo' => $categoryScore('seo'),
            'best_practices' => $categoryScore('best-practices'),
            'fcp' => $auditValue('first-contentful-paint'),
            'lcp' => $auditValue('largest-contentful-paint'),
            'cls' => $audits['cumulative-layout-shift']['numericValue'] ?? null,
            'tbt' => $auditValue('total-blocking-time'),
            'speed_index' => $auditValue('speed-index'),
            'tti' => $auditValue('interactive'),
        ];
    }
}
