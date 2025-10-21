<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TrainDashboardController;

// ダッシュボード
Route::get('/', [TrainDashboardController::class, 'index'])->name('dashboard');

// 鉄道事業者詳細
Route::get('/operator/{slug}', [TrainDashboardController::class, 'show'])->name('operator.show');

