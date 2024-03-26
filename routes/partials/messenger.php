<?php

use App\Http\Controllers\MessengerController;
use Illuminate\Support\Facades\Route;

Route::get('/messengers', [MessengerController::class, 'index'])->name('messengers');
Route::get('/messenger/create', [MessengerController::class, 'create'])->name('messenger.create');
Route::post('/messenger/store', [MessengerController::class, 'store'])->name('messenger.store');
Route::post('/messenger/{messenger}/deactivate', [MessengerController::class, 'create'])->name('messenger.deactivate');
