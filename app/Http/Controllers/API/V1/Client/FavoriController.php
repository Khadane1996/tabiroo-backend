<?php

namespace App\Http\Controllers\API\V1\Client;

use App\Http\Controllers\Controller;
use App\Models\Favori;
use App\Models\Menu;
use Illuminate\Http\Request;

class FavoriController extends Controller
{
    /**
     * GET /client/favoris
     * Returns all favorited menus for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $menus = Menu::with([
            'user:id,firstNameOrPseudo,lastName,photo_url,stripe_account_id',
            'user.adresse:id,user_id,latitude,longitude',
            'prestations.typeDeRepas',
            'prestations.reservationsConfirmées',
        ])
        ->withAvg('avisClients', 'note_client')
        ->whereHas('favoris', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->orderBy('id', 'desc')
        ->get();

        return response()->json([
            'status'  => true,
            'message' => 'Favoris de l\'utilisateur',
            'data'    => $menus,
        ]);
    }

    /**
     * GET /client/favoris/ids
     * Returns only the menu IDs favorited by the authenticated user (lightweight).
     */
    public function ids(Request $request)
    {
        $user = $request->user();

        $ids = Favori::where('user_id', $user->id)->pluck('menu_id');

        return response()->json([
            'status' => true,
            'data'   => $ids,
        ]);
    }

    /**
     * POST /client/favoris/{menu_id}/toggle
     * Add or remove a menu from the user's favorites.
     */
    public function toggle(Request $request, $menu_id)
    {
        $user = $request->user();

        $existing = Favori::where('user_id', $user->id)
            ->where('menu_id', $menu_id)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json([
                'status'    => true,
                'favori'    => false,
                'message'   => 'Retiré des favoris',
            ]);
        }

        Favori::create([
            'user_id' => $user->id,
            'menu_id' => $menu_id,
        ]);

        return response()->json([
            'status'  => true,
            'favori'  => true,
            'message' => 'Ajouté aux favoris',
        ]);
    }
}
