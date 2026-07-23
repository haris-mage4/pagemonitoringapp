<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('pagespeed:dispatch-scheduled-scans')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('pagespeed:check-uptime')
    ->cron('*/'.max(1, (int) config('pagespeed.uptime_check_interval')).' * * * *')
    ->withoutOverlapping();

Schedule::command('pagespeed:check-page-errors')
    ->cron('*/'.max(1, (int) config('pagespeed.page_error_check_interval')).' * * * *')
    ->withoutOverlapping();
