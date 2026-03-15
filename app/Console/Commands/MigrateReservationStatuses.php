<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateReservationStatuses extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'reservations:migrate-statuses {--dry-run : Afficher les changements sans les appliquer}';

    /**
     * The console command description.
     */
    protected $description = 'Migrer les anciens statuts de réservation vers les nouveaux statuts CDC';

    /**
     * Mapping des anciens statuts vers les nouveaux.
     */
    protected array $statusMapping = [
        'pending'   => 'pending_host_response',
        'accepted'  => 'confirmed',
        'cancelled' => 'cancelled_by_guest_before_48h',
        'completed' => 'completed_validated',
        'upcoming'  => 'confirmed',
        'expired'   => 'cancelled_no_response',
        'draft'     => 'draft',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('Mode dry-run activé — aucune modification ne sera effectuée.');
        }

        $this->info('Début de la migration des statuts de réservation...');
        $this->newLine();

        $summaryRows = [];
        $totalMigrated = 0;

        foreach ($this->statusMapping as $oldStatus => $newStatus) {
            $count = DB::table('reservations')
                ->where('status', $oldStatus)
                ->count();

            if ($count > 0 && $oldStatus !== $newStatus) {
                if (!$dryRun) {
                    DB::table('reservations')
                        ->where('status', $oldStatus)
                        ->update(['status' => $newStatus]);
                }

                $this->line("  {$oldStatus} → {$newStatus} : {$count} réservation(s)");
                $totalMigrated += $count;
            }

            $action = $oldStatus === $newStatus
                ? 'Inchangé'
                : ($count > 0 ? ($dryRun ? 'À migrer' : 'Migré') : 'Aucune entrée');

            $summaryRows[] = [
                $oldStatus,
                $newStatus,
                $count,
                $action,
            ];
        }

        $this->newLine();
        $this->table(
            ['Ancien statut', 'Nouveau statut', 'Nombre', 'Action'],
            $summaryRows,
        );

        $this->newLine();

        if ($dryRun) {
            $this->warn("Dry-run terminé — {$totalMigrated} réservation(s) seraient migrées.");
        } else {
            $this->info("Migration terminée — {$totalMigrated} réservation(s) migrées avec succès.");
        }

        return self::SUCCESS;
    }
}
