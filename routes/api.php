<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\LikeController;
use App\Http\Controllers\Api\V1\PostController;
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




// ************************* Profile API Routes ***************************************


Route::prefix('v1')->middleware(['auth:sanctum','role:user'])->group(function()
{
    Route::get('/profile/me',[ProfileController::class,'me']);
    Route::put('/profile',[ProfileController::class,'update']);
});


// Public Profile

    Route::get('/profiles/{username}',[ProfileController::class,'profile']);

// *************************************************************************************



//  ************************   Posts API ********************

Route::prefix('v1')->middleware(['auth:sanctum','role:user'])->group(function(){
    Route::post('/posts',[PostController::class,'store']);
    Route::put('/posts',[PostController::class,'update']);
    Route::delete('/posts',[PostController::class,'destroy']);
    Route::get('/posts/{id}',[PostController::class,'show']);
    Route::get('/posts',[PostController::class,'index']);


    // Comment API
    Route::post('/comments',[CommentController::class,'store']);
    Route::put('/comments/{id}',[CommentController::class,'update']);
    Route::get('/comments/{id}',[CommentController::class,'index']);
    Route::post('/likes',[LikeController::class,'toggle']);
});



