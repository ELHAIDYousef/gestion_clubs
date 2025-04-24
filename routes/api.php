<?php


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
Route::get('clubs', [\App\Http\Controllers\ClubController::class, 'index']);
Route::get('clubs/{id}', [\App\Http\Controllers\ClubController::class, 'show']);


// Auth
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);


});

// Only Super Admin
Route::middleware(['auth:sanctum', 'role:super_admin'])->group(function () {
    Route::post('/admins', [UserManagementController::class, 'createClubAdmin']);
    // Club API Routes
    Route::post('clubs', [\App\Http\Controllers\ClubController::class, 'store']);
    Route::post('/clubs/{id}/active', [\App\Http\Controllers\ClubController::class, 'updateActiveStatus']);
    Route::delete('clubs/{id}', [\App\Http\Controllers\ClubController::class, 'destroy']);

    // Salle API Routes
    Route::apiResource('salles', \App\Http\Controllers\SalleController::class);
    Route::post('salles/update/{id}', [\App\Http\Controllers\SalleController::class, 'update']);

    // Material API Routes
    Route::post('/material-reservations/{id}/status', [\App\Http\Controllers\MaterialReservationController::class, 'updateStatus']);
    Route::get('materials', [\App\Http\Controllers\MaterialReservationController::class, 'index']);
    Route::get('materials/{id}', [\App\Http\Controllers\MaterialReservationController::class, 'show']);

    // Salles API Routes
    Route::get('salle_reservation', [\App\Http\Controllers\SalleReservationController::class, 'index']);
    Route::get('salle_reservation/{id}', [\App\Http\Controllers\SalleReservationController::class, 'show']);

});

// Only club Admin
Route::middleware(['auth:sanctum', 'role:admin_club'])->group(function () {

    // Clubs API Routes
    Route::post('clubs/update/{id}', [\App\Http\Controllers\ClubController::class, 'update']);

    // Material API Routes
    Route::delete('materials/{id}', [\App\Http\Controllers\MaterialReservationController::class, 'destroy']);
    Route::post('materials/update/{id}', [\App\Http\Controllers\SalleReservationController::class, 'update']);
    Route::post('materials', [\App\Http\Controllers\MaterialReservationController::class, 'store']);

    // Salle API Routes

    // Salles reservation API Routes
    Route::get('salle_reservation', [\App\Http\Controllers\SalleReservationController::class, 'index']);
    Route::get('salle_reservation/{id}', [\App\Http\Controllers\SalleReservationController::class, 'show']);
    Route::delete('salle_reservation/{id}', [\App\Http\Controllers\SalleReservationController::class, 'destroy']);
    Route::post('salle_reservation', [\App\Http\Controllers\SalleReservationController::class, 'store']);
    Route::post('salle_reservation/update/{id}', [\App\Http\Controllers\SalleReservationController::class, 'update']);

});





