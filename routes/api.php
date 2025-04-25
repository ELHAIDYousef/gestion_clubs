<?php

use App\Http\Controllers\ActivityController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventsController;


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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
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
