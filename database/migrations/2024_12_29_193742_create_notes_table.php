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
        Schema::create('notes', function (Blueprint $table) {
            $table->id();

            $table->foreignId ("activity_id")->nullable ()->constrained ()->onDelete ("cascade");
            $table->foreignId ("actor_id")->nullable ()->constrained ()->onDelete ("cascade");

            $table->string ("note_id")->unique ();
            $table->string ("in_reply_to")->nullable ();
            $table->string ("type")->default ("Note");
            $table->string ("summary")->nullable ();
            $table->string ("url")->nullable ();
            $table->string ("attributedTo")->nullable ();
            $table->text ("content")->nullable ();
            $table->string ("tag")->nullable ();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
