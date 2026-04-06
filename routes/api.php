<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserManagementController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Fichier principal des routes API de l'application de gestion des clubs.
| Toutes les routes sont organisées par rôle :
| - Public (accessible sans authentification)
| - Authentifié
|    Super Admin
|    Admin Club
|--------------------------------------------------------------------------
*/

/*
---------------------------------------------------------------------------------------------------
---------------------------------------------------------------------------------------------------
----------------------------- Pour ajouter un Super Admin manuellement-----------------------------

 ==> Dans le terminal taper la command : php artisan tinker
 ==> Puis coller ce code la :

        \App\Models\User::create([
            'email' => 'admin@enset.ma',
            'password' => bcrypt('password123'), // ou un autre mot de passe sécurisé
            'role' => 'super_admin',
            'club_id' => null
        ]);
 ==> Puis taper ctrl+c pour terminer
---------------------------------------------------------------------------------------------------
---------------------------------------------------------------------------------------------------
---------------------------------------------------------------------------------------------------

 * */





/*
|--------------------------------------------------------------------------
| ROUTES PUBLIQUES (visibles par tout le monde)
|--------------------------------------------------------------------------
*/

// Liste de tous les clubs
Route::get('clubs', [\App\Http\Controllers\ClubController::class, 'index']);

// Détails d’un club spécifique
Route::get('clubs/{id}', [\App\Http\Controllers\ClubController::class, 'show']);

// Liste de toutes les annonces (événements)
Route::get('/announcements', [\App\Http\Controllers\EventsController::class, 'index']);

// Détails d'une annonce
Route::get('/announcements/{id}', [\App\Http\Controllers\EventsController::class, 'show']);

// Annonces d’un club spécifique
Route::get('/Clubannouncements/{id}', [\App\Http\Controllers\EventsController::class, 'clubEvent']);

// Liste de toutes les activités
Route::get('/activities', [\App\Http\Controllers\ActivityController::class, 'index']);

// Détail d'une activité
Route::get('/activities/{id}', [\App\Http\Controllers\ActivityController::class, 'show']);

// Activités d’un club spécifique
Route::get('/ClubActivity/{id}', [\App\Http\Controllers\ActivityController::class, 'clubActivity']);



/*
|--------------------------------------------------------------------------
 ROUTES AUTHENTIFIÉES (nécessitent un token valide)
|--------------------------------------------------------------------------
*/

// Connexion
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // Déconnexion
    Route::post('/logout', [AuthController::class, 'logout']);

    // Obtenir les infos de l’utilisateur connecté
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('materials', [\App\Http\Controllers\MaterialReservationController::class, 'index']);
    Route::get('materials/{id}', [\App\Http\Controllers\MaterialReservationController::class, 'show']);

    // Liste des réservations de salle
    Route::get('salle_reservation', [\App\Http\Controllers\SalleReservationController::class, 'index']);
    // Détail d’une réservation de salle
    Route::get('salle_reservation/{id}', [\App\Http\Controllers\SalleReservationController::class, 'show']);


});
/*
|--------------------------------------------------------------------------
| ROUTES SUPER ADMIN (accessible uniquement par super_admin)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'role:super_admin'])->group(function () {

    // Création d’un compte admin de club
    Route::post('/admins', [UserManagementController::class, 'createClubAdmin']);
    Route::get('clubs/{clubId}/users/details', [\App\Http\Controllers\ClubController::class, 'indexSuperAdmin']);



    // Ajout d’un club
    Route::post('clubs', [\App\Http\Controllers\ClubController::class, 'store']);
    // Activer/désactiver un club
    Route::post('/clubs/{id}/active', [\App\Http\Controllers\ClubController::class, 'updateActiveStatus']);
    // Supprimer un club
    Route::delete('clubs/{id}', [\App\Http\Controllers\ClubController::class, 'destroy']);


       // Mise à jour personnalisée d’une salle
    Route::get('salles', [\App\Http\Controllers\SalleController::class, 'index']);
    Route::get('salles/{id}', [\App\Http\Controllers\SalleController::class, 'show']);
    Route::post('salles', [\App\Http\Controllers\SalleController::class, 'store']);
    Route::delete('salles/{id}', [\App\Http\Controllers\SalleController::class, 'destroy']);
    Route::post('salles/update/{id}', [\App\Http\Controllers\SalleController::class, 'update']);

//    // Liste de tous les matériels
//    Route::get('materials', [\App\Http\Controllers\MaterialReservationController::class, 'index']);
//    // Détails d’un matériel
//    Route::get('materials/{id}', [\App\Http\Controllers\MaterialReservationController::class, 'show']);

    // Mise à jour du statut d’une réservation de matériel
    Route::post('/materials/{id}/status', [\App\Http\Controllers\MaterialReservationController::class, 'updateStatus']);




    // Mise à jour du statut d'une réservation de salle
    Route::post('salle_reservation/{id}/status', [\App\Http\Controllers\SalleReservationController::class, 'updateStatus']);

    Route::get('/club/{clubId}/reservations', [\App\Http\Controllers\SalleReservationController::class, 'getClubReservationsByStatus']);


    // Liste des utilisateurs avec recherche par email
    Route::get('/users', [\App\Http\Controllers\UserController::class, 'index']);
    // Créer un nouvel utilisateur
    Route::post('/users', [\App\Http\Controllers\UserController::class, 'store']);
    // Afficher un utilisateur spécifique
    Route::get('/users/{id}', [\App\Http\Controllers\UserController::class, 'show']);
    // Supprimer un utilisateur
    Route::delete('/users/{id}', [\App\Http\Controllers\UserController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
 ROUTES ADMIN CLUB (accessible uniquement par admin_club)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'role:admin_club'])->group(function () {

    // Mettre à jour un club (son propre club)
    Route::post('clubs/update/{id}', [\App\Http\Controllers\ClubController::class, 'update']);


    // Créer une réservation de matériel
    Route::post('materials', [\App\Http\Controllers\MaterialReservationController::class, 'store']);
    // Mettre à jour une réservation de matériel
    Route::post('materials/update/{id}', [\App\Http\Controllers\MaterialReservationController::class, 'update']);
    // Supprimer une réservation de matériel
    Route::delete('materials/{id}', [\App\Http\Controllers\MaterialReservationController::class, 'destroy']);

    // Liste des réservations de salle (du club)
//    Route::get('salle_reservation', [\App\Http\Controllers\SalleReservationController::class, 'index']);
    // Créer une réservation de salle
    Route::post('salle_reservation', [\App\Http\Controllers\SalleReservationController::class, 'store']);
    // Détail d’une réservation de salle
//    Route::get('salle_reservation/{id}', [\App\Http\Controllers\SalleReservationController::class, 'show']);
    // Mettre à jour une réservation de salle
    Route::post('salle_reservation/update/{id}', [\App\Http\Controllers\SalleReservationController::class, 'update']);
    // Supprimer une réservation de salle
    Route::delete('salle_reservation/{id}', [\App\Http\Controllers\SalleReservationController::class, 'destroy']);

    // Créer une annonce
    Route::post('/announcements', [\App\Http\Controllers\EventsController::class, 'store']);
    // Mettre à jour une annonce
    Route::post('/announcements/{id}', [\App\Http\Controllers\EventsController::class, 'update']);
    // Supprimer une annonce
    Route::delete('/announcements/{id}', [\App\Http\Controllers\EventsController::class, 'destroy']);

    // Créer une activité
    Route::post('/activities', [\App\Http\Controllers\ActivityController::class, 'store']);
    // Mettre à jour une activité
    Route::post('/activities/{id}', [\App\Http\Controllers\ActivityController::class, 'update']);
    // Supprimer une activité
    Route::delete('/activities/{id}', [\App\Http\Controllers\ActivityController::class, 'destroy']);

    Route::get('/salles-available', [\App\Http\Controllers\SalleController::class, 'getAvailableSalles']);


});