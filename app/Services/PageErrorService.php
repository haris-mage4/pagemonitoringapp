<?php

namespace App\Services;

use App\Models\Page;
use App\Models\PageError;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class PageErrorService
{
    /**
     * Navigate the page headless, capture console/page errors, and upsert them
     * into `page_errors` (matched by a fingerprint of the message so repeat
     * occurrences bump `occurrence_count`/`last_seen_at` instead of duplicating).
     *
     * @return array<int, PageError> newly-seen errors on this check (not repeats)
     */
    public function check(Page $page): array
    {
        $timeout = (int) config('pagespeed.js_error_check_timeout');

        $result = Process::timeout($timeout + 10)->run([
            config('pagespeed.node_path'),
            config('pagespeed.js_error_script_path'),
            $page->url,
            config('pagespeed.chrome_path'),
            (string) ($timeout * 1000),
        ]);

        if (! $result->successful()) {
            Log::warning('JS error capture failed', [
                'page_id' => $page->id,
                'url' => $page->url,
                'exit_code' => $result->exitCode(),
                'error' => $result->errorOutput(),
            ]);

            return [];
        }

        $output = json_decode($result->output(), true);

        if (! is_array($output) || ! ($output['success'] ?? false)) {
            Log::warning('Could not parse JS error capture output', [
                'page_id' => $page->id,
                'url' => $page->url,
                'output' => $result->output(),
            ]);

            return [];
        }

        $newErrors = [];

        foreach ($output['errors'] as $error) {
            [$pageError, $isNew] = $this->upsert($page, $error);

            if ($isNew) {
                $newErrors[] = $pageError;
            }
        }

        return $newErrors;
    }

    /**
     * @param  array{message: string, source: string|null, stack: string|null}  $error
     * @return array{0: PageError, 1: bool}
     */
    private function upsert(Page $page, array $error): array
    {
        $fingerprint = hash('sha256', $error['message']);

        $existing = PageError::where('page_id', $page->id)
            ->where('fingerprint', $fingerprint)
            ->first();

        if ($existing) {
            $existing->update([
                'last_seen_at' => now(),
                'occurrence_count' => $existing->occurrence_count + 1,
            ]);

            return [$existing, false];
        }

        $pageError = PageError::create([
            'page_id' => $page->id,
            'fingerprint' => $fingerprint,
            'message' => $error['message'],
            'source' => $error['source'] ?? null,
            'stack' => $error['stack'] ?? null,
            'first_seen_at' => now(),
            'last_seen_at' => now(),
            'occurrence_count' => 1,
        ]);

        return [$pageError, true];
    }
}
