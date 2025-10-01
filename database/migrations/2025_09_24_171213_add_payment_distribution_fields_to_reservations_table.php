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
            $table->string('payment_intent_id')->nullable()->after('transaction_detail');
            $table->boolean('payment_distributed')->default(false)->after('payment_intent_id');
            $table->string('transfer_id')->nullable()->after('payment_distributed');
            $table->decimal('chef_amount', 10, 2)->nullable()->after('transfer_id');
            $table->decimal('commission_amount', 10, 2)->nullable()->after('chef_amount');
            $table->timestamp('distributed_at')->nullable()->after('commission_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'payment_intent_id',
                'payment_distributed',
                'transfer_id',
                'chef_amount',
                'commission_amount',
                'distributed_at'
            ]);
        });
    }
};