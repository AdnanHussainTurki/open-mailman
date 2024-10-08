<?php

use App\Http\Controllers\ListController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\MessengerController;
use App\Http\Controllers\SubscriberController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::any('/subscribers/update-or-create', [SubscriberController::class, 'updateOrCreate']);
Route::any('/subscribers/difference-external-ids', [SubscriberController::class, 'differenceExternalIds']);

Route::any('/list/subscribe', [ListController::class, 'subscribe']);
Route::any('/list/unsubscribe-all', [ListController::class, 'unsubscribeAll']);

Route::any('/message/send', [MessengerController::class, 'sendMessage']);
