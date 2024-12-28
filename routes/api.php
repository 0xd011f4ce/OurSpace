<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AP\APActorController;
use App\Http\Controllers\AP\APInboxController;

use App\Http\Controllers\AP\APInstanceInboxController;

use App\Http\Controllers\AP\APOutboxController;
use App\Http\Controllers\AP\APWebfingerController;

Route::get ("/.well-known/webfinger", [ APWebfingerController::class, "webfinger" ])->name ("ap.webfinger");

Route::prefix ("/ap/v1")->group (function () {
    Route::post ("/user/{user:name}/inbox", [ APInboxController::class, "inbox" ])->name ("ap.inbox");
    Route::post ("/user/{user:name}/outbox", [ APOutboxController::class, "outbox" ])->name ("ap.outbox");
    Route::get ("/user/{user:name}", [ APActorController::class, "user" ])->name ("ap.user");

    Route::post ("/inbox", [ APInstanceInboxController::class, "inbox" ])->name ("ap.inbox");
});
