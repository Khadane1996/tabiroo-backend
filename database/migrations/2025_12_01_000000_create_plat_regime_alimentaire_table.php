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
        Schema::create('plat_regime_alimentaire', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plat_id')->constrained('plats')->onDelete('cascade');
            $table->foreignId('regime_alimentaire_id')->constrained('regimes_alimentaire')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plat_regime_alimentaire');
    }
};


