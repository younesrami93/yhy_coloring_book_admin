<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\GenerationApiController;
use App\Http\Controllers\Api\StyleApiController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user/me', [AuthController::class, 'me']);
    Route::post('/devices/sync', [DeviceController::class, 'sync']);
    Route::get('/styles', [StyleApiController::class, 'index']);


    Route::post('/generate', [GenerationApiController::class, 'store']);
    Route::get('/generations', [GenerationApiController::class, 'index']);
    Route::get('/generate/{id}', [GenerationApiController::class, 'show']);

});

// test route , print some json text
    Route::get('/test-json', function () {
    return response()->json([
        'message' => '
This is a test JSON response from the API.',
        'status' => 'success',
    ]);
});