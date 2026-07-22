<?php

namespace App\Services;

use App\Models\Page;
use App\Models\Scan;

class ScanService
{
    public function __construct(private readonly LighthouseService $lighthouse) {}

    public function scanPage(Page $page, string $trigger): Scan
    {
        $scan = Scan::create([
            'page_id' => $page->id,
            'status' => 'running',
            'trigger' => $trigger,
            'started_at' => now(),
        ]);

        $result = $this->lighthouse->scan($page->url);

        $scan->scanResult()->create([
            'device' => 'mobile',
            ...($result['success'] ? $result['metrics'] : []),
            'raw_json' => $result['raw'],
            'exit_code' => $result['exit_code'],
            'error_message' => $result['error_message'],
        ]);

        $scan->update([
            'status' => $result['success'] ? 'completed' : 'failed',
            'finished_at' => now(),
        ]);

        return $scan->fresh('scanResult');
    }
}
