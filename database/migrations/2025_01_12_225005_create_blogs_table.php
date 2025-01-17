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
        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();

            $table->string ("name")->unique ();
            $table->string ("slug")->unique ();

            $table->timestamps();
        });

        Schema::create('blogs', function (Blueprint $table) {
            $table->id();

            $table->string ("name")->unique ();
            $table->string ("slug")->unique ();

            $table->text ("description")->nullable ();

            $table->string ("icon")->nullable ();

            $table->foreignId ("user_id")->nullable ()->constrained ()->onDelete ("cascade");
            $table->foreignId ("actor_id")->nullable ()->constrained ()->onDelete ("cascade");
            $table->foreignId ("blog_category_id")->nullable ()->constrained ();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs');
        Schema::dropIfExists('blog_categories');
    }
};
