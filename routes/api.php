<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

// Club API Routes
Route::apiResource('clubs', \App\Http\Controllers\ClubController::class);
Route::post('clubs/update/{id}', [\App\Http\Controllers\ClubController::class, 'update']);

// Salle API Routes
Route::apiResource('salles', \App\Http\Controllers\SalleController::class);
Route::post('salles/update/{id}', [\App\Http\Controllers\SalleController::class, 'update']);

// Material API Routes
Route::apiResource('materials', \App\Http\Controllers\MaterialReservationController::class);
Route::post('materials/update/{id}', [\App\Http\Controllers\MaterialReservationController::class, 'update']);

// Material API Routes
Route::apiResource('salle_reservation', \App\Http\Controllers\SalleReservationController::class);
Route::post('salle_reservation/update/{id}', [\App\Http\Controllers\SalleReservationController::class, 'update']);


