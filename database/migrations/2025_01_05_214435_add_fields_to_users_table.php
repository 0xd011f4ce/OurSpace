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
            $table->boolean ("is_admin")->default (false);
            $table->boolean ("is_mod")->default (false);
            $table->timestamp ("last_online_at")->nullable ();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn ("is_admin");
            $table->dropColumn ("is_mod");
            $table->dropColumn ("last_online_at");
        });
    }
};