<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('actors', function (Blueprint $table) {
            $table->id();

            $table->foreignId ("user_id")->nullable ()->constrained ()->onDelete ("cascade");

            $table->string ("type")->nullable ();
            $table->string ("actor_id")->unique ();
            $table->string ("local_actor_id")->unique ()->nullable ();

            $table->string ("following")->nullable ();
            $table->string ("followers")->nullable ();

            $table->string ("liked")->nullable ();

            $table->string ("inbox")->nullable ();
            $table->string ("outbox")->nullable ();

            $table->string ("sharedInbox")->nullable ();

            $table->string ("preferredUsername")->nullable ();
            $table->string ("name")->nullable ();
            $table->text ("summary")->nullable ();

            $table->text ("public_key")->nullable ();
            $table->text ("private_key")->nullable ();

            $table->string ("icon")->nullable ();
            $table->string ("image")->nullable ();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actors');
    }
};
