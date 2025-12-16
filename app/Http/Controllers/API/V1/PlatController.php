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
            // relation historique (one-to-one)
            'regimeAlimentaire',
            // nouvelle relation many-to-many
            'regimesAlimentaires',
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

            // Normaliser le payload de régimes : on accepte soit regime_alimentaire_id (single),
            // soit regime_alimentaire_ids (array) pour le multi-select.
            $regimesIds = $request->input('regime_alimentaire_ids', []);
            if (!is_array($regimesIds)) {
                $regimesIds = $regimesIds !== null ? [$regimesIds] : [];
            }
            if (empty($regimesIds) && $request->filled('regime_alimentaire_id')) {
                $regimesIds = [$request->input('regime_alimentaire_id')];
            }

            $validate = Validator::make(
                array_merge($request->all(), $request->allFiles()),
            [
                'nom' => 'required|string|max:255',
                'bioPlat' => 'required|string|max:255',
                'ingredient' => 'nullable|string',
                'allergene' => 'nullable|string',
                'type_de_plat_id' => 'nullable|exists:types_de_plat,id',
                'type_de_cuisine_id' => 'nullable|exists:types_de_cuisine,id',
                // compatibilité : ancien champ single
                'regime_alimentaire_id' => 'nullable|exists:regimes_alimentaire,id',
                // nouveau champ multi
                'regime_alimentaire_ids' => 'nullable|array',
                'regime_alimentaire_ids.*' => 'exists:regimes_alimentaire,id',
                // 'theme_culinaire_id' => 'nullable|exists:themes_culinaire,id',
                'photo_url' => 'nullable|file|mimes:jpg,jpeg,png,heic|max:4096',
                'photo_url_2' => 'nullable|file|mimes:jpg,jpeg,png,heic|max:4096',
                'photo_url_3' => 'nullable|file|mimes:jpg,jpeg,png,heic|max:4096',
                'photo_url_4' => 'nullable|file|mimes:jpg,jpeg,png,heic|max:4096',
            ]);

            if($validate->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validate->errors()
                ], 401);
            }

            $photoFields = ['photo_url', 'photo_url_2', 'photo_url_3', 'photo_url_4'];
            $uploadedPhotos = [];
            foreach ($photoFields as $field) {
                if ($request->hasFile($field)) {
                    $uploadedPhotos[$field] = $this->uploadPlatPhoto($request->file($field));
                }
            }

            $plat = Plat::create([
                'user_id' => $user->id,
                'nom' => $request->nom,
                'bioPlat' => $request->bioPlat,
                'ingredient' => $request->ingredient,
                'allergene' => $request->allergene,
                'photo_url' => $uploadedPhotos['photo_url'] ?? null,
                'photo_url_2' => $uploadedPhotos['photo_url_2'] ?? null,
                'photo_url_3' => $uploadedPhotos['photo_url_3'] ?? null,
                'photo_url_4' => $uploadedPhotos['photo_url_4'] ?? null,
                'type_de_plat_id' => $request->type_de_plat_id,
                'type_de_cuisine_id' => $request->type_de_cuisine_id,
                // pour compatibilité, on conserve le premier régime (s'il existe) dans la colonne historique
                'regime_alimentaire_id' => !empty($regimesIds) ? $regimesIds[0] : null,
                'theme_culinaire_id' => $request->theme_culinaire_id,
            ]);

            // Attacher les régimes multiples dans la table pivot
            if (!empty($regimesIds)) {
                $plat->regimesAlimentaires()->sync($regimesIds);
            }

            $data = Plat::with([
                'typeDePlat',
                'typeDeCuisine',
                'regimeAlimentaire',
                'regimesAlimentaires',
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
                'regimesAlimentaires',
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

            // Normaliser le payload de régimes : single ou multiple
            $regimesIds = $request->input('regime_alimentaire_ids', null);
            if (!is_null($regimesIds) && !is_array($regimesIds)) {
                $regimesIds = [$regimesIds];
            }
            if (is_null($regimesIds) && $request->filled('regime_alimentaire_id')) {
                $regimesIds = [$request->input('regime_alimentaire_id')];
            }

            $validate = Validator::make($request->all(), [
                'nom' => 'sometimes|required|string|max:255',
                'bioPlat' => 'sometimes|required|string|max:255',
                'ingredient' => 'nullable|string',
                'allergene' => 'nullable|string',
                'type_de_plat_id' => 'nullable|exists:types_de_plat,id',
                'type_de_cuisine_id' => 'nullable|exists:types_de_cuisine,id',
                // compatibilité : ancien champ single
                'regime_alimentaire_id' => 'nullable|exists:regimes_alimentaire,id',
                // nouveau champ multi
                'regime_alimentaire_ids' => 'nullable|array',
                'regime_alimentaire_ids.*' => 'exists:regimes_alimentaire,id',
                // 'theme_culinaire_id' => 'nullable|exists:themes_culinaire,id',
                'photo_url' => 'nullable|file|mimes:jpg,jpeg,png,heic|max:4096',
                'photo_url_2' => 'nullable|file|mimes:jpg,jpeg,png,heic|max:4096',
                'photo_url_3' => 'nullable|file|mimes:jpg,jpeg,png,heic|max:4096',
                'photo_url_4' => 'nullable|file|mimes:jpg,jpeg,png,heic|max:4096',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validate->errors()
                ], 422);
            }

            $updateData = [
                'nom' => $request->nom ?? $plat->nom,
                'bioPlat' => $request->bioPlat ?? $plat->bioPlat,
                'ingredient' => $request->ingredient ?? $plat->ingredient,
                'allergene' => $request->allergene ?? $plat->allergene,
                'type_de_plat_id' => $request->type_de_plat_id ?? $plat->type_de_plat_id,
                'type_de_cuisine_id' => $request->type_de_cuisine_id ?? $plat->type_de_cuisine_id,
                // on garde une valeur "principale" pour compatibilité
                'regime_alimentaire_id' => is_array($regimesIds) && !empty($regimesIds)
                    ? $regimesIds[0]
                    : ($request->regime_alimentaire_id ?? $plat->regime_alimentaire_id),
                // 'theme_culinaire_id' => $request->theme_culinaire_id ?? $plat->theme_culinaire_id,
            ];

            $photoFields = ['photo_url', 'photo_url_2', 'photo_url_3', 'photo_url_4'];
            foreach ($photoFields as $field) {
                if ($request->hasFile($field)) {
                    if ($plat->$field) {
                        $oldPath = public_path('uploads/plats/' . $plat->$field);
                        if (file_exists($oldPath)) {
                            unlink($oldPath);
                        }
                    }
                    $updateData[$field] = $this->uploadPlatPhoto($request->file($field));
                }
            }

            $removePhotos = $request->input('remove_photos', []);
            if (!is_array($removePhotos)) {
                $removePhotos = [$removePhotos];
            }
            foreach ($removePhotos as $field) {
                if (in_array($field, $photoFields, true) && $plat->$field) {
                    $oldPath = public_path('uploads/plats/' . $plat->$field);
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                    $updateData[$field] = null;
                }
            }

            $plat->update($updateData);

            // Mettre à jour les régimes multiples si fournis
            if (is_array($regimesIds)) {
                $plat->regimesAlimentaires()->sync($regimesIds);
            }

            $plat->load(['typeDePlat', 'typeDeCuisine', 'regimeAlimentaire', 'regimesAlimentaires']);

            return response()->json([
                'status' => true,
                'message' => 'Plat mis à jour avec succès',
                'plat' => $plat,
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    private function uploadPlatPhoto($file): string
    {
        $path = public_path('uploads/plats');
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $fileName = time() . '_' . $file->getClientOriginalName();
        $file->move($path, $fileName);

        return $fileName;
    }


    public function destroy($id)
    {
        try {
            $plat = Plat::find($id);

            if (!$plat) {
                return response()->json(['message' => 'Plat non trouvé'], 404);
            }

            $photoFields = ['photo_url', 'photo_url_2', 'photo_url_3', 'photo_url_4'];
            foreach ($photoFields as $field) {
                if ($plat->$field) {
                    $imagePath = public_path('uploads/plats/' . $plat->$field);
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
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
