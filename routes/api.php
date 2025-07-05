<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/', function () {
    return response()->json(['message' => 'API is working']);
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify', [AuthController::class, 'verifyOtp']);

Route::middleware('auth:sanctum')->group(function () {
    // Current user basic info
    Route::get('/user', fn(Request $request) => $request->user());

    Route::put('/saveprofile', [ProfileController::class, 'saveProfile']);
    Route::put('/profile', [ProfileController::class, 'updateProfile']);

    // ✅ Profile fetch
    Route::get('/profile', function (Request $request) {
        return response()->json([
            'status' => true,
            'user' => $request->user()->load('driverProfile')
        ]);
    });
});
