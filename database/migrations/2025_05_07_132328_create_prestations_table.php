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
        Schema::create('prestations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('type_de_plat')->constrained('types_de_plat')->onDelete('cascade'); // Clé étrangère pour le type de plat
            $table->string('start_time');
            $table->string('end_time');
            $table->string('date_limite');
            $table->string('heure_arrivee_convive');
            $table->string('date_prestation');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prestations');
    }
};
