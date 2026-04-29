<?php

declare(strict_types=1);

use App\Http\Controllers\Api\MeasurementController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WeatherStationController;
use Illuminate\Support\Facades\Route;

Route::post('/users', [UserController::class, 'store']);
Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::patch('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);
Route::post('/users/{id}/subscriptions', [UserController::class, 'subscribe']);
Route::delete('/users/{id}/subscriptions/{weatherStationId}', [UserController::class, 'unsubscribe']);

Route::post('/weather-stations', [WeatherStationController::class, 'store']);
Route::get('/weather-stations', [WeatherStationController::class, 'index']);
Route::get('/weather-stations/{id}', [WeatherStationController::class, 'show']);
Route::patch('/weather-stations/{id}', [WeatherStationController::class, 'update']);
Route::delete('/weather-stations/{id}', [WeatherStationController::class, 'destroy']);

Route::get('/weather-stations/{weatherStationId}/measurements', [MeasurementController::class, 'indexByWeatherStation']);
Route::post('/measurements', [MeasurementController::class, 'store']);
Route::get('/measurements', [MeasurementController::class, 'index']);
Route::get('/measurements/{id}', [MeasurementController::class, 'show']);
Route::patch('/measurements/{id}', [MeasurementController::class, 'update']);
Route::delete('/measurements/{id}', [MeasurementController::class, 'destroy']);
