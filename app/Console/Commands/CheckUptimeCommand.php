<?php

namespace App\Console\Commands;

use App\Jobs\CheckWebsiteUptimeJob;
use App\Models\Website;
use Illuminate\Console\Command;

class CheckUptimeCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'pagespeed:check-uptime';

    /**
     * @var string
     */
    protected $description = 'Dispatch an uptime check job for every enabled website';

    public function handle(): int
    {
        Website::query()
            ->where('enabled', true)
            ->each(function (Website $website) {
                CheckWebsiteUptimeJob::dispatch($website);
            });

        return self::SUCCESS;
    }
}
