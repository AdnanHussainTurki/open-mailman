<?php

use App\Http\Controllers\SubscriberController;
use Illuminate\Support\Facades\Route;

Route::get('/subscribers', [SubscriberController::class, 'create'])->name('subscribers');
Route::get('/subscriber/create', [SubscriberController::class, 'create'])->name('subscriber.create');
Route::post('/subscriber/store', [SubscriberController::class, 'store'])->name('subscriber.store');
Route::post('/subscriber/{subscriber}/deactivate', [SubscriberController::class, 'create'])->name('subscriber.deactivate');
