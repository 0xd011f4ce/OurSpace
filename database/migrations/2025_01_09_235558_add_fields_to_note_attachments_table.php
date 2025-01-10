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
        Schema::table('note_attachments', function (Blueprint $table) {
            $table->string ("media_type")->nullable ();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('note_attachments', function (Blueprint $table) {
            $table->dropColumn ("media_type");
        });
    }
};
