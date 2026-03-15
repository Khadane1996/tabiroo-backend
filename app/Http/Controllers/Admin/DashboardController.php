<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\ReservationStatus;
use App\Models\Incident;
use App\Models\Post;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Prestation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->query('period', 'month');

        $postsCount = Post::count();
        $publishedCount = Post::where('status', 'published')->count();

        $today = Carbon::today()->toDateString();

        // Calculate $start and $end based on period
        [$start, $end] = $this->getDateRange($period);

        // === Section 1: Volume d'activite ===
        $volumeStats = $this->getVolumeStats($start, $end);

        // === Section 2: Reservations auto vs manuelles ===
        $flowStats = $this->getFlowStats();

        // === Section 3: Annulations ===
        $annulationsStats = $this->getAnnulationsStats();

        // === Section 4: Remboursements ===
        $refundStats = $this->getRefundStats();

        // === Section 5: Paiements en attente ===
        $pendingPayments = $this->getPendingPayments();

        // === Section 6: Comptes Stripe hotes ===
        $stripeAccountStats = $this->getStripeAccountStats();

        // === Section 7: Incidents ===
        $incidentStats = $this->getIncidentStats();

        // === Section 8: Disputes (CDC 11.5) ===
        $disputeStats = $this->getDisputeStats();

        // Indicateurs existants
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();
        $reservationsToday = $this->countReservationsByDate($today);
        $reservationsThisMonth = $this->countReservationsByDateRange($startOfMonth, $endOfMonth);
        $convivesToday = $this->sumConvivesByDate($today);
        $convivesThisMonth = $this->sumConvivesByDateRange($startOfMonth, $endOfMonth);
        $caMoisEnCours = $this->sumCaByDateRange($startOfMonth, $endOfMonth);

        $prestationsAVenir = Prestation::whereRaw("TO_DATE(SUBSTRING(date_prestation, 1, 10), 'YYYY-MM-DD') >= ?", [$today])
            ->count();

        // Activite plateforme
        $completedStatuses = array_map(fn($s) => $s->value, ReservationStatus::completedStatuses());
        $chefIdsAvecActivite = Reservation::whereIn('status', $completedStatuses)->pluck('chef_id')->unique()->filter();
        $hotesActifs = User::where('role_id', 2)
            ->where(function ($q) use ($chefIdsAvecActivite) {
                $q->whereHas('prestations')
                    ->orWhereIn('id', $chefIdsAvecActivite);
            })
            ->count();

        $convivesActifs = User::where('role_id', 3)
            ->whereIn('id', Reservation::whereIn('status', $completedStatuses)->pluck('client_id')->unique()->filter())
            ->count();

        $prestationsPubliees = Prestation::whereRaw("TO_DATE(SUBSTRING(date_prestation, 1, 10), 'YYYY-MM-DD') >= ?", [$today])
            ->whereHas('menus')
            ->count();

        // Convives par jour (30 derniers jours)
        $convivesParJour = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i)->toDateString();
            $convivesParJour[] = [
                'date' => $date,
                'label' => Carbon::parse($date)->format('d/m'),
                'count' => $this->sumConvivesByDate($date),
            ];
        }

        return view('admin.dashboard', [
            'postsCount' => $postsCount,
            'publishedCount' => $publishedCount,
            // Indicateurs cles
            'reservationsToday' => $reservationsToday,
            'reservationsThisMonth' => $reservationsThisMonth,
            'convivesToday' => $convivesToday,
            'convivesThisMonth' => $convivesThisMonth,
            'caMoisEnCours' => $caMoisEnCours,
            'prestationsAVenir' => $prestationsAVenir,
            // Activite plateforme
            'hotesActifs' => $hotesActifs,
            'convivesActifs' => $convivesActifs,
            'prestationsPubliees' => $prestationsPubliees,
            // Graphique
            'convivesParJour' => $convivesParJour,
            // Period filter
            'period' => $period,
            // CDC: 7 sections du dashboard + disputes
            'volumeStats' => $volumeStats,
            'flowStats' => $flowStats,
            'annulations' => $annulationsStats,
            'refundStats' => $refundStats,
            'pendingPayments' => $pendingPayments,
            'stripeAccountStats' => $stripeAccountStats,
            'incidentStats' => $incidentStats,
            'disputeStats' => $disputeStats,
        ]);
    }

    // === Section 1: Volume d'activite ===
    private function getVolumeStats(?string $start, ?string $end): array
    {
        $completedStatuses = array_map(fn($s) => $s->value, ReservationStatus::completedStatuses());
        $allActiveAndCompleted = array_merge(
            array_map(fn($s) => $s->value, ReservationStatus::activeStatuses()),
            $completedStatuses
        );

        $dateFilter = function ($query) use ($start, $end) {
            if ($start !== null && $end !== null) {
                $query->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59']);
            }
        };

        return [
            'total_reservations' => Reservation::where($dateFilter)->count(),
            'total_menus_amount' => (float) Reservation::whereIn('status', $allActiveAndCompleted)->where($dateFilter)->sum('sous_total'),
            'total_paid_by_guests' => (float) Reservation::whereIn('status', $allActiveAndCompleted)->where($dateFilter)->sum('total_charged'),
            'total_reversed_to_hosts' => (float) Reservation::where('payment_distributed', true)->where($dateFilter)->sum('chef_amount'),
            'total_commissions' => (float) Reservation::whereIn('status', $completedStatuses)->where($dateFilter)->sum('commission_amount'),
            'total_payment_fees' => (float) Reservation::whereIn('status', $allActiveAndCompleted)->where($dateFilter)->sum('stripe_fee_amount'),
        ];
    }

    // === Section 2: Auto vs Manuel ===
    private function getFlowStats(): array
    {
        $autoCount = Reservation::where('flow_type', 'automatic')->count();
        $manualCount = Reservation::where('flow_type', 'manual')->count();
        $manualAccepted = Reservation::where('flow_type', 'manual')
            ->whereNotIn('status', [
                ReservationStatus::DECLINED_BY_HOST->value,
                ReservationStatus::CANCELLED_NO_RESPONSE->value,
                ReservationStatus::DRAFT->value,
                ReservationStatus::PENDING_HOST_RESPONSE->value,
            ])
            ->count();
        $manualDeclined = Reservation::where('status', ReservationStatus::DECLINED_BY_HOST)->count();
        $manualExpired = Reservation::where('status', ReservationStatus::CANCELLED_NO_RESPONSE)->count();

        return [
            'automatic_count' => $autoCount,
            'manual_count' => $manualCount,
            'automatic_amount' => (float) Reservation::where('flow_type', 'automatic')->sum('sous_total'),
            'manual_amount' => (float) Reservation::where('flow_type', 'manual')->sum('sous_total'),
            'acceptance_rate' => $manualCount > 0 ? round($manualAccepted / $manualCount * 100, 1) : 0,
            'declined_count' => $manualDeclined,
            'expired_count' => $manualExpired,
        ];
    }

    // === Section 3: Annulations ===
    private function getAnnulationsStats(): array
    {
        return [
            'guest_before_48h' => Reservation::where('status', ReservationStatus::CANCELLED_BY_GUEST_BEFORE_48H)->count(),
            'guest_after_48h' => Reservation::where('status', ReservationStatus::CANCELLED_BY_GUEST_AFTER_48H)->count(),
            'by_host' => Reservation::where('status', ReservationStatus::CANCELLED_BY_HOST)->count(),
            'no_response' => Reservation::where('status', ReservationStatus::CANCELLED_NO_RESPONSE)->count(),
            'total_cancelled' => Reservation::cancelled()->count(),
            'cancellation_rate' => Reservation::count() > 0
                ? round(Reservation::cancelled()->count() / Reservation::count() * 100, 1)
                : 0,
            'host_refunded_amount' => (float) Reservation::where('status', ReservationStatus::CANCELLED_BY_HOST)
                ->whereNotNull('refunded_at')
                ->sum('total_charged'),
        ];
    }

    // === Section 4: Remboursements ===
    private function getRefundStats(): array
    {
        $refundFailed = Reservation::where('status', ReservationStatus::REFUND_FAILED)->get();

        return [
            'total_refunds' => Reservation::where('status', ReservationStatus::REFUNDED)->count(),
            'total_refunded_amount' => (float) Reservation::where('status', ReservationStatus::REFUNDED)->sum('total_charged'),
            'refunded_by_host' => (float) Reservation::where('status', ReservationStatus::REFUNDED)
                ->where('cancelled_by', 'chef')
                ->sum('total_charged'),
            'refunded_by_guest' => (float) Reservation::where('status', ReservationStatus::REFUNDED)
                ->where('cancelled_by', 'client')
                ->sum('total_charged'),
            'refund_failed_count' => $refundFailed->count(),
            'refund_failed_reservations' => $refundFailed->pluck('id')->toArray(),
            'pending_refunds' => Reservation::where('status', ReservationStatus::REFUND_INITIATED)->count(),
        ];
    }

    // === Section 5: Paiements en attente ===
    private function getPendingPayments(): array
    {
        $pendingOtp = Reservation::where('status', ReservationStatus::COMPLETED_PENDING_OTP)->get();
        $expiredOtp = $pendingOtp->filter(fn($r) => $r->otp_deadline && now()->isAfter($r->otp_deadline));

        return [
            'pending_otp_count' => $pendingOtp->count(),
            'expired_otp_count' => $expiredOtp->count(),
            'pending_payout_count' => Reservation::pendingPayout()->count(),
            'captured_not_transferred' => Reservation::whereIn('status', [
                    ReservationStatus::CONFIRMED->value,
                    ReservationStatus::COMPLETED_PENDING_OTP->value,
                    ReservationStatus::COMPLETED_VALIDATED->value,
                    ReservationStatus::COMPLETED_AUTO_VALIDATED->value,
                ])
                ->where('payment_distributed', false)
                ->whereNotNull('payment_intent_id')
                ->count(),
        ];
    }

    // === Section 6: Comptes Stripe hotes ===
    private function getStripeAccountStats(): array
    {
        $hosts = User::where('role_id', 2);

        return [
            'total_created' => (clone $hosts)->whereNotNull('stripe_account_id')->count(),
            'activated' => (clone $hosts)->where('stripe_payouts_enabled', true)->count(),
            'pending_verification' => (clone $hosts)->whereNotNull('stripe_account_id')
                ->where('stripe_details_submitted', true)
                ->where('stripe_payouts_enabled', false)
                ->count(),
            'action_required' => (clone $hosts)->where('stripe_requirements_currently_due_count', '>', 0)->count(),
            'not_started' => (clone $hosts)->whereNull('stripe_account_id')->count(),
        ];
    }

    // === Section 7: Incidents ===
    private function getIncidentStats(): array
    {
        return [
            'open' => Incident::open()->count(),
            'resolved' => Incident::resolved()->count(),
            'disputes' => Incident::where('type', 'dispute')->open()->count(),
            'refund_failures' => Incident::where('type', 'refund_failed')->open()->count(),
            'recent_incidents' => Incident::open()
                ->with('reservation')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
        ];
    }

    // === Period date range helper ===
    private function getDateRange(string $period): array
    {
        return match ($period) {
            'day' => [
                Carbon::today()->toDateString(),
                Carbon::today()->toDateString(),
            ],
            'week' => [
                Carbon::now()->startOfWeek()->toDateString(),
                Carbon::now()->endOfWeek()->toDateString(),
            ],
            'all' => [null, null],
            default => [ // 'month'
                Carbon::now()->startOfMonth()->toDateString(),
                Carbon::now()->endOfMonth()->toDateString(),
            ],
        };
    }

    // === Section 8: Disputes (CDC 11.5) ===
    private function getDisputeStats(): array
    {
        $disputeCount = Incident::where('type', 'dispute')->count();
        $disputeOpen = Incident::where('type', 'dispute')->open()->count();

        $disputeReservationIds = Incident::where('type', 'dispute')
            ->whereNotNull('reservation_id')
            ->pluck('reservation_id');

        $disputeAmount = (float) Reservation::whereIn('id', $disputeReservationIds)->sum('total_charged');

        $totalReservations = Reservation::count();
        $disputeRate = $totalReservations > 0
            ? round($disputeCount / $totalReservations * 100, 2)
            : 0;

        return [
            'dispute_count' => $disputeCount,
            'dispute_open' => $disputeOpen,
            'dispute_amount' => $disputeAmount,
            'dispute_rate' => $disputeRate,
        ];
    }

    // === CSV Export ===
    public function exportCsv(Request $request): StreamedResponse
    {
        $period = $request->query('period', 'month');
        [$start, $end] = $this->getDateRange($period);

        $volumeStats = $this->getVolumeStats($start, $end);
        $flowStats = $this->getFlowStats();
        $annulationsStats = $this->getAnnulationsStats();
        $refundStats = $this->getRefundStats();
        $pendingPayments = $this->getPendingPayments();
        $stripeAccountStats = $this->getStripeAccountStats();
        $incidentStats = $this->getIncidentStats();
        $disputeStats = $this->getDisputeStats();

        $filename = 'dashboard_export_' . $period . '_' . now()->format('Y-m-d_His') . '.csv';

        return new StreamedResponse(function () use (
            $period, $volumeStats, $flowStats, $annulationsStats, $refundStats,
            $pendingPayments, $stripeAccountStats, $incidentStats, $disputeStats
        ) {
            $handle = fopen('php://output', 'w');

            // BOM for Excel UTF-8 compatibility
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, ['Dashboard Export - Period: ' . $period]);
            fputcsv($handle, ['Generated at', now()->toDateTimeString()]);
            fputcsv($handle, []);

            // Volume stats
            fputcsv($handle, ['=== Volume d\'activite ===']);
            foreach ($volumeStats as $key => $value) {
                fputcsv($handle, [$key, $value]);
            }
            fputcsv($handle, []);

            // Flow stats
            fputcsv($handle, ['=== Reservations auto vs manuelles ===']);
            foreach ($flowStats as $key => $value) {
                fputcsv($handle, [$key, $value]);
            }
            fputcsv($handle, []);

            // Cancellation stats
            fputcsv($handle, ['=== Annulations ===']);
            foreach ($annulationsStats as $key => $value) {
                fputcsv($handle, [$key, $value]);
            }
            fputcsv($handle, []);

            // Refund stats
            fputcsv($handle, ['=== Remboursements ===']);
            foreach ($refundStats as $key => $value) {
                if (is_array($value)) {
                    fputcsv($handle, [$key, implode(', ', $value)]);
                } else {
                    fputcsv($handle, [$key, $value]);
                }
            }
            fputcsv($handle, []);

            // Pending payments
            fputcsv($handle, ['=== Paiements en attente ===']);
            foreach ($pendingPayments as $key => $value) {
                fputcsv($handle, [$key, $value]);
            }
            fputcsv($handle, []);

            // Stripe accounts
            fputcsv($handle, ['=== Comptes Stripe hotes ===']);
            foreach ($stripeAccountStats as $key => $value) {
                fputcsv($handle, [$key, $value]);
            }
            fputcsv($handle, []);

            // Incidents
            fputcsv($handle, ['=== Incidents ===']);
            foreach ($incidentStats as $key => $value) {
                if ($key === 'recent_incidents') {
                    continue; // Skip collection in CSV
                }
                fputcsv($handle, [$key, $value]);
            }
            fputcsv($handle, []);

            // Disputes
            fputcsv($handle, ['=== Disputes ===']);
            foreach ($disputeStats as $key => $value) {
                fputcsv($handle, [$key, $value]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    // === Methodes utilitaires existantes ===

    private function countReservationsByDate(string $date): int
    {
        $activeStatuses = array_merge(
            array_map(fn($s) => $s->value, ReservationStatus::activeStatuses()),
            array_map(fn($s) => $s->value, ReservationStatus::completedStatuses())
        );

        return Reservation::whereIn('status', $activeStatuses)
            ->whereRaw("TO_DATE(SUBSTRING(date_prestation, 1, 10), 'YYYY-MM-DD') = ?", [$date])
            ->count();
    }

    private function countReservationsByDateRange(string $start, string $end): int
    {
        $activeStatuses = array_merge(
            array_map(fn($s) => $s->value, ReservationStatus::activeStatuses()),
            array_map(fn($s) => $s->value, ReservationStatus::completedStatuses())
        );

        return Reservation::whereIn('status', $activeStatuses)
            ->whereRaw("TO_DATE(SUBSTRING(date_prestation, 1, 10), 'YYYY-MM-DD') BETWEEN ? AND ?", [$start, $end])
            ->count();
    }

    private function sumConvivesByDate(string $date): int
    {
        $completedStatuses = array_map(fn($s) => $s->value, ReservationStatus::completedStatuses());

        return (int) Reservation::whereIn('status', $completedStatuses)
            ->whereRaw("TO_DATE(SUBSTRING(date_prestation, 1, 10), 'YYYY-MM-DD') = ?", [$date])
            ->sum('nombre_convive');
    }

    private function sumConvivesByDateRange(string $start, string $end): int
    {
        $completedStatuses = array_map(fn($s) => $s->value, ReservationStatus::completedStatuses());

        return (int) Reservation::whereIn('status', $completedStatuses)
            ->whereRaw("TO_DATE(SUBSTRING(date_prestation, 1, 10), 'YYYY-MM-DD') BETWEEN ? AND ?", [$start, $end])
            ->sum('nombre_convive');
    }

    private function sumCaByDateRange(string $start, string $end): float
    {
        $completedStatuses = array_map(fn($s) => $s->value, ReservationStatus::completedStatuses());

        return (float) Reservation::whereIn('status', $completedStatuses)
            ->whereRaw("TO_DATE(SUBSTRING(date_prestation, 1, 10), 'YYYY-MM-DD') BETWEEN ? AND ?", [$start, $end])
            ->sum('sous_total');
    }
}
