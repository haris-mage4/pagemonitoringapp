<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BitbucketWebhookController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\WebsiteController;
use App\Http\Middleware\VerifyBitbucketWebhookSignature;
use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
    return response()->json(['status' => 'ok']);
});

Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/forgot-password', [PasswordResetController::class, 'sendResetLink']);
Route::post('auth/reset-password', [PasswordResetController::class, 'reset']);

Route::post('webhooks/bitbucket/deployment', [BitbucketWebhookController::class, 'deployment'])
    ->middleware(VerifyBitbucketWebhookSignature::class);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);

    Route::apiResource('websites', WebsiteController::class);
    Route::patch('websites/{website}/enabled', [WebsiteController::class, 'setEnabled']);
    Route::post('websites/{website}/scan', [WebsiteController::class, 'scan']);

    Route::apiResource('websites.pages', PageController::class)->shallow();
    Route::patch('pages/{page}/enabled', [PageController::class, 'setEnabled']);

    Route::get('dashboard/summary', [DashboardController::class, 'summary']);
    Route::get('dashboard/trend/{metric}', [DashboardController::class, 'trend'])
        ->whereIn('metric', ['performance', 'lcp', 'cls', 'tbt']);
});
