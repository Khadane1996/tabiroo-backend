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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Twilio\Rest\Client;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Durée de vie du cache pour les inscriptions (en minutes)
     */
    const REGISTRATION_CACHE_TTL = 15;

    /**
     * Nettoyer les données d'inscription expirées du cache
     */
    private function cleanupExpiredRegistrations()
    {
        // Cette méthode peut être appelée périodiquement ou dans un job
        // Pour l'instant, elle est documentée pour usage futur
    }

    /**
     * Renvoi du code OTP pour une inscription en attente
     */
    public function resendOtp(Request $request)
    {
        try {
            $validate = Validator::make($request->all(), [
                'email' => 'nullable|string|email|required_without_all:phone,cache_key',
                'phone' => 'nullable|string|required_without_all:email,cache_key',
                'cache_key' => 'nullable|string|required_without_all:email,phone',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validate->errors(),
                ], 422);
            }

            $registrationData = null;
            $cacheKey = $request->cache_key;

            // Chercher les données dans le cache
            if ($cacheKey) {
                $registrationData = Cache::get($cacheKey);
            } else {
                $identifier = $request->email ?: $request->phone;
                $cacheKeys = Cache::get('registration_keys_' . $identifier, []);
                
                foreach ($cacheKeys as $key) {
                    $data = Cache::get($key);
                    if ($data) {
                        $registrationData = $data;
                        $cacheKey = $key;
                        break;
                    }
                }
            }

            if (!$registrationData) {
                return response()->json([
                    'status' => false,
                    'message' => 'Aucune inscription en attente trouvée ou session expirée.',
                ], 404);
            }

            // Générer un nouveau code
            $newConfirmationCode = random_int(1000, 9999);
            $registrationData['confirmation_code'] = $newConfirmationCode;

            // Remettre à jour le cache
            Cache::put($cacheKey, $registrationData, now()->addMinutes(self::REGISTRATION_CACHE_TTL));

            // Renvoyer le code
            $view = 'mail.confirm';

            if ($registrationData['email']) {
                Mail::to($registrationData['email'])->send(new ConfirmRegistrationMail($newConfirmationCode, $view));
            } else {
                $this->sendSms($registrationData['phone'], $newConfirmationCode);
            }

            return response()->json([
                'status' => true,
                'message' => 'Nouveau code de confirmation envoyé.',
                'cache_key' => $cacheKey
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Une erreur est survenue : ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Vérifier le statut d'une inscription en attente
     */
    public function checkRegistrationStatus(Request $request)
    {
        try {
            $validate = Validator::make($request->all(), [
                'cache_key' => 'required|string',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Clé de cache requise',
                    'errors' => $validate->errors(),
                ], 422);
            }

            $registrationData = Cache::get($request->cache_key);

            if (!$registrationData) {
                return response()->json([
                    'status' => false,
                    'message' => 'Session d\'inscription expirée',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Session d\'inscription active',
                'expires_at' => $registrationData['created_at']->addMinutes(self::REGISTRATION_CACHE_TTL),
                'email' => $registrationData['email'] ? '***' . substr($registrationData['email'], -10) : null,
                'phone' => $registrationData['phone'] ? '***' . substr($registrationData['phone'], -4) : null,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Une erreur est survenue : ' . $th->getMessage(),
            ], 500);
        }
    }
    public function register(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(), [
                'phone' => 'nullable|string|unique:users,phone|required_without:email',
                'email' => 'nullable|string|unique:users,email|required_without:phone',
                'role_id' => 'required|integer',
                'password' => 'required|string|min:6',
                // 'firstNameOrPseudo' => 'required|string|max:255',
                // 'lastName' => 'nullable|string|max:255',
            ]);

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validateUser->errors()
                ], 422);
            }

            // Vérifier si l'utilisateur existe déjà (même s'il n'est pas confirmé)
            $existingUser = User::where(function ($query) use ($request) {
                if ($request->email) {
                    $query->where('email', $request->email);
                }
                if ($request->phone) {
                    $query->orWhere('phone', $request->phone);
                }
            })->first();

            if ($existingUser) {
                return response()->json([
                    'status' => false,
                    'message' => 'Un compte existe déjà avec ces informations'
                ], 409);
            }

            // Générer un code de confirmation
            $confirmationCode = random_int(1000, 9999);
            
            // Générer une clé unique pour le cache
            $cacheKey = 'registration_' . ($request->email ?: $request->phone) . '_' . time();
            
            // Stocker les données d'inscription dans le cache pour 15 minutes
            $registrationData = [
                // 'firstNameOrPseudo' => $request->firstNameOrPseudo,
                // 'lastName' => $request->lastName,
                'phone' => $request->phone,
                'email' => $request->email,
                'role_id' => $request->role_id,
                'password' => Hash::make($request->password),
                'confirmation_code' => $confirmationCode,
                'cache_key' => $cacheKey,
                'created_at' => now(),
            ];

            Cache::put($cacheKey, $registrationData, now()->addMinutes(self::REGISTRATION_CACHE_TTL));

            // Envoyer le code de confirmation
            $view = 'mail.confirm';

            if ($request->email) {
                Mail::to($request->email)->send(new ConfirmRegistrationMail($confirmationCode, $view));
            } else {
                $this->sendSms($request->phone, $confirmationCode);
            }

            return response()->json([
                'status' => true,
                'message' => 'Code de confirmation envoyé. Veuillez vérifier votre ' . ($request->email ? 'email' : 'SMS'),
                'cache_key' => $cacheKey, // Optionnel: pour le debug en développement
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Une erreur est survenue : ' . $th->getMessage()
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

            if ($user->etat != 1) {
                return response()->json([
                    'status' => false,
                    'message' => 'Votre compte est inactif',
                ], 403);
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
        try {
            $validate = Validator::make($request->all(), [
                'email' => 'nullable|string|email|required_without_all:phone,cache_key',
                'phone' => 'nullable|string|required_without_all:email,cache_key',
                'cache_key' => 'nullable|string|required_without_all:email,phone',
                'confirmation_code' => 'required|string',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validate->errors(),
                ], 422);
            }

            $registrationData = null;
            $cacheKey = $request->cache_key;

            // Si cache_key est fourni, l'utiliser directement
            if ($cacheKey) {
                $registrationData = Cache::get($cacheKey);
            } else {
                // Sinon, chercher dans le cache par email/phone
                $identifier = $request->email ?: $request->phone;
                $cacheKeys = Cache::get('registration_keys_' . $identifier, []);
                
                foreach ($cacheKeys as $key) {
                    $data = Cache::get($key);
                    if ($data && $data['confirmation_code'] == $request->confirmation_code) {
                        $registrationData = $data;
                        $cacheKey = $key;
                        break;
                    }
                }
            }

            if (!$registrationData || $registrationData['confirmation_code'] != $request->confirmation_code) {
                return response()->json([
                    'status' => false,
                    'message' => 'Code de confirmation incorrect ou expiré.',
                ], 400);
            }

            // Vérifier si les identifiants correspondent
            $identifierMatch = false;
            if ($request->email && $registrationData['email'] === $request->email) {
                $identifierMatch = true;
            } elseif ($request->phone && $registrationData['phone'] === $request->phone) {
                $identifierMatch = true;
            } elseif ($request->cache_key) {
                $identifierMatch = true; // Si cache_key est fourni, on fait confiance
            }

            if (!$identifierMatch) {
                return response()->json([
                    'status' => false,
                    'message' => 'Les identifiants ne correspondent pas.',
                ], 400);
            }

            // Commencer une transaction pour créer l'utilisateur et ses relations
            DB::beginTransaction();

            try {
                // Créer l'utilisateur
                $user = User::create([
                    // 'firstNameOrPseudo' => $registrationData['firstNameOrPseudo'],
                    // 'lastName' => $registrationData['lastName'],
                    'phone' => $registrationData['phone'],
                    'email' => $registrationData['email'],
                    'role_id' => $registrationData['role_id'],
                    'password' => $registrationData['password'],
                    'etat' => 1, // Compte confirmé
                    'email_verified_at' => $registrationData['email'] ? now() : null,
                ]);

                // Créer l'adresse associée
                Adresse::create([
                    'user_id' => $user->id
                ]);

                // Créer les informations bancaires associées
                Bancaire::create([
                    'user_id' => $user->id
                ]);

                // Supprimer les données du cache
                Cache::forget($cacheKey);

                // Supprimer aussi la clé de la liste des clés pour cet identifier
                if (!$request->cache_key) {
                    $identifier = $request->email ?: $request->phone;
                    $cacheKeys = Cache::get('registration_keys_' . $identifier, []);
                    $cacheKeys = array_filter($cacheKeys, function($key) use ($cacheKey) {
                        return $key !== $cacheKey;
                    });
                    
                    if (empty($cacheKeys)) {
                        Cache::forget('registration_keys_' . $identifier);
                    } else {
                        Cache::put('registration_keys_' . $identifier, $cacheKeys, now()->addMinutes(15));
                    }
                }

                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Compte confirmé et créé avec succès.',
                    'user' => [
                        'id' => $user->id,
                        'firstNameOrPseudo' => $user->firstNameOrPseudo,
                        'lastName' => $user->lastName,
                        'email' => $user->email,
                        'phone' => $user->phone,
                    ]
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Une erreur est survenue : ' . $th->getMessage(),
            ], 500);
        }
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

    function sendSms2(Request $request)
    {
        try {
            $sid = env('TWILIO_SID');
            $token = env('TWILIO_TOKEN');
            $from = env('TWILIO_PHONE');
    
            $twilio = new Client($sid, $token);
            $message = "Votre code de validation est : " . $request->otp;
    
            $twilio->messages->create($request->phone, [
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

    public function updateProfile(Request $request)
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

            $user = User::find($request->id);

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
