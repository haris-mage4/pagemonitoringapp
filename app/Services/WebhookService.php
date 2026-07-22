<?php

namespace App\Services;

use App\Jobs\ScanWebsiteJob;
use App\Models\Website;

class WebhookService
{
    /**
     * Queue a delayed scan for the website targeted by a verified Bitbucket deployment webhook.
     */
    public function handleDeployment(Website $website): void
    {
        ScanWebsiteJob::dispatch($website, 'webhook')
            ->delay(now()->addSeconds((int) config('pagespeed.webhook_delay')));
    }
}
