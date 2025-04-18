<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserManagementController;


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
Route::get('/clubs/search', [\App\Http\Controllers\ClubController::class, 'search']);

// Salle API Routes
Route::apiResource('salles', \App\Http\Controllers\SalleController::class);
Route::post('salles/update/{id}', [\App\Http\Controllers\SalleController::class, 'update']);

// Material API Routes
Route::apiResource('materials', \App\Http\Controllers\MaterialReservationController::class);
Route::post('materials/update/{id}', [\App\Http\Controllers\MaterialReservationController::class, 'update']);

// Material API Routes
Route::apiResource('salle_reservation', \App\Http\Controllers\SalleReservationController::class);
Route::post('salle_reservation/update/{id}', [\App\Http\Controllers\SalleReservationController::class, 'update']);


// Auth
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);


});
// Only Super Admin
Route::middleware(['auth:sanctum', 'role:super_admin'])->group(function () {
    Route::post('/admins', [UserManagementController::class, 'createClubAdmin']);
    ///....
});

// Only Super Admin
Route::middleware(['auth:sanctum', 'role:admin_club'])->group(function () {
    //.....

});





