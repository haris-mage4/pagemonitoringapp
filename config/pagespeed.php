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

    /*
    |--------------------------------------------------------------------------
    | JS console error capture
    |--------------------------------------------------------------------------
    */

    'node_path' => env('PAGESPEED_NODE_PATH', 'node'),

    'js_error_script_path' => env(
        'PAGESPEED_JS_ERROR_SCRIPT_PATH',
        base_path('node-scripts/capture-console-errors.js')
    ),

    'js_error_check_timeout' => env('PAGESPEED_JS_ERROR_CHECK_TIMEOUT', 30),

    'page_error_check_interval' => env('PAGESPEED_PAGE_ERROR_CHECK_INTERVAL', 60),

];
