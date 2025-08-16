<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\Menu;
use App\Models\Plat;
use App\Models\AvisClient;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TableauBordController extends Controller
{
     public function index($user_id)
    {
        $countPending = Reservation::where('chef_id', $user_id)
        ->where('status', 'pending')
        ->count();

        $now = Carbon::now();

        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        $totalRevenue = Reservation::where('chef_id', $user_id)
            ->where('status', 'completed')
            ->whereRaw("TO_DATE(SUBSTRING(date_prestation, 1, 10), 'YYYY-MM-DD') BETWEEN ? AND ?", [
                $startOfMonth,
                $endOfMonth
            ])
            ->sum('sous_total');

        $averageNote = DB::table('avis_clients')
            ->whereNotNull('menu_id')
            ->join('menus', 'avis_clients.menu_id', '=', 'menus.id')
            ->where('menus.user_id', $user_id)
            ->avg('avis_clients.note_client');

        $noteFormatted = $averageNote ? round($averageNote, 1) . '/5' : '0/5';

        // Calculer le menu best-seller (le plus réservé)
        $bestSellerMenu = DB::table('reservations')
            ->join('menu_prestation', 'reservations.menu_prestation_id', '=', 'menu_prestation.id')
            ->join('menus', 'menu_prestation.menu_id', '=', 'menus.id')
            ->where('menus.user_id', $user_id)
            ->where('reservations.status', '!=', 'cancelled')
            ->select('menus.nom', 'menus.id', DB::raw('COUNT(*) as reservation_count'))
            ->groupBy('menus.id', 'menus.nom')
            ->orderByDesc('reservation_count')
            ->first();

        $bestSellerData = $bestSellerMenu ? [
            'nom' => $bestSellerMenu->nom,
            'count' => $bestSellerMenu->reservation_count
        ] : [
            'nom' => 'Aucun menu',
            'count' => 0
        ];

        return response()->json([
        'status' => true,
        'message' => 'Données du tableau de bord',
            'data' => [
                'pending' => $countPending,
                'totalRevenue' => $totalRevenue,
                'noteClient' => $noteFormatted,
                'bestSellerMenu' => $bestSellerData
            ]
        ]);
    }

    /**
     * Récupérer les détails du menu best-seller avec ses plats
     */
    public function getBestSellerDetails($user_id)
    {
        // Récupérer le menu le plus réservé avec la même logique que index()
        $bestSellerMenu = DB::table('reservations')
            ->join('menu_prestation', 'reservations.menu_prestation_id', '=', 'menu_prestation.id')
            ->join('menus', 'menu_prestation.menu_id', '=', 'menus.id')
            ->where('menus.user_id', $user_id)
            ->where('reservations.status', '!=', 'cancelled')
            ->select('menus.nom', 'menus.id', DB::raw('COUNT(*) as reservation_count'))
            ->groupBy('menus.id', 'menus.nom')
            ->orderByDesc('reservation_count')
            ->first();

        if (!$bestSellerMenu) {
            return response()->json([
                'status' => true,
                'message' => 'Aucun menu best-seller trouvé',
                'data' => null
            ]);
        }

        // Récupérer les détails complets du menu
        $menuDetails = Menu::find($bestSellerMenu->id);
        
        // Récupérer les plats du menu
        $plats = DB::table('menu_plat')
            ->join('plats', 'menu_plat.plat_id', '=', 'plats.id')
            ->where('menu_plat.menu_id', $bestSellerMenu->id)
            ->select('plats.nom')
            ->get()
            ->pluck('nom')
            ->toArray();

        return response()->json([
            'status' => true,
            'message' => 'Détails du menu best-seller',
            'data' => [
                'id' => $bestSellerMenu->id,
                'nom' => $bestSellerMenu->nom,
                'bioMenu' => $menuDetails ? $menuDetails->bioMenu : '',
                'photo_url' => $menuDetails ? $menuDetails->photo_url : '',
                'prix' => $menuDetails ? $menuDetails->prix : 0,
                'reservation_count' => $bestSellerMenu->reservation_count,
                'plats' => $plats
            ]
        ]);
    }

    /**
     * Récupérer les menus à la carte (avec prestations)
     */
    public function getMenusAlaCarte($user_id)
    {
        $menus = Menu::where('user_id', $user_id)
            ->whereHas('prestations') // Menus qui ont des prestations (à la carte)
            ->with(['plats:id,nom', 'prestations:id,type_de_repas'])
            ->get()
            ->map(function ($menu) {
                return [
                    'id' => $menu->id,
                    'nom' => $menu->nom,
                    'bioMenu' => $menu->bioMenu,
                    'photo_url' => $menu->photo_url,
                    'prix' => $menu->prix,
                    'plats' => $menu->plats->pluck('nom')->toArray(),
                    'type' => 'Plats' // Type par défaut
                ];
            });

        return response()->json([
            'status' => true,
            'message' => 'Menus à la carte',
            'data' => [
                'count' => $menus->count(),
                'menus' => $menus
            ]
        ]);
    }

    /**
     * Récupérer les menus non commercialisés (sans prestations)
     */
    public function getMenusNonCommercialises($user_id)
    {
        $menus = Menu::where('user_id', $user_id)
            ->whereDoesntHave('prestations') // Menus qui n'ont pas de prestations
            ->with(['plats:id,nom'])
            ->get()
            ->map(function ($menu) {
                return [
                    'id' => $menu->id,
                    'nom' => $menu->nom,
                    'bioMenu' => $menu->bioMenu,
                    'photo_url' => $menu->photo_url,
                    'prix' => $menu->prix,
                    'plats' => $menu->plats->pluck('nom')->toArray(),
                    'type' => 'Plats' // Type par défaut
                ];
            });

        return response()->json([
            'status' => true,
            'message' => 'Menus non commercialisés',
            'data' => [
                'count' => $menus->count(),
                'menus' => $menus
            ]
        ]);
    }

    /**
     * Debug - Vérifier les données de best-seller
     */
    public function debugBestSeller($user_id)
    {
        // Données depuis index()
        $bestSellerFromIndex = DB::table('reservations')
            ->join('menu_prestation', 'reservations.menu_prestation_id', '=', 'menu_prestation.id')
            ->join('menus', 'menu_prestation.menu_id', '=', 'menus.id')
            ->where('menus.user_id', $user_id)
            ->where('reservations.status', '!=', 'cancelled')
            ->select('menus.nom', 'menus.id', DB::raw('COUNT(*) as reservation_count'))
            ->groupBy('menus.id', 'menus.nom')
            ->orderByDesc('reservation_count')
            ->first();

        // Compter toutes les réservations pour ce chef
        $totalReservations = DB::table('reservations')
            ->join('menu_prestation', 'reservations.menu_prestation_id', '=', 'menu_prestation.id')
            ->join('menus', 'menu_prestation.menu_id', '=', 'menus.id')
            ->where('menus.user_id', $user_id)
            ->count();

        // Compter les réservations non annulées
        $activeReservations = DB::table('reservations')
            ->join('menu_prestation', 'reservations.menu_prestation_id', '=', 'menu_prestation.id')
            ->join('menus', 'menu_prestation.menu_id', '=', 'menus.id')
            ->where('menus.user_id', $user_id)
            ->where('reservations.status', '!=', 'cancelled')
            ->count();

        // Lister tous les menus du chef
        $allMenus = Menu::where('user_id', $user_id)->count();

        return response()->json([
            'status' => true,
            'message' => 'Debug best-seller',
            'data' => [
                'bestSellerFromIndex' => $bestSellerFromIndex,
                'totalReservations' => $totalReservations,
                'activeReservations' => $activeReservations,
                'allMenusCount' => $allMenus,
                'user_id' => $user_id
            ]
        ]);
    }

    /**
     * Récupérer les détails des notes clients
     */
    public function getNotesClientsDetails($user_id)
    {
        // 1. Note moyenne (même logique que HomeScreen)
        $averageNote = DB::table('avis_clients')
            ->whereNotNull('menu_id')
            ->join('menus', 'avis_clients.menu_id', '=', 'menus.id')
            ->where('menus.user_id', $user_id)
            ->avg('avis_clients.note_client');

        $noteFormatted = $averageNote ? round($averageNote, 1) . '/5' : '0/5';

        // 2. Nombre d'avis clients
        $reviewCount = DB::table('avis_clients')
            ->join('menus', 'avis_clients.menu_id', '=', 'menus.id')
            ->where('menus.user_id', $user_id)
            ->count();

        // 3. Nombre de convives servis (depuis les réservations completed)
        $guestCount = DB::table('reservations')
            ->join('menu_prestation', 'reservations.menu_prestation_id', '=', 'menu_prestation.id')
            ->join('menus', 'menu_prestation.menu_id', '=', 'menus.id')
            ->where('menus.user_id', $user_id)
            ->where('reservations.status', 'completed')
            ->sum('reservations.nombre_convive');

        // 4. Taux de satisfaction (avis >= 4/5)
        $satisfiedReviews = DB::table('avis_clients')
            ->join('menus', 'avis_clients.menu_id', '=', 'menus.id')
            ->where('menus.user_id', $user_id)
            ->where('avis_clients.note_client', '>=', 4)
            ->count();

        $satisfactionRate = $reviewCount > 0 ? round(($satisfiedReviews / $reviewCount) * 100) . '%' : '0%';

        // 5. Liste des avis détaillés (les 10 plus récents)
        $reviews = DB::table('avis_clients')
            ->join('menus', 'avis_clients.menu_id', '=', 'menus.id')
            ->join('users', 'avis_clients.client_id', '=', 'users.id')
            ->where('menus.user_id', $user_id)
            ->select(
                'avis_clients.id',
                'avis_clients.note_client',
                'avis_clients.commentaire',
                'avis_clients.created_at',
                'users.firstNameOrPseudo as client_name',
                'users.photo_url as client_avatar',
                'menus.nom as menu_name'
            )
            ->orderBy('avis_clients.created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($review) {
                return [
                    'id' => $review->id,
                    'client_name' => $review->client_name,
                    'rating' => number_format($review->note_client, 1),
                    'comment' => $review->commentaire ?: 'Aucun commentaire',
                    'menu_name' => $review->menu_name,
                    'date' => $review->created_at,
                    'avatar' => $review->client_avatar
                ];
            });

        return response()->json([
            'status' => true,
            'message' => 'Détails des notes clients',
            'data' => [
                'average_rating' => $noteFormatted,
                'review_count' => $reviewCount,
                'guest_count' => $guestCount ?: 0,
                'satisfaction_rate' => $satisfactionRate,
                'reviews' => $reviews
            ]
        ]);
    }

    /**
     * Récupérer les données de CA (Chiffre d'Affaires) du mois
     */
    public function getCaDetails($user_id)
    {
        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear = $now->year;
        
        // 1. Revenus des prestations réalisées (completed) du mois actuel
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();
        
        $revenuRealisees = DB::table('reservations')
            ->join('menu_prestation', 'reservations.menu_prestation_id', '=', 'menu_prestation.id')
            ->join('menus', 'menu_prestation.menu_id', '=', 'menus.id')
            ->where('menus.user_id', $user_id)
            ->where('reservations.status', 'completed')
            ->whereRaw("TO_DATE(SUBSTRING(reservations.date_prestation, 1, 10), 'YYYY-MM-DD') BETWEEN ? AND ?", [
                $startOfMonth,
                $endOfMonth
            ])
            ->sum('reservations.sous_total');

        // 2. Revenus des prestations à venir (confirmed/pending) du mois actuel et futur
        $revenuAVenir = DB::table('reservations')
            ->join('menu_prestation', 'reservations.menu_prestation_id', '=', 'menu_prestation.id')
            ->join('menus', 'menu_prestation.menu_id', '=', 'menus.id')
            ->where('menus.user_id', $user_id)
            ->whereIn('reservations.status', ['confirmed', 'pending'])
            ->whereRaw("TO_DATE(SUBSTRING(reservations.date_prestation, 1, 10), 'YYYY-MM-DD') >= ?", [
                $startOfMonth
            ])
            ->sum('reservations.sous_total');

        // 3. Évolution du CA sur les 5 derniers mois (incluant le mois actuel)
        $evolutionCa = [];
        $moisNoms = ['JAN', 'FEV', 'MAR', 'AVR', 'MAI', 'JUN', 'JUL', 'AOU', 'SEP', 'OCT', 'NOV', 'DEC'];
        
        for ($i = 4; $i >= 0; $i--) {
            $dateCalcul = Carbon::now()->subMonths($i);
            $startMois = $dateCalcul->copy()->startOfMonth()->toDateString();
            $endMois = $dateCalcul->copy()->endOfMonth()->toDateString();
            
            $caRealise = DB::table('reservations')
                ->join('menu_prestation', 'reservations.menu_prestation_id', '=', 'menu_prestation.id')
                ->join('menus', 'menu_prestation.menu_id', '=', 'menus.id')
                ->where('menus.user_id', $user_id)
                ->where('reservations.status', 'completed')
                ->whereRaw("TO_DATE(SUBSTRING(reservations.date_prestation, 1, 10), 'YYYY-MM-DD') BETWEEN ? AND ?", [
                    $startMois,
                    $endMois
                ])
                ->sum('reservations.sous_total');
            
            // Pour le mois actuel, ajouter les prévisions
            $caPrevisions = 0;
            if ($i === 0) { // Mois actuel
                $caPrevisions = DB::table('reservations')
                    ->join('menu_prestation', 'reservations.menu_prestation_id', '=', 'menu_prestation.id')
                    ->join('menus', 'menu_prestation.menu_id', '=', 'menus.id')
                    ->where('menus.user_id', $user_id)
                    ->whereIn('reservations.status', ['confirmed', 'pending'])
                    ->whereRaw("TO_DATE(SUBSTRING(reservations.date_prestation, 1, 10), 'YYYY-MM-DD') BETWEEN ? AND ?", [
                        $startMois,
                        $endMois
                    ])
                    ->sum('reservations.sous_total');
            }
            
            $evolutionCa[] = [
                'mois' => $moisNoms[$dateCalcul->month - 1],
                'ca_realise' => (float) ($caRealise ?: 0),
                'ca_previsions' => (float) ($caPrevisions ?: 0),
                'total' => (float) (($caRealise ?: 0) + ($caPrevisions ?: 0))
            ];
        }

        // 4. Nombre de menus vendus ce mois
        $menusVendus = DB::table('reservations')
            ->join('menu_prestation', 'reservations.menu_prestation_id', '=', 'menu_prestation.id')
            ->join('menus', 'menu_prestation.menu_id', '=', 'menus.id')
            ->where('menus.user_id', $user_id)
            ->where('reservations.status', 'completed')
            ->whereRaw("TO_DATE(SUBSTRING(reservations.date_prestation, 1, 10), 'YYYY-MM-DD') BETWEEN ? AND ?", [
                $startOfMonth,
                $endOfMonth
            ])
            ->count();

        // 5. Classement des menus par ventes
        $classementMenus = DB::table('reservations')
            ->join('menu_prestation', 'reservations.menu_prestation_id', '=', 'menu_prestation.id')
            ->join('menus', 'menu_prestation.menu_id', '=', 'menus.id')
            ->where('menus.user_id', $user_id)
            ->where('reservations.status', 'completed')
            ->whereRaw("TO_DATE(SUBSTRING(reservations.date_prestation, 1, 10), 'YYYY-MM-DD') BETWEEN ? AND ?", [
                $startOfMonth,
                $endOfMonth
            ])
            ->select('menus.nom', DB::raw('COUNT(*) as ventes'), DB::raw('SUM(reservations.sous_total) as ca_total'))
            ->groupBy('menus.id', 'menus.nom')
            ->orderByDesc('ventes')
            ->limit(10)
            ->get()
            ->map(function ($menu) {
                return [
                    'nom' => $menu->nom,
                    'ventes' => (int) $menu->ventes,
                    'ca_total' => (float) $menu->ca_total
                ];
            });

        // 6. Liste des prestations à venir pour la modal
        $prestationsAVenir = DB::table('reservations')
            ->join('menu_prestation', 'reservations.menu_prestation_id', '=', 'menu_prestation.id')
            ->join('menus', 'menu_prestation.menu_id', '=', 'menus.id')
            ->join('prestations', 'menu_prestation.prestation_id', '=', 'prestations.id')
            ->where('menus.user_id', $user_id)
            ->whereIn('reservations.status', ['confirmed', 'pending'])
            ->whereRaw("TO_DATE(SUBSTRING(reservations.date_prestation, 1, 10), 'YYYY-MM-DD') >= ?", [
                $startOfMonth
            ])
            ->select(
                'reservations.id',
                'reservations.date_prestation',
                'reservations.sous_total',
                'menus.nom as menu_nom',
                'prestations.date_prestation as prestation_date'
            )
            ->orderBy('reservations.date_prestation')
            ->limit(10)
            ->get()
            ->map(function ($prestation) {
                return [
                    'id' => $prestation->id,
                    'date' => $prestation->date_prestation,
                    'menu' => $prestation->menu_nom,
                    'amount' => (float) $prestation->sous_total
                ];
            });

        return response()->json([
            'status' => true,
            'message' => 'Données CA du mois',
            'data' => [
                'revenus_realises' => (float) ($revenuRealisees ?: 0),
                'revenus_a_venir' => (float) ($revenuAVenir ?: 0),
                'evolution_ca' => $evolutionCa,
                'menus_vendus' => (int) $menusVendus,
                'classement_menus' => $classementMenus,
                'prestations_a_venir' => $prestationsAVenir
            ]
        ]);
    }
}
