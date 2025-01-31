<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Mail\ConfirmRegistrationMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Twilio\Rest\Client;

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
                'firstName' => $request->firstName,
                'lastName' => $request->lastName,
                'phone' => $request->phone,
                'email' => $request->email,
                'role_id' => $request->role_id,
                'password' => Hash::make($request->password)
            ]);

            $confirmationCode = random_int(1000, 9999);
            
            $user->confirmation_code = $confirmationCode;
            $user->save();

            if ($user->email) {
                Mail::to($user->email)->send(new ConfirmRegistrationMail($confirmationCode));
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
                ->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Identifiants incorrects',
                ], 401);
            }

            $token = $user->createToken("API TOKEN")->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'Connexion réussie',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'firstName' => $user->firstName,
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
}
