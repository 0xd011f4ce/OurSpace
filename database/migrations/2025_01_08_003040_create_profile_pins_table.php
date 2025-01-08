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
        Schema::create('profile_pins', function (Blueprint $table) {
            $table->id();
            $table->foreignId ("activity_id")->nullable ()->constrained ()->onDelete ("cascade");
            $table->foreignId ("note_id")->constrained ()->onDelete ("cascade");
            $table->foreignId ("actor_id")->constrained ()->onDelete ("cascade");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_pins');
    }
};
