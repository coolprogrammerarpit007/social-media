<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/v1/check-health',[AuthController::class,'checkHealth']);

// Auth Routes

Route::prefix('v1')->group(function(){
    Route::post('/auth/register',[AuthController::class,'register']);
    Route::post('/auth/login',[AuthController::class,'login']);
    Route::post('/auth/logout',[AuthController::class,'logout'])->middleware('auth:sanctum');
});


Route::prefix('v1')->middleware(['auth:sanctum','role:user'])->group(function(){
    Route::get('/user/profile/{username}',[ProfileController::class,'show']);
});
