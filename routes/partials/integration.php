<?php

use App\Http\Controllers\IntegrationController;
use Illuminate\Support\Facades\Route;

Route::get('/integration', [IntegrationController::class, 'index'])->name('integration');
Route::post('/integration/token', [IntegrationController::class, 'storeToken'])->name('integration.token.store');
Route::post('/integration/token/{token}/revoke', [IntegrationController::class, 'revokeToken'])->name('integration.token.revoke');
