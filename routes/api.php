<?php

use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\WebsiteController;
use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
    return response()->json(['status' => 'ok']);
});

Route::apiResource('websites', WebsiteController::class);
Route::patch('websites/{website}/enabled', [WebsiteController::class, 'setEnabled']);

Route::apiResource('websites.pages', PageController::class)->shallow();
Route::patch('pages/{page}/enabled', [PageController::class, 'setEnabled']);
