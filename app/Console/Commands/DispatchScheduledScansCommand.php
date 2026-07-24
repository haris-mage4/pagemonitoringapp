<?php

namespace App\Console\Commands;

use App\Jobs\ScanWebsiteJob;
use App\Models\Website;
use Illuminate\Console\Command;

class DispatchScheduledScansCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pagespeed:dispatch-scheduled-scans';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch a scan for every enabled website whose schedule interval has elapsed';

    public function handle(): int
    {
        Website::query()
            ->where('enabled', true)
            ->each(function (Website $website) {
                if ($this->isDue($website)) {
                    ScanWebsiteJob::dispatchSync($website, 'schedule');
                    $this->info("Dispatched scan for website #{$website->id} ({$website->name}).");
                }
            });

        return self::SUCCESS;
    }

    private function isDue(Website $website): bool
    {
        $intervalMinutes = Website::SCHEDULE_INTERVAL_MINUTES[$website->schedule] ?? null;

        if ($intervalMinutes === null) {
            $this->warn("Website #{$website->id} has unknown schedule '{$website->schedule}', skipping.");

            return false;
        }

        $lastScannedAt = $website->pages()
            ->join('scans', 'scans.page_id', '=', 'pages.id')
            ->max('scans.created_at');

        if ($lastScannedAt === null) {
            return true;
        }

        return abs(now()->diffInMinutes($lastScannedAt)) >= $intervalMinutes;
    }
}
