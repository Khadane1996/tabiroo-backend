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
        Schema::create('plats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('photo_url')->nullable();
            $table->string('nom');
            $table->string('bioPlat');
            $table->text('ingredient')->nullable();
            $table->text('allergene')->nullable();
            $table->foreignId('type_de_plat_id')->nullable()->constrained('types_de_plat')->onDelete('set null');
            $table->foreignId('type_de_cuisine_id')->nullable()->constrained('types_de_cuisine')->onDelete('set null');
            $table->foreignId('regime_alimentaire_id')->nullable()->constrained('regimes_alimentaire')->onDelete('set null');
            $table->foreignId('theme_culinaire_id')->nullable()->constrained('themes_culinaire')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plats');
    }
};
