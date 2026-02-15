<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Prestation;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $postsCount = Post::count();
        $publishedCount = Post::where('status', 'published')->count();

        // === Indicateurs clés (logique alignée sur TableauBordController chef) ===
        $today = Carbon::today()->toDateString();
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        // Réservations (status completed, accepted, pending - hors cancelled)
        $reservationsToday = $this->countReservationsByDate($today);
        $reservationsThisMonth = $this->countReservationsByDateRange($startOfMonth, $endOfMonth);

        // Convives accueillis (reservations completed uniquement)
        $convivesToday = $this->sumConvivesByDate($today);
        $convivesThisMonth = $this->sumConvivesByDateRange($startOfMonth, $endOfMonth);

        // CA brut (sous_total des completed du mois)
        $caMoisEnCours = $this->sumCaByDateRange($startOfMonth, $endOfMonth);

        // Prestations à venir (prestations avec date_prestation >= aujourd'hui)
        $prestationsAVenir = Prestation::whereRaw("TO_DATE(SUBSTRING(date_prestation, 1, 10), 'YYYY-MM-DD') >= ?", [$today])
            ->count();

        // === Activité plateforme ===
        // Hôtes actifs (chefs avec prestations ou réservations complétées)
        $chefIdsAvecActivite = Reservation::where('status', 'completed')->pluck('chef_id')->unique()->filter();
        $hotesActifs = User::where('role_id', 2)
            ->where(function ($q) use ($chefIdsAvecActivite) {
                $q->whereHas('prestations')
                    ->orWhereIn('id', $chefIdsAvecActivite);
            })
            ->count();

        // Convives actifs (clients avec au moins 1 réservation completed)
        $convivesActifs = User::where('role_id', 3)
            ->whereIn('id', Reservation::where('status', 'completed')->pluck('client_id')->unique()->filter())
            ->count();

        // Prestations publiées (prestations avec date >= aujourd'hui et au moins 1 menu)
        $prestationsPubliees = Prestation::whereRaw("TO_DATE(SUBSTRING(date_prestation, 1, 10), 'YYYY-MM-DD') >= ?", [$today])
            ->whereHas('menus')
            ->count();

        // === Convives par jour (30 derniers jours) ===
        $convivesParJour = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i)->toDateString();
            $convivesParJour[] = [
                'date' => $date,
                'label' => Carbon::parse($date)->format('d/m'),
                'count' => $this->sumConvivesByDate($date),
            ];
        }

        // === Annulations (convives vs hôtes, avant/après 48h) ===
        $annulationsStats = $this->getAnnulationsStats();

        return view('admin.dashboard', [
            'postsCount' => $postsCount,
            'publishedCount' => $publishedCount,
            // Indicateurs clés
            'reservationsToday' => $reservationsToday,
            'reservationsThisMonth' => $reservationsThisMonth,
            'convivesToday' => $convivesToday,
            'convivesThisMonth' => $convivesThisMonth,
            'caMoisEnCours' => $caMoisEnCours,
            'prestationsAVenir' => $prestationsAVenir,
            // Activité plateforme
            'hotesActifs' => $hotesActifs,
            'convivesActifs' => $convivesActifs,
            'prestationsPubliees' => $prestationsPubliees,
            // Graphique
            'convivesParJour' => $convivesParJour,
            // Annulations
            'annulations' => $annulationsStats,
        ]);
    }

    private function countReservationsByDate(string $date): int
    {
        return Reservation::whereIn('status', ['completed', 'accepted', 'pending'])
            ->whereRaw("TO_DATE(SUBSTRING(date_prestation, 1, 10), 'YYYY-MM-DD') = ?", [$date])
            ->count();
    }

    private function countReservationsByDateRange(string $start, string $end): int
    {
        return Reservation::whereIn('status', ['completed', 'accepted', 'pending'])
            ->whereRaw("TO_DATE(SUBSTRING(date_prestation, 1, 10), 'YYYY-MM-DD') BETWEEN ? AND ?", [$start, $end])
            ->count();
    }

    private function sumConvivesByDate(string $date): int
    {
        return (int) Reservation::where('status', 'completed')
            ->whereRaw("TO_DATE(SUBSTRING(date_prestation, 1, 10), 'YYYY-MM-DD') = ?", [$date])
            ->sum('nombre_convive');
    }

    private function sumConvivesByDateRange(string $start, string $end): int
    {
        return (int) Reservation::where('status', 'completed')
            ->whereRaw("TO_DATE(SUBSTRING(date_prestation, 1, 10), 'YYYY-MM-DD') BETWEEN ? AND ?", [$start, $end])
            ->sum('nombre_convive');
    }

    private function sumCaByDateRange(string $start, string $end): float
    {
        return (float) Reservation::where('status', 'completed')
            ->whereRaw("TO_DATE(SUBSTRING(date_prestation, 1, 10), 'YYYY-MM-DD') BETWEEN ? AND ?", [$start, $end])
            ->sum('sous_total');
    }

    private function getAnnulationsStats(): array
    {
        $cancelled = Reservation::where('status', 'cancelled')->get();
        $conviveAvant48h = 0;
        $conviveApres48h = 0;
        $hoteAvant48h = 0;
        $hoteApres48h = 0;
        $autreAvant48h = 0;
        $autreApres48h = 0;

        foreach ($cancelled as $r) {
            $dateAnnul = $r->cancelled_at ?? $r->updated_at;
            if (!$dateAnnul) continue;
            $datePresta = null;
            if (preg_match('/^\d{4}-\d{2}-\d{2}/', (string) $r->date_prestation, $m)) {
                $datePresta = Carbon::parse($m[0])->startOfDay();
            }
            if (!$datePresta) continue;
            $heuresAvantPrestation = $dateAnnul->diffInHours($datePresta, false);
            $avant48h = $heuresAvantPrestation >= 48;

            $by = $r->cancelled_by ?? 'system';
            if ($by === 'client') {
                $avant48h ? $conviveAvant48h++ : $conviveApres48h++;
            } elseif ($by === 'chef') {
                $avant48h ? $hoteAvant48h++ : $hoteApres48h++;
            } else {
                $avant48h ? $autreAvant48h++ : $autreApres48h++;
            }
        }

        return [
            'convive_avant_48h' => $conviveAvant48h,
            'convive_apres_48h' => $conviveApres48h,
            'hote_avant_48h' => $hoteAvant48h,
            'hote_apres_48h' => $hoteApres48h,
            'autre_avant_48h' => $autreAvant48h,
            'autre_apres_48h' => $autreApres48h,
        ];
    }
}

