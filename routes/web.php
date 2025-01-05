<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserActionController;

Route::get('/', [ HomeController::class, "home" ])->name ("home");

// auth related
Route::get ("/auth/login", [ UserController::class, "login" ])->name ("login")->middleware ("guest");
Route::get ("/auth/signup", [ UserController::class, "signup" ])->name ("signup")->middleware ("guest");
Route::get ("/auth/logout", [ UserController::class, "logout" ])->name ("logout")->middleware ("auth");
Route::post ("/auth/signup", [ UserController::class, "do_signup" ])->middleware ("guest");
Route::post ("/auth/login", [ UserController::class, "do_login" ])->middleware ("guest");

// user actions
Route::post ("/user/action/friend", [ UserActionController::class, "friend" ])->name ("user.friend")->middleware ("auth");
Route::post ("/user/action/unfriend", [ UserActionController::class, "unfriend" ])->name ("user.unfriend")->middleware ("auth");
Route::post ("/user/action/post/new", [ UserActionController::class, "post_new" ])->name ("user.post.new")->middleware ("auth");

// user routes
Route::get ("/user/edit", [ ProfileController::class, "edit" ])->name ("users.edit")->middleware ("auth");
Route::post ("/user/edit", [ ProfileController::class, "update" ])->middleware ("auth");
Route::get ("/user/{user_name}/friends", [ ProfileController::class, "friends" ])->name ("users.friends");
Route::get ("/user/{user_name}", [ ProfileController::class, "show" ])->name ("users.show");

// posts routes
Route::get ("/post/{note}/edit", [ PostController::class, "edit" ])->name ("posts.edit")->middleware ("auth");
Route::post ("/post/{note}/edit", [ PostController::class, "update" ])->middleware ("auth");
Route::post ("/post/{note}/like", [ PostController::class, "like" ])->name ("posts.like")->middleware ("auth");
Route::get ("/post/{note}", [ PostController::class, "show" ])->name ("posts.show");
Route::delete ("/post/{note}", [ PostController::class, "delete" ])->name ("posts.delete")->middleware ("auth");

// other routes
Route::get ("/browse", [ HomeController::class, "browse" ])->name ("browse"); // TODO: This
Route::get ("/tags/{tag}", [ HomeController::class, "tag" ])->name ("tags"); // TODO: This
Route::get ("/search", [ HomeController::class, "search" ])->name ("search");
Route::get ("/requests", [ HomeController::class, "requests" ])->name ("requests")->middleware ("auth");
Route::post ("/requests", [ HomeController::class, "requests_accept" ])->middleware ("auth");

require __DIR__ . "/api.php";
