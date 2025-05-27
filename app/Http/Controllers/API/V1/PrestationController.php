<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Prestation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PrestationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $prestations = Prestation::with(['menus', 'typeDeRepas'])
            ->where('user_id', $user->id)
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Liste des prestations de l’utilisateur connecté',
            'data' => $prestations
        ]);
    }

    public function filterByDate(Request $request)
    {
        $request->validate([
            'date_prestation' => 'required|date'
        ]);

        $user = Auth::user();

        // Exemple : "2025-05-01T00:00:00.000Z" => "2025-05-01"
        $date = \Carbon\Carbon::parse($request->date_prestation)->toDateString();

        $prestations = Prestation::with(['menus', 'typeDeRepas'])
            ->where('user_id', $user->id)
            ->whereDate('date_prestation', $date)
            ->get();

        return response()->json([
            'status' => true,
            'message' => "Liste des prestations du $date",
            'data' => $prestations
        ]);
    }


    public function store(Request $request)
    {
        try {

            $validate = Validator::make($request->all(), [
                'type_de_repas' => 'required|exists:types_de_repas,id',
                'start_time' => 'required|string',
                'end_time' => 'required|string',
                'date_limite' => 'required|string',
                'heure_arrivee_convive' => 'required|string',
                'date_prestation' => 'required|string',
                'menus' => 'required|array',
                'menus.*' => 'exists:menus,id',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validate->errors()
                ], 401);
            }

            $user = Auth::user();

            $prestation = Prestation::create([
                'user_id' => $user->id,
                'type_de_repas' => $request->type_de_repas,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'date_limite' => $request->date_limite,
                'heure_arrivee_convive' => $request->heure_arrivee_convive,
                'date_prestation' => $request->date_prestation,
            ]);

            $prestation->menus()->attach($request->menus);

            $data = Prestation::with(['menus', 'typeDeRepas'])
                ->where('id', $prestation->id)
                ->get();
                
            return response()->json([
                'status' => true,
                'message' => 'Prestation créée avec succès',
                'prestation' => $data
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $prestation = Prestation::where('user_id', $user->id)->find($id);

        if (!$prestation) {
            return response()->json([
                'status' => false,
                'message' => 'Prestation introuvable ou non autorisée'
            ], 404);
        }

        $validate = Validator::make($request->all(), [
            'type_de_repas' => 'sometimes|exists:types_de_repas,id',
            'start_time' => 'sometimes|string',
            'end_time' => 'sometimes|string',
            'date_limite' => 'sometimes|string',
            'heure_arrivee_convive' => 'sometimes|string',
            'date_prestation' => 'sometimes|string',
            'menus' => 'sometimes|array',
            'menus.*' => 'exists:menus,id',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validate->errors()
            ], 401);
        }

        $prestation->update($request->only([
            'type_de_repas', 'start_time', 'end_time', 'date_limite',
            'heure_arrivee_convive', 'date_prestation'
        ]));

        if ($request->has('menus')) {
            $prestation->menus()->sync($request->menus);
        }

        return response()->json([
            'status' => true,
            'message' => 'Prestation mise à jour avec succès',
            'data' => $prestation->load(['menus', 'typeDeRepas'])
        ]);
    }

    public function show($id)
    {
        $user = Auth::user();
        $prestation = Prestation::with(['menus', 'typeDeRepas'])
            ->where('user_id', $user->id)
            ->find($id);

        if (!$prestation) {
            return response()->json([
                'status' => false,
                'message' => 'Prestation introuvable ou non autorisée'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Détails de la prestation',
            'data' => $prestation
        ]);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $prestation = Prestation::where('user_id', $user->id)->find($id);

        if (!$prestation) {
            return response()->json([
                'status' => false,
                'message' => 'Prestation introuvable ou non autorisée'
            ], 404);
        }

        $prestation->menus()->detach();
        $prestation->delete();

        return response()->json([
            'status' => true,
            'message' => 'Prestation supprimée avec succès'
        ]);
    }
}
