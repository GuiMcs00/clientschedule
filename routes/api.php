<?php

use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\CustomerAppointmentController;
use App\Http\Controllers\Api\CustomerAppointmentSeriesController;
use Illuminate\Support\Facades\Route;

Route::get('/health-check-api', function () {
    return response('checked!', 200);
});

/*
 * API Routes
 */
Route::prefix('v1')->group(function () {
    Route::apiResource('customers', CustomerController::class);

    Route::post('customers/{customer}/restore', [CustomerController::class, 'restore']);
    Route::delete('customers/{customer}/force-delete', [CustomerController::class, 'forceDelete']);

    Route::apiResource('customers.appointments', CustomerAppointmentController::class);

    Route::apiResource('customers.series', CustomerAppointmentSeriesController::class);
});
