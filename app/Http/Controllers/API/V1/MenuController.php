<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;


class MenuController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $menus = Menu::with('plats')
        ->where('user_id', $user->id)
        ->orderBy('id', 'desc')
        ->get();

        return response()->json([
            'status' => true,
            'message' => 'Liste de mes menus',
            'data' => $menus
        ]);
    }

    // Afficher un menu spécifique
    public function show($id)
    {
        $menu = Menu::with('plats')->find($id);

        if (!$menu) {
            return response()->json(['message' => 'Menu non trouvé'], 404);
        }

        return response()->json([
            'status' => true,
            'menu' => $menu
        ]);
    }

    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            $validate = Validator::make($request->all(), [
                'nom' => 'required|string|max:255',
                'bioMenu' => 'required|string|max:255',
                'prix' => 'required|string|max:255',
                'photo_url' => 'nullable|file|mimes:jpg,jpeg,png,heic|max:4096',
                'plat_ids' => 'nullable|array',
                'plat_ids.*' => 'exists:plats,id',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validate->errors()
                ], 401);
            }

            $menu = Menu::create([
                'user_id' => $user->id,
                'nom' => $request->nom,
                'bioMenu' => $request->bioMenu,
                'prix' => $request->prix,
                'photo_url' => $request->photo_url ? $this->uploadPhoto($request->file('photo_url')) : null,
            ]);

            if ($request->has('plat_ids')) {
                $menu->plats()->sync($request->plat_ids);
            }

            $data = Menu::with([
                'plats'
            ])->where('id', $menu->id)->first();

            return response()->json([
                'status' => true,
                'message' => 'Menu créé avec succès',
                'menu' => $data
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    // Mettre à jour un menu
    public function update(Request $request, $id)
    {
        try {
            $menu = Menu::find($id);
            if (!$menu) {
                return response()->json(['message' => 'Menu non trouvé'], 404);
            }

            $validate = Validator::make($request->all(), [
                'nom' => 'required|string|max:255',
                'bioMenu' => 'required|string|max:255',
                'prix' => 'required|string|max:255',
                'photo_url' => 'nullable|file|mimes:jpg,jpeg,png,heic|max:4096',
                'plat_ids' => 'nullable|array',
                'plat_ids.*' => 'exists:plats,id',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validate->errors()
                ], 401);
            }

            // Mise à jour du menu
            $menu->update([
                'nom' => $request->nom,
                'bioMenu' => $request->bioMenu,
                'prix' => $request->prix,
                'photo_url' => $request->photo_url ? $this->uploadPhoto($request->file('photo_url')) : $menu->photo_url,
            ]);

            // Mettre à jour les plats associés
            if ($request->has('plat_ids')) {
                $menu->plats()->sync($request->plat_ids); // sync() pour lier les plats
            }

            return response()->json([
                'status' => true,
                'message' => 'Menu mis à jour avec succès',
                'menu' => $menu
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    // Supprimer un menu
    public function destroy($id)
    {
        try {
            $menu = Menu::find($id);

            if (!$menu) {
                return response()->json(['message' => 'Menu non trouvé'], 404);
            }

            if ($menu->photo_url) {
                $imagePath = public_path('uploads/menus/' . $menu->photo_url);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            $menu->plats()->detach();
            $menu->delete();

            return response()->json([
                'status' => true,
                'message' => 'Menu supprimé avec succès'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    // Fonction pour uploader la photo du menu
    private function uploadPhoto($file)
    {
        $path = public_path('uploads/menus');
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $fileName = time() . '_' . $file->getClientOriginalName();
        $file->move($path, $fileName);

        return $fileName;
    }
}
