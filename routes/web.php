<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name("welcome");

Route::get('/reset-password/{token}', function ($token) {
    return view('auth.reset-password', ['token' => $token,]);
})->middleware(['guest'])->name('password.reset');

Route::post('/reset-password', [\App\Http\Controllers\UserController::class, 'resetPassword'])
->middleware(['guest'])->name('password.update');

Route::get("/test", function(){ return 'test'; });
