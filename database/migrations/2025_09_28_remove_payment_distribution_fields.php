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
        Schema::table('reservations', function (Blueprint $table) {
            // Supprimer les colonnes liées à la distribution différée
            $table->dropColumn([
                'payment_distributed',
                'transfer_id',
                'distributed_at'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->boolean('payment_distributed')->default(false)->after('payment_intent_id');
            $table->string('transfer_id')->nullable()->after('payment_distributed');
            $table->timestamp('distributed_at')->nullable()->after('commission_amount');
        });
    }
};
