<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DataSeedController extends Controller
{
    public function typePlat()
    {
        try {
            $typeDePlat = DB::table('types_de_plat')->get();

            return response()->json([
                'status' => true,
                'data' => $typeDePlat
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Une erreur est survenue : ' . $th->getMessage(),
            ], 500);
        }
    }

    public function typeCuisine()
    {
        try {
            $typeDeCuisine = DB::table('types_de_cuisine')->get();

            return response()->json([
                'status' => true,
                'data' => $typeDeCuisine
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Une erreur est survenue : ' . $th->getMessage(),
            ], 500);
        }
    }

    public function regimeAlimentaire()
    {
        try {
            $regime = DB::table('regimes_alimentaire')->get();

            return response()->json([
                'status' => true,
                'data' => $regime
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Une erreur est survenue : ' . $th->getMessage(),
            ], 500);
        }
    }

    public function themeCulinaire()
    {
        try {
            $theme = DB::table('themes_culinaire')->get();

            return response()->json([
                'status' => true,
                'data' => $theme
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Une erreur est survenue : ' . $th->getMessage(),
            ], 500);
        }
    }

    public function typeRepas()
    {
        try {
            $typeDeRepas = DB::table('types_de_repas')->get();

            return response()->json([
                'status' => true,
                'data' => $typeDeRepas
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Une erreur est survenue : ' . $th->getMessage(),
            ], 500);
        }
    }
}