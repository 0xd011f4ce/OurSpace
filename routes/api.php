<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AP\APActorController;
use App\Http\Controllers\AP\APGeneralController;

use App\Http\Controllers\AP\APOutboxController;
use App\Http\Controllers\AP\APInboxController;

use App\Http\Controllers\AP\APInstanceInboxController;

use App\Http\Controllers\AP\APWebfingerController;
use App\Http\Controllers\AP\APNodeInfoController;

Route::get ("/.well-known/webfinger", [ APWebfingerController::class, "webfinger" ])->name ("ap.webfinger");
Route::get ("/.well-known/nodeinfo", [ APNodeInfoController::class, "wk_nodeinfo" ])->name ("ap.nodeinfo");
Route::get ("/.well-known/nodeinfo/2.1", [ APNodeInfoController::class, "nodeinfo" ])->name ("ap.nodeinfo");

Route::prefix ("/ap/v1")->group (function () {
    // users
    Route::post ("/user/{name}/inbox", [ APInboxController::class, "inbox" ])->name ("ap.inbox");
    Route::post ("/user/{name}/outbox", [ APOutboxController::class, "outbox" ])->name ("ap.outbox");
    Route::get ("/user/{name}/followers", [ APActorController::class, "followers" ])->name ("ap.followers");
    Route::get ("/user/{name}/following", [ APActorController::class, "following" ])->name ("ap.following");
    Route::get ("/user/{name}/collections/featured", [ APActorController::class, "featured" ])->name ("ap.featured");
    Route::get ("/user/{name}", [ APActorController::class, "user" ])->name ("ap.user");

    // notes
    Route::get ("/note/{note:private_id}", [ APGeneralController::class, "note" ])->name ("ap.note");

    // instance
    Route::post ("/inbox", [ APInstanceInboxController::class, "inbox" ])->name ("ap.inbox");
});
