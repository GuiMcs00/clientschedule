<?php

use App\Http\Controllers\Api\CustomerController;
use Illuminate\Support\Facades\Route;

Route::get('/health-check-api', function () {
    return response('checked!', 200);
});

/*
 * API Routes for Customer Resource
 */
Route::apiResource('customers', CustomerController::class);
Route::post('customers/{id}/restore', [CustomerController::class, 'restore']);
Route::delete('customers/{id}/force-delete', [CustomerController::class, 'forceDelete']);