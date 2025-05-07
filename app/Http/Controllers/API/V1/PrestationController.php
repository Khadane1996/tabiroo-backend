<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Prestation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PrestationController extends Controller
{
    public function index()
    {
        $prestations = Prestation::with(['menus', 'typeDePlat'])->get();
        return response()->json($prestations);
    }

    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'type_de_plat' => 'required|exists:types_de_plat,id',
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

        $prestation = Prestation::create($request->only([
            'type_de_plat', 'start_time', 'end_time', 'date_limite',
            'heure_arrivee_convive', 'date_prestation'
        ]));

        $prestation->menus()->attach($request->menus);

        return response()->json([
            'status' => true,
            'message' => 'Prestation créée avec succès',
            'data' => $prestation->load(['menus', 'typeDePlat'])
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $prestation = Prestation::findOrFail($id);

        $validate = Validator::make($request->all(), [
            'type_de_plat' => 'sometimes|exists:types_de_plat,id',
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
            'type_de_plat', 'start_time', 'end_time', 'date_limite',
            'heure_arrivee_convive', 'date_prestation'
        ]));

        if ($request->has('menus')) {
            $prestation->menus()->sync($request->menus);
        }

        return response()->json([
            'status' => true,
            'message' => 'Prestation mise à jour avec succès',
            'data' => $prestation->load(['menus', 'typeDePlat'])
        ]);
    }

    public function destroy($id)
    {
        $prestation = Prestation::findOrFail($id);
        $prestation->menus()->detach();
        $prestation->delete();

        return response()->json(['message' => 'Prestation supprimée.']);
    }
}
