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
        Schema::table('activities', function (Blueprint $table) {
            $table->json ("to")->default (json_encode (["https://www.w3.org/ns/activitystreams#Public"], JSON_UNESCAPED_SLASHES))->nullable ();
            $table->json ("cc")->default (json_encode ([], JSON_UNESCAPED_SLASHES))->nullable ();
        });

        Schema::table ("notes", function (Blueprint $table) {
            $table->json ("to")->default (json_encode (["https://www.w3.org/ns/activitystreams#Public"], JSON_UNESCAPED_SLASHES))->nullable ();
            $table->json ("cc")->default (json_encode ([], JSON_UNESCAPED_SLASHES))->nullable ();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn ("to");
            $table->dropColumn ("cc");
        });

        Schema::table ("notes", function (Blueprint $table) {
            $table->dropColumn ("to");
            $table->dropColumn ("cc");
        });
    }
};
