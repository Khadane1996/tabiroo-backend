<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Flux de reservation
            $table->string('capture_method', 20)->nullable()->after('status');
            $table->string('flow_type', 20)->nullable()->after('capture_method');

            // Timers
            $table->timestamp('host_response_deadline')->nullable()->after('flow_type');
            $table->timestamp('otp_deadline')->nullable()->after('host_response_deadline');
            $table->timestamp('auto_validated_at')->nullable()->after('otp_deadline');

            // Payout tracking
            $table->timestamp('payout_initiated_at')->nullable()->after('distributed_at');
            $table->timestamp('payout_completed_at')->nullable()->after('payout_initiated_at');

            // Montants CDC
            $table->decimal('stripe_fee_amount', 10, 2)->nullable()->after('commission_amount');
            $table->decimal('total_charged', 10, 2)->nullable()->after('stripe_fee_amount');

            // Tracabilite Stripe
            $table->string('stripe_charge_id')->nullable()->after('payment_intent_id');
            $table->string('stripe_balance_transaction_id')->nullable()->after('stripe_charge_id');

            // Remboursement detaille
            $table->string('refund_status', 30)->nullable()->after('refund_id');
            $table->string('refund_reason')->nullable()->after('refund_status');
            $table->timestamp('refund_timestamp')->nullable()->after('refund_reason');
            $table->string('refund_failure_reason')->nullable()->after('refund_timestamp');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'capture_method',
                'flow_type',
                'host_response_deadline',
                'otp_deadline',
                'auto_validated_at',
                'payout_initiated_at',
                'payout_completed_at',
                'stripe_fee_amount',
                'total_charged',
                'stripe_charge_id',
                'stripe_balance_transaction_id',
                'refund_status',
                'refund_reason',
                'refund_timestamp',
                'refund_failure_reason',
            ]);
        });
    }
};
