<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->timestamp('cancelled_at')->nullable()->after('auto_cancelled_at');
            $table->string('cancelled_by', 20)->nullable()->after('cancelled_at')->comment('client, chef, or system');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['cancelled_at', 'cancelled_by']);
        });
    }
};
