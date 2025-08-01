<?php

namespace App\Http\Controllers\API\V1\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AvisClient;

class AvisClientController extends Controller
{

    public function index($menu_id)
    {
        $reservations = AvisClient::with('client')
        ->where('$menu_i', $menu_id)
        ->orderBy('id', 'desc')
        ->get();

        return response()->json([
            'status' => true,
            'message' => 'Liste de mes reservations',
            'data' => $reservations
        ]);
    }


    public function store(Request $request)
    {
        try {

            $validate = Validator::make($request->all(), [
                'menu_id' => 'required|exists:menus,id',
                'client_id' => 'required|exists:users,id',
                'note_client' => 'required'
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validate->errors()
                ], 401);
            }

            $avisClient = AvisClient::create([
                'menu_id' => $request->menu_id,
                'client_id' => $request->client_id,
                'note_client' => $request->note_client,
                'commentaire' => $request->commentaire
            ]);
                
            return response()->json([
                'status' => true,
                'message' => 'Avis client créé avec succès',
                'avisClient' => $avisClient
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
