<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TrainInfoController;

// 電車運行情報 API
Route::prefix('train')->group(function () {
    // 全ての鉄道事業者と運行状況を取得
    Route::get('/', [TrainInfoController::class, 'index']);

    // 遅延している路線のみを取得
    Route::get('/delayed', [TrainInfoController::class, 'delayed']);

    // 特定の鉄道事業者の運行状況を取得
    Route::get('/{slug}', [TrainInfoController::class, 'show']);

    // 特定路線の運行状況履歴を取得
    Route::get('/line/{lineId}/history', [TrainInfoController::class, 'history']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

