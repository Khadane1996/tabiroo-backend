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
        $prix       = $request->get('prix');

        $menusQuery = Menu::with([
            'user:id,firstNameOrPseudo,lastName,phone,email,biographie,photo_url',
            'prestations.typeDeRepas'
        ])
        ->whereHas('prestations');

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

    // public function index(Request $request)
    // {
    //     $perPage = $request->get('per_page', 10);
    //     $typeRepasIds = Arr::wrap($request->get('types_de_repas'));
    
    //     $menusQuery = Menu::with([
    //         'user:id,firstNameOrPseudo,lastName,phone,email,biographie,photo_url',
    //         'prestations.typeDeRepas'
    //     ])
    //     ->orderBy('id', 'desc');
    
    //     // Appliquer le filtre uniquement si on a des types_de_repas
    //     if (!empty($typeRepasIds)) {
    //         $menusQuery->whereHas('prestations', function ($query) use ($typeRepasIds) {
    //             $query->whereHas('typeDeRepas', function ($q) use ($typeRepasIds) {
    //                 $q->whereIn('id', $typeRepasIds);
    //             });
    //         })
    //         ->with(['prestations' => function ($query) use ($typeRepasIds) {
    //             $query->whereHas('typeDeRepas', function ($q) use ($typeRepasIds) {
    //                 $q->whereIn('id', $typeRepasIds);
    //             });
    //         }]);
    //     } else {
    //         // Si pas de filtre, charger simplement toutes les prestations
    //         $menusQuery->with('prestations');
    //     }
    
    //     $menus = $menusQuery->simplePaginate($perPage);
    
    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Liste des menus paginée',
    //         'data' => $menus,
    //     ]);
    // }

    public function mieuxNote(Request $request){

        $perPage = $request->get('per_page', 10);
        $typeRepasIds = Arr::wrap($request->get('types_de_repas'));
        $placeDisponible = $request->get('placeDisponible');

        $menusQuery = Menu::with([
            'user:id,firstNameOrPseudo,lastName,phone,email,biographie,photo_url',
            'prestations.typeDeRepas'
        ])
        ->whereHas('prestations')
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
