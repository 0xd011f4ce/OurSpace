<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;

Route::get('/', [ HomeController::class, "home" ])->name ("home");

// auth related
Route::get ("/auth/login", [ UserController::class, "login" ])->name ("login")->middleware ("guest");
Route::get ("/auth/signup", [ UserController::class, "signup" ])->name ("signup")->middleware ("guest");
Route::get ("/auth/logout", [ UserController::class, "logout" ])->name ("logout")->middleware ("auth");
Route::post ("/auth/signup", [ UserController::class, "do_signup" ])->middleware ("guest");
Route::post ("/auth/login", [ UserController::class, "do_login" ])->middleware ("guest");

// user routes
Route::get ("/user/edit", [ ProfileController::class, "edit" ])->name ("users.edit")->middleware ("auth");
Route::post ("/user/edit", [ ProfileController::class, "update" ])->middleware ("auth");
Route::get ("/user/{user_name}", [ ProfileController::class, "show" ])->name ("users.show");

// other routes
Route::get ("/search", [ HomeController::class, "search" ])->name ("search");
Route::get ("/requests", [ HomeController::class, "requests" ])->name ("requests")->middleware ("auth");

require __DIR__ . "/api.php";
