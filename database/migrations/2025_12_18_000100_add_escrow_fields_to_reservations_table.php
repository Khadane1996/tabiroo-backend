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
            // Code de validation communiqué au client puis saisi par le chef
            $table->string('validation_code', 20)->nullable()->after('private_message');
            $table->timestamp('validation_code_used_at')->nullable()->after('validation_code');

            // Suivi des remboursements
            $table->string('refund_id')->nullable()->after('commission_amount');
            $table->timestamp('refunded_at')->nullable()->after('refund_id');

            // Distribution différée au chef (réintroduit pour le mode escrow)
            $table->boolean('payment_distributed')->default(false)->after('payment_intent_id');
            $table->string('transfer_id')->nullable()->after('payment_distributed');
            $table->timestamp('distributed_at')->nullable()->after('transfer_id');

            // Horodatage d'une éventuelle annulation automatique
            $table->timestamp('auto_cancelled_at')->nullable()->after('refunded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'validation_code',
                'validation_code_used_at',
                'refund_id',
                'refunded_at',
                'payment_distributed',
                'transfer_id',
                'distributed_at',
                'auto_cancelled_at',
            ]);
        });
    }
};
