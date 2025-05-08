<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;

Route::withoutMiddleware(['web'])->group(function () {
    Route::get('/', function () {
        return view('welcome');
    });

    # webhook routes
    Route::post('/webhook', [WebhookController::class, 'handleWebhook']);
    Route::get('/webhook', [WebhookController::class, 'verifyWebhook']);
});
