<?php

use App\Http\Controllers\Api\BitbucketWebhookController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\WebsiteController;
use App\Http\Middleware\VerifyBitbucketWebhookSignature;
use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
    return response()->json(['status' => 'ok']);
});

Route::apiResource('websites', WebsiteController::class);
Route::patch('websites/{website}/enabled', [WebsiteController::class, 'setEnabled']);
Route::post('websites/{website}/scan', [WebsiteController::class, 'scan']);

Route::apiResource('websites.pages', PageController::class)->shallow();
Route::patch('pages/{page}/enabled', [PageController::class, 'setEnabled']);

Route::post('webhooks/bitbucket/deployment', [BitbucketWebhookController::class, 'deployment'])
    ->middleware(VerifyBitbucketWebhookSignature::class);

Route::get('dashboard/summary', [DashboardController::class, 'summary']);
Route::get('dashboard/trend/{metric}', [DashboardController::class, 'trend'])
    ->whereIn('metric', ['performance', 'lcp', 'cls', 'tbt']);
