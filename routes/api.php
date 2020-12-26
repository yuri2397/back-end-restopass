<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\EstablishmentController;
use App\Http\Controllers\RestoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VigilantController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;

Route::get("/test", function(){ return 'test'; });
/**
 * User Routes
 */

Route::post('/forgot-password', function (Request $request) {
    $request->validate(['email' => 'required|email|exists:users,email']);

    $status = Password::sendResetLink(
        $request->only('email')
    );

    return $status === Password::RESET_LINK_SENT
        ? response()->json(["error" => false, "message" => "Email envoyé"], 200)
        : response()->json(["error" => true, "message" => "Erreur du serveur"], 404);
})->middleware(['guest'])->name('password.email');


// login
Route::post("user/login", [UserController::class, "login"]);
// Refersh token
Route::post("user/refresh", [UserController::class, "refresh"]);

Route::group(['middleware' => ['auth:api']], function (){
    // user logout
    Route::post('user/logout', [UserController::class, "logout"]);
    // user transfert here pay
    Route::post("user/transfer", [UserController::class, "transfert"]);
    // user confirm tranfert
    Route::post("user/confirm", [UserController::class, "confirmTransfert"]);
    // user bay tickets
    Route::post("user/bay", [UserController::class, "bay"]);
    // user update password
    Route::post("user/password/update", [UserController::class, "updatePassword"]);
    Route::post("user/emprunt", [UserController::class, "emprunt"]);
    // user profile
    Route::get("user/profile", [UserController::class, "profile"]);
	// user pay
    Route::get("user/pay", [UserController::class, "pay"]);
    // user get tranfer history
    Route::get("user/history", [UserController::class, "transferHistory"]);
    // user get resto history
    Route::get("user/passage", [UserController::class, "historiquePassage"]);
});


/**
 * Admin Routes
 */

 // login
 Route::post("/admin/login", [AdminController::class, "login"]);
 // refresh token
 Route::post("/admin/refresh", [AdminController::class, "refresh"]);

Route::group(['middleware' => ['auth:admin', 'abilitie']], function () {
    Route::post("/admin/logout", [AdminController::class, "logout"]);
    Route::post("admin/create", [AdminController::class, "create"]);

    // User CRUD
    Route::post("user/update", [UserController::class, "update"]);
    // Admin add a new account
    Route::post("user/create", [UserController::class, "create"]);
    // Admin delete a account
    Route::post("user/delete", [UserController::class, "remove"]);
    // Admin get all user's profile
    Route::get("users", [UserController::class, "users"]);
    // Admin get a user profile
    Route::get("user/{number}", [UserController::class, "user"]);

     // Resto CRUD
     Route::post("resto/update", [RestoController::class, "update"]);
     // Admin add a new account
     Route::post("resto/store", [RestoController::class, "store"]);
     // Admin delete a account
     Route::post("resto/delete", [RestoController::class, "remove"]);
     // Admin get all user's profile
     Route::get("restos", [RestoController::class, "users"]);
     // Admin get a user profile
     Route::get("resto/{id}", [RestoController::class, "show"]);

    // Admin create an establishment
    Route::post("establishment/create", [EstablishmentController::class, "create"]);
    // Admin update an establishement
    //Route::post("admin/update/establishment", [EstablishmentController::class, 'update']);
    // Admin delete an establishment
    Route::post("establishment/delete", [EstablishmentController::class, "remove"]);
    // Admin get an establishement
    Route::get("establishment/{name}", [EstablishmentController::class, 'establishment']);
    // Admin get all establishement
    Route::get("establishments", [EstablishmentController::class, 'establishments']);
    // Admin change the establishement's status
    Route::post("state", [EstablishmentController::class, 'changeState']);

    // Admin update a vigil account
    Route::post("vigilant/update", [VigilantController::class, "update"]);
    // Admin create a vigil account
    Route::post("vigilant/create", [VigilantController::class, "create"]);
    // Admin delete a vigil account
    Route::post("vigilant/delete", [VigilantController::class, "remove"]);
    // Admin get all vigil's account
    Route::get("vigilants", [VigilantController::class, "vigils"]);
    // Admin get a vigil's account
    Route::get("vigilant/{code}", [VigilantController::class, "vigil"]);
});

/**
 * Vigil Routes
*/

// Vigil Login
Route::post('/vigilant/login', [VigilantController::class, "login"]);
// Vigil Refresh Token
Route::post("/vigilant/refresh", [VigilantController::class, "refresh"]);

Route::group(['middleware' => ['auth:vigilant']], function(){
    // Vigil Logout
    Route::post("vigilant/logout", [VigilantController::class, "logout"]);
    // Vigil
    Route::post("vigilant/scan", [VigilantController::class, "scan"]);
});
