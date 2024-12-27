<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AP\APActorController;
use App\Http\Controllers\AP\APWebfingerController;

use App\Http\Controllers\AP\APInboxController;
use App\Http\Controllers\AP\APOutboxController;

Route::get ("/.well-known/webfinger", [ APWebfingerController::class, "webfinger" ])->name ("ap.webfinger");

Route::prefix ("/ap/v1")->group (function () {
    Route::get ("/user/{user:name}", [ APActorController::class, "user" ])->name ("ap.user");
    Route::post ("/user/{user:name}/inbox", [ APInboxController::class, "inbox" ])->name ("ap.inbox");
    Route::post ("/user/{user:name}/outbox", [ APOutboxController::class, "outbox" ])->name ("ap.outbox");
});
