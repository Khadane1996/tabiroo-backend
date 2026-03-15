<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('stripe_details_submitted')->default(false)->after('stripe_account_id');
            $table->boolean('stripe_charges_enabled')->default(false)->after('stripe_details_submitted');
            $table->boolean('stripe_payouts_enabled')->default(false)->after('stripe_charges_enabled');
            $table->integer('stripe_requirements_currently_due_count')->default(0)->after('stripe_payouts_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_details_submitted',
                'stripe_charges_enabled',
                'stripe_payouts_enabled',
                'stripe_requirements_currently_due_count',
            ]);
        });
    }
};
