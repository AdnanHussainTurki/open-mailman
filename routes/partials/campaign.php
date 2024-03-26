<?php

use App\Http\Controllers\CampaignController;
use Illuminate\Support\Facades\Route;

Route::get('/campaigns', [CampaignController::class, 'index'])->name('campaigns');
Route::get('/campaign/create', [CampaignController::class, 'create'])->name('campaign.create');
Route::post('/campaign/store', [CampaignController::class, 'store'])->name('campaign.store');
Route::get('/campaign/{campaign}/show', [CampaignController::class, 'show'])->name('campaign.show');
Route::get('/campaign/{campaign}/history', [CampaignController::class, 'history'])->name('campaign.history');
Route::get('/campaign/{campaign}/activate', [CampaignController::class, 'activate'])->name('campaign.activate');
