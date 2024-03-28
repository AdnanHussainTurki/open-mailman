<?php

use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;


Route::post('/message', 'MessageController@store')->name('message.store');
Route::get('/message/{message}/change-status', [MessageController::class, 'changeStatus'])->name('message.change-status');
Route::get('/message/{message}/send', [MessageController::class, 'send'])->name('message.send');
