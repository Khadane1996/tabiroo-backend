<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Adresse;
use App\Models\Bancaire;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;


class ProfilController extends Controller
{
    public function updateAdresse(Request $request)
    {
        // $userAuth = Auth::user();

        try {
            $validateUser = Validator::make($request->all(), [
                'id' => 'required|exists:adresses,id'
            ]);

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validateUser->errors(),
                ], 422);
            }

            $adresse = Adresse::find($request->id);

            if (!$adresse) {
                return response()->json([
                    'status' => false,
                    'message' => 'Utilisateur non trouvé',
                ], 404);
            }    

            $adresse->update([
                'adresse' => $request->adresse,
                'complementAdresse' => $request->complementAdresse,
                'codePostal' => $request->codePostal,
                'ville' => $request->ville
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Adresse modifiée avec succès',
                'adresse' => $adresse
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Une erreur est survenue : ' . $th->getMessage(),
            ], 500);
        }
    }

    public function updateBancaire(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(), [
                'id' => 'required|exists:bancaires,id'
            ]);

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validateUser->errors(),
                ], 422);
            }

            $bancaire = Bancaire::find($request->id);

            if (!$bancaire) {
                return response()->json([
                    'status' => false,
                    'message' => 'Utilisateur non trouvé',
                ], 404);
            }    

            $bancaire->update([
                'iban' => $request->iban,
                'bic' => $request->bic
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Données bancaires modifiées avec succès',
                'bancaire' => $bancaire
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Une erreur est survenue : ' . $th->getMessage(),
            ], 500);
        }
    }

    public function desactiveCompte(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(), [
                'id' => 'required|exists:bancaires,id',
                'etat' => 'required'
            ]);

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validateUser->errors(),
                ], 422);
            }

            $user = User::find($request->id);

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Utilisateur non trouvé',
                ], 404);
            }    

            $user->update([
                'etat' => $request->etat
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Utilisateur désactivé avec succès',
                'user' => $user
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Une erreur est survenue : ' . $th->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json(['message' => 'Utilisateur non trouvé'], 404);
            }

            $user->delete();

            return response()->json([
                'status' => true,
                'message' => 'Utilisateur supprimé avec succès'
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Une erreur est survenue : ' . $th->getMessage(),
            ], 500);
        }
    }

    public function updatePassword(Request $request, $id)
    {
        try {
            $request->validate([
                'password' => 'required|string|min:6',
                'new_password' => 'required|string|min:6|confirmed'
            ]);
    
            $user = User::find($id);
    
            if (!$user) {
                return response()->json(['message' => 'Utilisateur non trouvé'], 404);
            }
    
            if (!Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'Ancien mot de passe incorrect'], 403);
            }
    
            $user->password = Hash::make($request->new_password);
            $user->save();
    
            return response()->json([
                'status' => true,
                'message' => 'Mot de passe mis à jour avec succès',
            ]);
    
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Une erreur est survenue : ' . $th->getMessage(),
            ], 500);
        }
    }
}
