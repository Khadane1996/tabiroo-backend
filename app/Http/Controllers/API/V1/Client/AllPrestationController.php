<?php

namespace App\Http\Controllers\API\V1\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\Prestation;
use Illuminate\Support\Arr;


class AllPrestationController extends Controller
{

    public function index(Request $request)
    {
        $perPage         = $request->get('per_page', 10);
        $typeRepasIds    = Arr::wrap($request->get('types_de_repas'));
        $placeDisponible = $request->get('placeDisponible');
        $mieuxNote       = $request->get('mieuxNote');
        $prix            = $request->get('prix');
        $typeCuisineIds  = Arr::wrap($request->get('types_cuisine'));
        $regimeIds       = Arr::wrap($request->get('regimes_alimentaire'));

        $menusQuery = Menu::with([
            'user:id,firstNameOrPseudo,lastName,phone,email,biographie,photo_url,stripe_account_id',
            'user.adresse:id,user_id,latitude,longitude', // <-- ajout ici
            'prestations.typeDeRepas',
            'prestations.reservationsConfirmées'
        ])
        ->whereHas('prestations')
        // Filtrer uniquement les menus des chefs avec compte Stripe configuré
        ->whereHas('user', function ($q) {
            $q->whereNotNull('stripe_account_id');
        });

        // Si on filtre par type de repas
        if (!empty($typeRepasIds)) {
            $menusQuery->whereHas('prestations', function ($query) use ($typeRepasIds) {
                $query->whereHas('typeDeRepas', function ($q) use ($typeRepasIds) {
                    $q->whereIn('id', $typeRepasIds);
                });
            });
        }

        // Si on veut trier par note moyenne
        $menusQuery->withAvg('avisClients', 'note_client');   
        // if ($mieuxNote) {
        //     $menusQuery
        //         ->withAvg('avisClients', 'note_client')
        //         ->orderByDesc('avis_clients_avg_note_client');
        // }

        // Filtre places disponibles
        if (!is_null($placeDisponible)) {
            $menusQuery->whereHas('prestations', function ($q) use ($placeDisponible) {
                $q->whereRaw('
                    prestations.nombre_convive - COALESCE((
                        SELECT SUM(r.nombre_convive)
                        FROM reservations r
                        JOIN menu_prestation mp ON mp.id = r.menu_prestation_id
                        WHERE mp.prestation_id = prestations.id
                        AND mp.menu_id = menus.id
                    ), 0) >= ?
                ', [(int) $placeDisponible]);
            });
        }

        // Filtre par type de cuisine
        if (!empty($typeCuisineIds)) {
            $menusQuery->whereHas('plats', function ($q) use ($typeCuisineIds) {
                $q->whereIn('type_de_cuisine_id', $typeCuisineIds);
            });
        }

        // Filtre par régime alimentaire
        if (!empty($regimeIds)) {
            $menusQuery->whereHas('plats', function ($q) use ($regimeIds) {
                $q->whereIn('regime_alimentaire_id', $regimeIds);
            });
        }

        if ($prix) {
            $menusQuery->orderBy('prix', 'asc');
        } else {
            $menusQuery->orderBy('id', 'desc');
        }

        // Pagination
        $menus = $menusQuery->simplePaginate($perPage);

        return response()->json([
            'status'  => true,
            'message' => 'Liste des menus',
            'data'    => $menus,
        ]);
    }

    public function mieuxNote(Request $request){

        $perPage = $request->get('per_page', 10);
        $typeRepasIds = Arr::wrap($request->get('types_de_repas'));
        $placeDisponible = $request->get('placeDisponible');

        $menusQuery = Menu::with([
            'user:id,firstNameOrPseudo,lastName,phone,email,biographie,photo_url,stripe_account_id',
            'user.adresse:id,user_id,latitude,longitude', // <-- ajout ici
            'prestations.typeDeRepas',
            'prestations.reservationsConfirmées'
        ])
        ->whereHas('prestations')
        // Filtrer uniquement les menus des chefs avec compte Stripe configuré
        ->whereHas('user', function ($q) {
            $q->whereNotNull('stripe_account_id');
        })
        ->withAvg('avisClients', 'note_client')  // Calcul de la moyenne des notes
        ->orderBy('avis_clients_avg_note_client', 'asc')  // Tri par moyenne décroissante
        ->orderBy('id', 'desc');

        if (!empty($typeRepasIds)) {
            $menusQuery->whereHas('prestations', function ($query) use ($typeRepasIds) {
                $query->whereHas('typeDeRepas', function ($q) use ($typeRepasIds) {
                    $q->whereIn('id', $typeRepasIds);
                });
            });
        }

        // filtre places disponibles (uniquement si placeDisponible est fourni)
        if (!is_null($placeDisponible)) {
            $menusQuery->whereHas('prestations', function ($q) use ($placeDisponible) {
                // On calcule : prestations.nombre_convive - SUM(reservations.nombre_convive pour ce menu_prestation)
                $q->whereRaw('
                    prestations.nombre_convive - COALESCE((
                        SELECT SUM(r.nombre_convive)
                        FROM reservations r
                        JOIN menu_prestation mp ON mp.id = r.menu_prestation_id
                        WHERE mp.prestation_id = prestations.id
                        AND mp.menu_id = menus.id
                    ), 0) >= ?
                ', [(int) $placeDisponible]);
            });
        }

        $menus = $menusQuery->simplePaginate($perPage);

        return response()->json([
            'status' => true,
            'message' => 'Liste des menus triés par note moyenne',
            'data' => $menus,
        ]);
    }

    public function getPlats($id)
    {
        $prestation = Prestation::with('menus.plats')->find($id);

        if (!$prestation) {
            return response()->json([
                'status' => false,
                'message' => 'Prestation non trouvée',
            ], 404);
        }

        $plats = $prestation->menus->flatMap(function ($menu) {
            return $menu->plats;
        })->unique('id')->values();

        return response()->json([
            'status' => true,
            'message' => 'Liste des plats pour la prestation',
            'data' => $plats,
        ]);
    }

}
