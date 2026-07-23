<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Chrome / Lighthouse executables
    |--------------------------------------------------------------------------
    */

    'chrome_path' => env('PAGESPEED_CHROME_PATH', '/bin/google-chrome'),

    'lighthouse_path' => env('PAGESPEED_LIGHTHOUSE_PATH', '/usr/local/bin/lighthouse'),

    /*
    |--------------------------------------------------------------------------
    | Scheduling
    |--------------------------------------------------------------------------
    */

    'default_schedule' => env('PAGESPEED_DEFAULT_SCHEDULE', 'daily'),

    /*
    |--------------------------------------------------------------------------
    | Scan behavior
    |--------------------------------------------------------------------------
    */

    'scan_timeout' => env('PAGESPEED_SCAN_TIMEOUT', 120),

    'concurrent_scans' => env('PAGESPEED_CONCURRENT_SCANS', 1),

    /*
    |--------------------------------------------------------------------------
    | Deployment webhook
    |--------------------------------------------------------------------------
    */

    'webhook_delay' => env('PAGESPEED_WEBHOOK_DELAY', 600),

    'webhook_secret' => env('PAGESPEED_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Uptime monitoring
    |--------------------------------------------------------------------------
    */

    'uptime_check_interval' => env('PAGESPEED_UPTIME_CHECK_INTERVAL', 2),

    'uptime_check_timeout' => env('PAGESPEED_UPTIME_CHECK_TIMEOUT', 10),

];
