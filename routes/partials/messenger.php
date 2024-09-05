<?php

use App\Http\Controllers\MessengerController;
use Illuminate\Support\Facades\Route;

Route::get('/messengers', [MessengerController::class, 'index'])->name('messengers');
Route::get('/messenger/create', [MessengerController::class, 'create'])->name('messenger.create');
Route::post('/messenger/store', [MessengerController::class, 'store'])->name('messenger.store');
Route::get('/messenger/{messenger}/show', [MessengerController::class, 'show'])->name('messenger.show');

Route::get('/messenger/{messenger}/edit', [MessengerController::class, 'edit'])->name('messenger.edit');
Route::post('/messenger/{messenger}/edit', [MessengerController::class, 'update'])->name('messenger.update');


Route::post('/messenger/{messenger}/deactivate', [MessengerController::class, 'create'])->name('messenger.deactivate');
