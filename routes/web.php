<?php

use App\Http\Controllers\DonasiController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\UsermanController;

Route::get('/', [DonasiController::class, 'index']);
Route::get('/selengkapnya', [DonasiController::class, 'Selengkapnya']);
Route::get('/tentang-kami', [DonasiController::class, 'TentangKami']);
Route::get('/category/{id}', [DonasiController::class, 'CategoryDonasi']);
Route::get('/profile', [DonasiController::class, 'ProfileDonasi'])->middleware('auth');

// Donasi
Route::get('/donasi/detail/{slug}', [DonasiController::class, 'DetailDonasi']);
Route::get('donasi/payment/{id}', [DonasiController::class, 'DetailPayment']);
Route::get('donasi/history', [DonasiController::class, 'HistoryDonasi'])->middleware('auth');
Route::post('donasi/payment', [TransaksiController::class, 'berDonasi'])->middleware('throttle:10,1');
Route::post('/donasi/payment/callback', [TransaksiController::class, 'handleCallback']);

Route::prefix('admin')->middleware(['admin'])->group(function () {
    Route::resource('userman', UsermanController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('campaigns', CampaignController::class);
    Route::post('campaigns/{campaign}/updates', [\App\Http\Controllers\CampaignUpdateController::class, 'store'])->name('campaigns.updates.store');
    Route::delete('campaigns/updates/{update}', [\App\Http\Controllers\CampaignUpdateController::class, 'destroy'])->name('campaigns.updates.destroy');
    Route::resource('disbursements', \App\Http\Controllers\DisbursementController::class);
    Route::get('dashboard', [DashboardController::class, 'index']);
    Route::get('statistics', [DashboardController::class, 'campaignStats']);
});

Route::middleware(['guest'])->group(function () {
    Route::get('auth/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('auth/login', [AuthController::class, 'authenticate'])->middleware('throttle:10,1');
    Route::get('auth/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('auth/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
});
Route::get('logout', [AuthController::class, 'logout'])->middleware(['auth']);