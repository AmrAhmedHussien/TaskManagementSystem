<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('tasks', TaskController::class);
    Route::post('tasks/{task}/dependencies', [TaskController::class, 'addDependency']);
    Route::delete('tasks/{task}/dependencies/{dependency}', [TaskController::class, 'removeDependency']);
    Route::patch('tasks/{task}/assign', [TaskController::class, 'assignTask']);
});
