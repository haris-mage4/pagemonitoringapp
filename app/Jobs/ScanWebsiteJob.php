<?php

namespace App\Jobs;

use App\Models\Scan;
use App\Models\Website;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ScanWebsiteJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Website $website,
        public readonly string $trigger,
    ) {}

    /**
     * Create each page's Scan row as `pending` right away, so a busy queue delaying
     * the actual ScanPageJob run doesn't make the scheduler think nothing was dispatched.
     */
    public function handle(): void
    {
        $this->website->pages()
            ->where('enabled', true)
            ->each(function ($page) {
                $scan = Scan::create([
                    'page_id' => $page->id,
                    'status' => 'pending',
                    'trigger' => $this->trigger,
                ]);

                ScanPageJob::dispatch($page, $this->trigger, $scan);
            });
    }
}
