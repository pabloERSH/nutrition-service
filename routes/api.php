<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\EatenFoodController;
use App\Http\Controllers\Api\V1\SavedFoodController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(['auth:sanctum']);

Route::prefix('v1')->middleware(['throttle:api'])->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

Route::prefix('v1')->middleware(['throttle:api', 'auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/saved-foods', [SavedFoodController::class, 'index']);
    Route::post('/saved-foods', [SavedFoodController::class, 'store']);
    Route::get('/saved-foods/search', [SavedFoodController::class, 'search']);
    Route::delete('/saved-foods/{savedFood}', [SavedFoodController::class, 'destroy']);
    Route::patch('/saved-foods/{savedFood}', [SavedFoodController::class, 'update']);

    Route::get('/eaten-foods', [EatenFoodController::class, 'index']);
    Route::post('/eaten-foods', [EatenFoodController::class, 'store']);
    Route::delete('/eaten-foods/{eatenFood}', [EatenFoodController::class, 'destroy']);
    Route::patch('/eaten-foods/{eatenFood}', [EatenFoodController::class, 'update']);
    Route::get('/eaten-foods/show-by-date', [EatenFoodController::class, 'showByDate']);
});
