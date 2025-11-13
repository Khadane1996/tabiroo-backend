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
        Schema::table('plats', function (Blueprint $table) {
            $table->string('photo_url_2')->nullable()->after('photo_url');
            $table->string('photo_url_3')->nullable()->after('photo_url_2');
            $table->string('photo_url_4')->nullable()->after('photo_url_3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plats', function (Blueprint $table) {
            $table->dropColumn(['photo_url_2', 'photo_url_3', 'photo_url_4']);
        });
    }
};
