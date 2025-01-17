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
            $table->text ("bio")->nullable ();
            $table->string ("avatar")->nullable ();

            $table->string ("status")->nullable ();
            $table->string ("mood")->nullable ();
            $table->string ("about_you")->nullable ();
            $table->text ("blurbs")->nullable ();

            // interests
            $table->string ("interests_general")->nullable ();
            $table->string ("interests_music")->nullable ();
            $table->string ("interests_movies")->nullable ();
            $table->string ("interests_television")->nullable ();
            $table->string ("interests_books")->nullable ();
            $table->string ("interests_heroes")->nullable ();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn ("bio");
            $table->dropColumn ("avatar");

            $table->dropColumn ("status");
            $table->dropColumn ("mood");
            $table->dropColumn ("about_you");
            $table->dropColumn ("blurbs");

            $table->dropColumn ("interests_general");
            $table->dropColumn ("interests_music");
            $table->dropColumn ("interests_movies");
            $table->dropColumn ("interests_television");
            $table->dropColumn ("interests_books");
            $table->dropColumn ("interests_heroes");
        });
    }
};
