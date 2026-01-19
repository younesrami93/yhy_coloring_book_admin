<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GenerationApiController;
use App\Http\Controllers\Api\StyleApiController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user/me', [AuthController::class, 'me']);
    Route::get('/styles', [StyleApiController::class, 'index']);


    Route::post('/generate', [GenerationApiController::class, 'store']);
    // Check Status (Poll)
    Route::get('/generate/{id}', [GenerationApiController::class, 'show']);

});
