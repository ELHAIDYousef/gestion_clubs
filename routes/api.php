<?php


use App\Http\Controllers\ActivityController;
use Illuminate\Http\Request;
use App\Http\Controllers\EventsController;
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

    // les route de les eventement
    Route::controller(EventsController::class)->group(function () {
        Route::get('/announcements', 'index');
        Route::get('/announcements/{id}', 'show');
    });
    // ce rout pour obteuner les evenements de chaque club
    Route::get('/Clubannouncements/{id}',[EventsController::class,'clubEvent']);
    // les route de les eventement avec authentification
    Route::controller(EventsController::class)->group(function () {
        Route::post('/announcements', 'store');
        Route::delete('/announcements/{id}', 'destroy');
        Route::post('/announcements/{id}', 'update'); // Ou utiliser PUT avec _method
    });
    // rout activite
    Route::controller(ActivityController::class)->group(function () {
        Route::get('/activities', 'index');
        Route::get('/activities/{id}', 'show');
    });
     // rout activite avec authentification
     Route::controller(ActivityController::class)->group(function () {
        Route::post('/activities', 'store');
        Route::delete('/activities/{id}', 'destroy');
        Route::post('/activities/{id}', 'update'); // Ou utiliser PUT avec _method
    });
    // ce rout pour obteuner les activite de chaque club
    Route::get('/ClubActivity/{id}',[ActivityController::class,'clubActivity']);


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


