<?php

namespace App\Http\Controllers\API\V1\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\Prestation;

class AllPrestationController extends Controller
{
   public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        $menus = Menu::with(['user:id,firstNameOrPseudo,lastName,phone,email,biographie,photo_url','prestations.typeDeRepas'])
            ->has('prestations')
            ->orderBy('id', 'desc')
            ->simplePaginate($perPage);

        return response()->json([
            'status' => true,
            'message' => 'Liste des menus paginée',
            'data' => $menus
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
