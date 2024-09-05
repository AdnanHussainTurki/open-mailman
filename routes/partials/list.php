<?php

use App\Http\Controllers\ListController;
use Illuminate\Support\Facades\Route;

Route::get('/lists', [ListController::class, 'index'])->name('lists');
Route::get('/list/create', [ListController::class, 'create'])->name('list.create');
Route::post('/list/store', [ListController::class, 'store'])->name('list.store');

Route::get('/list/edit', [ListController::class, 'edit'])->name('list.edit');
Route::post('/list/edit', [ListController::class, 'update'])->name('list.update');

Route::get('/list/{list}/show', [ListController::class, 'show'])->name('list.show');
