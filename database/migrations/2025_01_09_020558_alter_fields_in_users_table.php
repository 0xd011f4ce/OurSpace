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
        Schema::table('users', function (Blueprint $table) {
            $table->text ("interests_general")->nullable ()->change ();
            $table->text ("interests_music")->nullable ()->change ();
            $table->text ("interests_movies")->nullable ()->change ();
            $table->text ("interests_television")->nullable ()->change ();
            $table->text ("interests_books")->nullable ()->change ();
            $table->text ("interests_heroes")->nullable ()->change ();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string ("interests_general")->nullable ()->change ();
            $table->string ("interests_music")->nullable ()->change ();
            $table->string ("interests_movies")->nullable ()->change ();
            $table->string ("interests_television")->nullable ()->change ();
            $table->string ("interests_books")->nullable ()->change ();
            $table->string ("interests_heroes")->nullable ()->change ();
        });
    }
};
