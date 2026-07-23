<?php

namespace App\Console\Commands;

use App\Jobs\CheckPageErrorsJob;
use App\Models\Page;
use Illuminate\Console\Command;

class CheckPageErrorsCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'pagespeed:check-page-errors';

    /**
     * @var string
     */
    protected $description = 'Dispatch a JS console error check for every enabled page on every enabled website';

    public function handle(): int
    {
        Page::query()
            ->where('enabled', true)
            ->whereHas('website', fn ($query) => $query->where('enabled', true))
            ->each(function (Page $page) {
                CheckPageErrorsJob::dispatch($page);
            });

        return self::SUCCESS;
    }
}
