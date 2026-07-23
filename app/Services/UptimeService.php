<?php

namespace App\Services;

use App\Models\UptimeCheck;
use App\Models\Website;
use App\Notifications\WebsiteUptimeStatusChanged;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class UptimeService
{
    public function check(Website $website): UptimeCheck
    {
        $previousStatus = $website->uptimeChecks()->latest('checked_at')->value('status');

        $start = microtime(true);

        try {
            $response = Http::timeout(config('pagespeed.uptime_check_timeout'))
                ->get($website->base_url);

            $status = $response->successful() ? 'online' : 'unavailable';
            $httpCode = $response->status();
        } catch (ConnectionException) {
            $status = 'offline';
            $httpCode = null;
        }

        $responseTimeMs = (int) round((microtime(true) - $start) * 1000);

        $check = $website->uptimeChecks()->create([
            'status' => $status,
            'http_code' => $httpCode,
            'response_time_ms' => $responseTimeMs,
            'checked_at' => now(),
        ]);

        if ($previousStatus !== null && $this->isUp($previousStatus) !== $this->isUp($status)) {
            $website->user->notify(new WebsiteUptimeStatusChanged($website, $check));
        }

        return $check;
    }

    private function isUp(string $status): bool
    {
        return $status === 'online';
    }
}
