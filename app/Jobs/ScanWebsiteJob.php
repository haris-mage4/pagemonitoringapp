<?php

namespace App\Jobs;

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

    public function handle(): void
    {
        $this->website->pages()
            ->where('enabled', true)
            ->each(fn ($page) => ScanPageJob::dispatch($page, $this->trigger));
    }
}
