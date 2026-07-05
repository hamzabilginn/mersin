<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkforceController;

Route::get('/', [WorkforceController::class, 'index']);
Route::get('/api/wbs', [WorkforceController::class, 'getWbs']);
Route::get('/api/plans', [WorkforceController::class, 'getPlans']);
Route::post('/api/plans', [WorkforceController::class, 'storePlan']);
Route::post('/api/facts/sync', [WorkforceController::class, 'syncFacts']);
Route::post('/api/plans/{id}/approve', [WorkforceController::class, 'approveFact']);
