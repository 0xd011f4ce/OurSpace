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
        Schema::create('hashtags', function (Blueprint $table) {
            $table->id();

            $table->string('name')->unique();

            $table->timestamps();
        });

        Schema::create('note_hashtag', function (Blueprint $table) {
            $table->id ();

            $table->foreignId ('note_id')->constrained ()->onDelete ('cascade');
            $table->foreignId ('hashtag_id')->constrained ()->onDelete ('cascade');

            $table->timestamps ();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('note_hashtag');
        Schema::dropIfExists('hashtags');
    }
};
