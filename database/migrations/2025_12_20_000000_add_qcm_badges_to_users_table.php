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
            $table->unsignedTinyInteger('hygiene_qcm_badge_level')
                ->default(0)
                ->after('stripe_customer_id')
                ->comment('Niveau de badge obtenu pour le QCM Hygiène Tabiroo');

            $table->unsignedTinyInteger('hospitalite_qcm_badge_level')
                ->default(0)
                ->after('hygiene_qcm_badge_level')
                ->comment('Niveau de badge obtenu pour le QCM Hospitalité Tabiroo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['hygiene_qcm_badge_level', 'hospitalite_qcm_badge_level']);
        });
    }
};

