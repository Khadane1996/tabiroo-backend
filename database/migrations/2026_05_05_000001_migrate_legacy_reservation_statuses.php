<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Mapping des anciens statuts vers les nouveaux.
     * Anciens: pending, accepted, declined, rejected, completed, cancelled
     */
    private array $mapping = [
        'pending'  => 'pending_host_response',
        'accepted' => 'confirmed',
        'declined' => 'declined_by_host',
        'rejected' => 'declined_by_host',
        'completed' => 'completed_validated',
        'cancelled' => 'cancelled_by_guest_before_48h',
    ];

    public function up(): void
    {
        foreach ($this->mapping as $old => $new) {
            DB::table('reservations')
                ->where('status', $old)
                ->update(['status' => $new]);
        }
    }

    public function down(): void
    {
        foreach ($this->mapping as $old => $new) {
            DB::table('reservations')
                ->where('status', $new)
                ->update(['status' => $old]);
        }
    }
};
