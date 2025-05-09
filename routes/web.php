<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::withoutMiddleware(['web'])->group(function () {
    Route::get('/', function () {
        return '<html><body style="display:flex;justify-content:center;align-items:center;height:100vh;font-family:sans-serif;"><h1>WhatsApp Webhook Service</h1></body></html>';
    });

    # webhook routes
    Route::post('/webhook', [WebhookController::class, 'handleWebhook']);
    Route::get('/webhook', [WebhookController::class, 'verifyWebhook']);
});
