<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Plat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PlatController extends Controller
{
   
    public function index()
    {
        $user = Auth::user();
        $plats = Plat::with([
            'typeDePlat',
            'typeDeCuisine',
            'regimeAlimentaire',
            // 'themeCulinaire',
        ])
        ->where('user_id', $user->id)
        ->orderBy('id', 'desc')
        ->get();

        return response()->json(
            [
                'status' => true,
                'message' => 'Liste de mes plats',
                'data' => $plats
            ]);
    }

    public function store(Request $request)
    {
        try {

            $user = Auth::user();

            $validate = Validator::make(
                array_merge($request->all(), $request->allFiles()),
            [
                'nom' => 'required|string|max:255',
                'bioPlat' => 'required|string|max:255',
                'ingredient' => 'nullable|string',
                'allergene' => 'nullable|string',
                'type_de_plat_id' => 'nullable|exists:types_de_plat,id',
                'type_de_cuisine_id' => 'nullable|exists:types_de_cuisine,id',
                'regime_alimentaire_id' => 'nullable|exists:regimes_alimentaire,id',
                // 'theme_culinaire_id' => 'nullable|exists:themes_culinaire,id',
                'photo_url' => 'nullable|file|mimes:jpg,jpeg,png,heic|max:4096',
            ]);

            if($validate->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validate->errors()
                ], 401);
            }

            $fileName = null;
            if ($user->photo_url) {
                $oldPath = public_path('uploads/profiles/' . $user->photo_url);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            if ($request->hasFile('photo_url')) {
                $file = $request->file('photo_url');
                $fileName = time() . '_' . $file->getClientOriginalName();
                
                $path = public_path('uploads/plats');
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }

                $file->move($path, $fileName);
            }

            $plat = Plat::create([
                'user_id' => $user->id,
                'nom' => $request->nom,
                'bioPlat' => $request->bioPlat,
                'ingredient' => $request->ingredient,
                'allergene' => $request->allergene,
                'photo_url' => $fileName,
                'type_de_plat_id' => $request->type_de_plat_id,
                'type_de_cuisine_id' => $request->type_de_cuisine_id,
                'regime_alimentaire_id' => $request->regime_alimentaire_id,
                'theme_culinaire_id' => $request->theme_culinaire_id,
            ]);

            $data = Plat::with([
                'typeDePlat',
                'typeDeCuisine',
                'regimeAlimentaire',
                'themeCulinaire'
            ])->where('id', $plat->id)->first();

            return response()->json([
                'status' => true,
                'message' => 'Plat créé avec succès',
                'plat' => $data
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $plat = Plat::with([
                'typeDePlat',
                'typeDeCuisine',
                'regimeAlimentaire',
                // 'themeCulinaire'
            ])->find($id);

            if (!$plat) {
                return response()->json(['message' => 'Plat non trouvé'], 404);
            }

            return response()->json($plat);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $plat = Plat::find($id);

            if (!$plat) {
                return response()->json(['message' => 'Plat non trouvé'], 404);
            }

            $validate = Validator::make($request->all(), [
                'nom' => 'sometimes|required|string|max:255',
                'bioPlat' => 'sometimes|required|string|max:255',
                'ingredient' => 'nullable|string',
                'allergene' => 'nullable|string',
                'type_de_plat_id' => 'nullable|exists:types_de_plat,id',
                'type_de_cuisine_id' => 'nullable|exists:types_de_cuisine,id',
                'regime_alimentaire_id' => 'nullable|exists:regimes_alimentaire,id',
                // 'theme_culinaire_id' => 'nullable|exists:themes_culinaire,id',
                'photo_url' => 'nullable|file|mimes:jpg,jpeg,png,heic|max:4096',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validate->errors()
                ], 422);
            }

            if ($request->hasFile('photo_url')) {
                if ($plat->photo_url) {
                    $oldPath = public_path('uploads/plats/' . $plat->photo_url);
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }

                $file = $request->file('photo_url');
                $fileName = time() . '_' . $file->getClientOriginalName();

                $path = public_path('uploads/plats');
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }

                $file->move($path, $fileName);

                $plat->photo_url = $fileName;
            }

            $plat->update([
                'nom' => $request->nom ?? $plat->nom,
                'bioPlat' => $request->bioPlat ?? $plat->bioPlat,
                'ingredient' => $request->ingredient ?? $plat->ingredient,
                'allergene' => $request->allergene ?? $plat->allergene,
                'type_de_plat_id' => $request->type_de_plat_id ?? $plat->type_de_plat_id,
                'type_de_cuisine_id' => $request->type_de_cuisine_id ?? $plat->type_de_cuisine_id,
                'regime_alimentaire_id' => $request->regime_alimentaire_id ?? $plat->regime_alimentaire_id,
                // 'theme_culinaire_id' => $request->theme_culinaire_id ?? $plat->theme_culinaire_id,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Plat mis à jour avec succès',
                'plat' => $plat
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    public function destroy($id)
    {
        try {
            $plat = Plat::find($id);

            if (!$plat) {
                return response()->json(['message' => 'Plat non trouvé'], 404);
            }

            if ($plat->photo_url) {
                $imagePath = public_path('uploads/plats/' . $plat->photo_url);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $plat->delete();

            return response()->json([
                'status' => true,
                'message' => 'Plat supprimé avec succès'
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
