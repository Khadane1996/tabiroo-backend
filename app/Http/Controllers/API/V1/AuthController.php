<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Mail\ConfirmRegistrationMail;
use App\Models\Adresse;
use App\Models\Bancaire;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Twilio\Rest\Client;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
   {
        try {
            $validateUser = Validator::make($request->all(), 
            [
                'phone' => 'nullable|string|unique:users,phone|required_without:email',
                'email' => 'nullable|string|unique:users,email|required_without:phone',
                'role_id' => 'required',
                'password' => 'required',
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }
            
            $user = User::create([
                'firstNameOrPseudo' => $request->firstNameOrPseudo,
                'lastName' => $request->lastName,
                'phone' => $request->phone,
                'email' => $request->email,
                'role_id' => $request->role_id,
                'password' => Hash::make($request->password)
            ]);

            $confirmationCode = random_int(1000, 9999);
            
            $user->confirmation_code = $confirmationCode;
            $user->save();

            Adresse::create([
                'user_id' => $user->id
            ]);
    
            Bancaire::create([
                'user_id' => $user->id
            ]);

            $view = 'mail.confirm';

            if ($user->email) {
                Mail::to($user->email)->send(new ConfirmRegistrationMail($confirmationCode, $view));
            } else {
                $this->sendSms($request->phone, $confirmationCode);
            }

            return response()->json([
                'status' => true,
                'message' => 'Utilisateur créé avec succès',
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
   }

   public function login(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(), [
                'email' => 'nullable|string|email|required_without:phone',
                'phone' => 'nullable|string|required_without:email',
                'password' => 'required|string',
            ]);

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validateUser->errors(),
                ], 422);
            }

            $user = User::when($request->email, function ($query) use ($request) {
                    $query->where('email', $request->email);
                })
                ->when($request->phone, function ($query) use ($request) {
                    $query->where('phone', $request->phone);
                })
                ->with(['adresse', 'bancaire'])
                ->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Identifiants incorrects',
                ], 401);
            }

            $token = $user->createToken("auth_token")->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'Connexion réussie',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'photo_url' => $user->photo_url,
                    'firstNameOrPseudo' => $user->firstNameOrPseudo,
                    'lastName' => $user->lastName,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'biographie' => $user->biographie,
                    'adresse' => $user->adresse,
                    'bancaire' => $user->bancaire,
                ],
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Une erreur est survenue : ' . $th->getMessage(),
            ], 500);
        }
    }


    public function verify(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'email' => 'nullable|string|email|required_without:phone',
            'phone' => 'nullable|string|required_without:email',
            'confirmation_code' => 'required|string',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Erreur de validation',
                'errors' => $validate->errors(),
            ], 422);
        }

        $user = User::where(function ($query) use ($request) {
                if (!empty($request->phone)) {
                    $query->where('phone', $request->phone);
                } else {
                    $query->where('email', $request->email);
                }
            })
            ->where('confirmation_code', $request->confirmation_code)
            ->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Code de confirmation incorrect.',
            ], 400);
        }

        $user->confirmation_code = null;
        $user->etat = 1;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Compte confirmé avec succès.',
        ], 200);
    }

    function sendSms($phone, $otp)
    {
        try {
            $sid = env('TWILIO_SID');
            $token = env('TWILIO_TOKEN');
            $from = env('TWILIO_PHONE');
    
            $twilio = new Client($sid, $token);
            $message = "Votre code de validation est : " . $otp;
    
            $twilio->messages->create($phone, [
                'from' => $from,
                'body' => $message
            ]);
    
            return response()->json(['message' => 'SMS envoyé avec succès.'], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    function sendOtpforResetPassword(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(), [
                'email' => 'nullable|string|email|required_without:phone',
                'phone' => 'nullable|string|required_without:email',
            ]);

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validateUser->errors(),
                ], 422);
            }

            $user = User::when($request->email, function ($query) use ($request) {
                    $query->where('email', $request->email);
                })
                ->when($request->phone, function ($query) use ($request) {
                    $query->where('phone', $request->phone);
                })
                ->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Identifiants incorrects',
                ], 401);
            }
            
            $confirmationCode = random_int(1000, 9999);
            
            $user->confirmation_code = $confirmationCode;
            $user->save();

            $view = 'mail.password';

            if ($user->email) {
                Mail::to($user->email)->send(new ConfirmRegistrationMail($confirmationCode, $view));
            } else {
                $this->sendSms($request->phone, $confirmationCode);
            }

            return response()->json([
                'status' => true,
                'message' => 'Requete envoyée avec succès',
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Une erreur est survenue : ' . $th->getMessage(),
            ], 500);
        }
    }

    function resetPassword(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(), [
                'email' => 'nullable|string|email|required_without:phone',
                'phone' => 'nullable|string|required_without:email',
                'password' => 'required|string'
            ]);

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validateUser->errors(),
                ], 422);
            }

            $user = User::when($request->email, function ($query) use ($request) {
                    $query->where('email', $request->email);
                })
                ->when($request->phone, function ($query) use ($request) {
                    $query->where('phone', $request->phone);
                })
                ->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Identifiants incorrects',
                ], 401);
            }
            
            $user->password = Hash::make($request->password);
            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'Mot de passe modifié avec succès',
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Une erreur est survenue : ' . $th->getMessage(),
            ], 500);
        }
    }

    public function googleAuth(Request $request)
    {
        try 
        {
            $validator = Validator::make($request->all(), [
                'token' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            // Vérification du token Google avec Google API
            $googleToken = $request->token;
            $googleUser = Http::get("https://oauth2.googleapis.com/tokeninfo?id_token={$googleToken}")->json();

            if (isset($googleUser['error_description'])) {
                return response()->json(['error' => 'Token Google invalide'], 401);
            }

            $user = User::firstOrCreate(
                ['email' => $googleUser['email']],
                [
                    'role_id' => 2,
                    'etat' => 1,
                    'password' => Hash::make(uniqid()),
                ]
            );

            $token = $user->createToken("auth_token")->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'Connexion réussie',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'firstNameOrPseudo' => $user->firstNameOrPseudo,
                    'lastName' => $user->lastName,
                    'email' => $user->email,
                    'phone' => $user->phone,
                ],
            ], 200);
            
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Une erreur est survenue : ' . $th->getMessage(),
            ], 500);
        }
    }

    public function appleAuth(Request $request)
    {
        try 
        {
            $validator = Validator::make($request->all(), [
                'token' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            // Récupérer le token Apple envoyé par le client
            $appleToken = $request->token;

            // Récupérer les clés publiques d'Apple
            $appleKeys = Http::get("https://appleid.apple.com/auth/keys")->json();
            if (!isset($appleKeys['keys'])) {
                return response()->json(['error' => 'Impossible de récupérer les clés publiques d\'Apple'], 500);
            }

            // Décoder le token sans vérification pour extraire le header
            $tokenParts = explode(".", $appleToken);
            if (count($tokenParts) !== 3) {
                return response()->json(['error' => 'Token Apple invalide'], 401);
            }

            // Décoder le header du JWT pour récupérer le "kid"
            $header = json_decode(base64_decode($tokenParts[0]), true);
            if (!isset($header['kid'])) {
                return response()->json(['error' => 'Header JWT invalide'], 401);
            }

            // Récupérer la clé publique correspondante au "kid"
            $appleKey = null;
            foreach ($appleKeys['keys'] as $key) {
                if ($key['kid'] === $header['kid']) {
                    $appleKey = $key;
                    break;
                }
            }

            if (!$appleKey) {
                return response()->json(['error' => 'Clé Apple introuvable'], 401);
            }

            $publicKeys = JWK::parseKeySet(['keys' => [$appleKey]]);
            $decodedToken = (array) JWT::decode($appleToken, $publicKeys);

            $email = $decodedToken['email'] ?? null;
            if (!$email) {
                return response()->json(['error' => 'Impossible de récupérer l\'email depuis Apple'], 400);
            }

            // Vérifier si l'utilisateur existe ou le créer
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'role_id' => 2,
                    'etat' => 1,
                    'password' => Hash::make(uniqid()),
                ]
            );

            $token = $user->createToken("auth_token")->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'Connexion réussie',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'firstNameOrPseudo' => $user->firstNameOrPseudo,
                    'lastName' => $user->lastName,
                    'email' => $user->email,
                    'phone' => $user->phone,
                ],
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Une erreur est survenue : ' . $th->getMessage(),
            ], 500);
        }
    }

    public function updateProfile(Request $request, $id)
    {

        // $user = Auth::user();

        // if (!$user) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'Utilisateur non authentifié.',
        //     ]);
        // }
        try {
            $validateUser = Validator::make($request->all(), [
                'phone' => 'nullable|string|required_without:email',
                'email' => 'nullable|string|required_without:phone',
            ]);

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validateUser->errors(),
                ], 422);
            }

            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Utilisateur non trouvé',
                ], 404);
            }

            $fileName = null;

            if ($user->photo_url) {
                $oldPath = public_path('uploads/profile/' . $user->photo_url);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            if ($request->hasFile('photo_url')) {
                $file = $request->file('photo_url');
                $fileName = time() . '_' . $file->getClientOriginalName();
                
                $path = public_path('uploads/profile');
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }

                $file->move($path, $fileName);
            }

            $user->update([
                'firstNameOrPseudo' => $request->firstNameOrPseudo,
                'lastName' => $request->lastName,
                'email' => $request->email,
                'phone' => $request->phone,
                'biographie' => $request->biographie,
                'photo_url' => $fileName
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Profil mis à jour avec succès',
                'user' => $user
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Une erreur est survenue : ' . $th->getMessage(),
            ], 500);
        }
    }

}
