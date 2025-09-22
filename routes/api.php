<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\RecipeController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
});
Route::middleware('auth:api')->group(function () {
    Route::apiResource('ingredients', IngredientController::class);
});
Route::middleware('auth:api')->group(function () {
    Route::apiResource('recipes', RecipeController::class);
});
