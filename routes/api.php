<?php

declare(strict_types=1);

use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WeatherStationController;
use Illuminate\Support\Facades\Route;

Route::post('/users', [UserController::class, 'store']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::patch('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);
Route::post('/users/{id}/subscriptions', [UserController::class, 'subscribe']);
Route::delete('/users/{id}/subscriptions/{stationId}', [UserController::class, 'unsubscribe']);

Route::post('/stations', [WeatherStationController::class, 'store']);
Route::get('/stations/{id}', [WeatherStationController::class, 'show']);
Route::patch('/stations/{id}', [WeatherStationController::class, 'update']);
Route::delete('/stations/{id}', [WeatherStationController::class, 'destroy']);
