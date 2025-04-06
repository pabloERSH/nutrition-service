<?php

use App\Http\Controllers\FatSecretApiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/nutrition_service/foods/search', [FatSecretApiController::class, 'search']);
