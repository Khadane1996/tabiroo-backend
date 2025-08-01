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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_prestation_id')->nullable()->constrained('menu_prestation')->onDelete('set null');
            $table->foreignId('client_id')->constrained('users');
            $table->foreignId('chef_id')->constrained('users');
            $table->decimal('sous_total', 10, 2);
            $table->decimal('frais_service', 10, 2);        
            $table->integer("nombre_convive")->default(0);
            $table->string('date_prestation');
            $table->text('transaction_detail')->nullable();
            $table->text('motif')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
