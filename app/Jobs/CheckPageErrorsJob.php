<?php

namespace App\Jobs;

use App\Models\Page;
use App\Services\PageErrorService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckPageErrorsJob implements ShouldQueue
{
    use Queueable;

    public int $timeout;

    public function __construct(public readonly Page $page)
    {
        $this->timeout = (int) config('pagespeed.js_error_check_timeout') + 20;
    }

    public function handle(PageErrorService $pageErrors): void
    {
        $pageErrors->check($this->page);
    }
}
