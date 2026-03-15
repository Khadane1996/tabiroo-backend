<?php

use App\Http\Controllers\API\V1\AuthController;
use App\Http\Controllers\API\V1\DataSeedController;
use App\Http\Controllers\API\V1\MenuController;
use App\Http\Controllers\API\V1\PlatController;
use App\Http\Controllers\API\V1\PrestationController;
use App\Http\Controllers\API\V1\ProfilController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\Client\AllPrestationController;
use App\Http\Controllers\API\V1\Client\ReservationController;
use App\Http\Controllers\API\V1\Client\AvisClientController;
use App\Http\Controllers\API\V1\TableauBordController;
use App\Http\Controllers\StripeController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post("/register", [AuthController::class, "register"]);
Route::post("/login", [AuthController::class, "login"]);
Route::post("/opt", [AuthController::class, "verify"]);
Route::post("/resend-otp", [AuthController::class, "resendOtp"]);
Route::post("/check-registration-status", [AuthController::class, "checkRegistrationStatus"]);
Route::post("/sms", [AuthController::class, "sendSms2"]);
Route::post("/send-otp-reset-password", [AuthController::class, "sendOtpforResetPassword"]);
Route::post("/reset-password", [AuthController::class, "resetPassword"]);
Route::post("/auth/google", [AuthController::class, "googleAuth"]);
Route::post("/auth/apple", [AuthController::class, "appleAuth"]);


Route::middleware('auth:sanctum')->group(function () {
    Route::post("/update-profile", [AuthController::class, "updateProfile"]);
    Route::post("/update-adresse", [ProfilController::class, "updateAdresse"]);
    Route::post("/qcm-badges", [ProfilController::class, "updateQcmBadges"]);
    Route::post("/push-token", [AuthController::class, "storePushToken"]);
});

Route::post("/update-bancaire", [ProfilController::class, "updateBancaire"]);
Route::post("/desactive-compte", [ProfilController::class, "desactiveCompte"]);
Route::delete("/destroy-compte/{id}", [ProfilController::class, "destroy"]);
Route::put("/update-password-profil/{id}", [ProfilController::class, "updatePassword"]);

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('plats')->group(function () {
        Route::get('/', [PlatController::class, 'index']);
        Route::post('/', [PlatController::class, 'store']);
        Route::get('/{id}/check-usage', [PlatController::class, 'checkUsage']);
        Route::get('/{id}', [PlatController::class, 'show']);
        Route::post('/update/{id}', [PlatController::class, 'update']);
        Route::delete('/{id}', [PlatController::class, 'destroy']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('menus')->group(function () {
        Route::get('/', [MenuController::class, 'index']);
        Route::get('{id}/check-usage', [MenuController::class, 'checkUsage']);
        Route::get('{id}', [MenuController::class, 'show']);
        Route::post('/', [MenuController::class, 'store']);
        Route::post('/update/{id}', [MenuController::class, 'update']);
        Route::delete('{id}', [MenuController::class, 'destroy']);
    });
});

Route::get("/type-plat", [DataSeedController::class, "typePlat"]);
Route::get("/type-cuisine", [DataSeedController::class, "typeCuisine"]);
Route::get("/regime-alimentaire", [DataSeedController::class, "regimeAlimentaire"]);
Route::get("/theme-culinaire", [DataSeedController::class, "themeCulinaire"]);
Route::get("/type-repas", [DataSeedController::class, "typeRepas"]);
Route::get("/ambiance-animations", [DataSeedController::class, "ambianceAnimations"]);

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('prestations')->group(function () {
        Route::get('/', [PrestationController::class, 'index']);
        Route::get('{id}/check-reservations', [PrestationController::class, 'checkReservations']);
        Route::get('{id}', [PrestationController::class, 'show']);
        Route::post('/', [PrestationController::class, 'store']);
        Route::put('{id}', [PrestationController::class, 'update']);
        Route::delete('{id}', [PrestationController::class, 'destroy']);
    });
});

Route::get('/test', function (Request $request) {
    return response()->json(['message' => 'Test reussi'], 200);
});

Route::prefix('client')->group(function () {
    Route::get("/all-prestations", [AllPrestationController::class, "index"]);
    Route::get("/mieux-notes-prestations", [AllPrestationController::class, "mieuxNote"]);
    Route::get("/plats/{id}", [AllPrestationController::class, "getPlats"]);
    Route::post("/reservation", [ReservationController::class, "store"]);

    // CDC: Calcul du prix (preview avant paiement)
    Route::get("/reservation/calculate-price", [ReservationController::class, "calculatePrice"]);

    Route::post("/reservation/reserve-and-pay", [ReservationController::class, "reserveAndPay"])
        ->middleware('auth:sanctum');
    Route::post("/reservation/cancel-on-payment-fail", [ReservationController::class, "cancelOnPaymentFail"])
        ->middleware('auth:sanctum');
    Route::get("/reservation/{id}/payment-details", [ReservationController::class, "getPaymentDetails"])
        ->middleware('auth:sanctum');
    Route::post("/reservation/update/{id}", [ReservationController::class, "update"]);
    Route::get("/reservation/{user_id}", [ReservationController::class, "index"]);

    // CDC: Annulation par le convive (endpoint dedie)
    Route::post("/reservation/{id}/cancel", [ReservationController::class, "cancelByGuest"])
        ->middleware('auth:sanctum');

    Route::get("/avis-client/{menu_id}", [AvisClientController::class, "index"]);
    Route::post("/avis-client", [AvisClientController::class, "store"]);

    Route::get("/reservation-chef/{user_id}", [ReservationController::class, "getReservationForChef"]);
    Route::get("/notifications/{user_id}", [ReservationController::class, "getNotication"]);

    Route::post("/reservation/{id}/validate-code", [ReservationController::class, "validateWithCode"])
        ->middleware('auth:sanctum');
});

// CDC: Annulation par l'hote (endpoint dedie)
Route::middleware('auth:sanctum')->group(function () {
    Route::post("/chef/reservation/{id}/cancel", [ReservationController::class, "cancelByHost"]);
});

Route::get("/tableau-bord/{user_id}", [TableauBordController::class, "index"]);
Route::get("/best-seller-details/{user_id}", [TableauBordController::class, "getBestSellerDetails"]);
Route::get("/menus-a-la-carte/{user_id}", [TableauBordController::class, "getMenusAlaCarte"]);
Route::get("/menus-non-commercialises/{user_id}", [TableauBordController::class, "getMenusNonCommercialises"]);
Route::get("/notes-clients-details/{user_id}", [TableauBordController::class, "getNotesClientsDetails"]);
Route::get("/ca-details/{user_id}", [TableauBordController::class, "getCaDetails"]);
Route::get("/debug-best-seller/{user_id}", [TableauBordController::class, "debugBestSeller"]);

Route::prefix('stripe')->group(function () {
    Route::post('/payment-intent', [StripeController::class, 'createPaymentIntent']);

    Route::post('/setup-intent', [StripeController::class, 'createSetupIntent'])
        ->middleware('auth:sanctum');

    Route::post('/account', [StripeController::class, 'createAccount'])
        ->middleware('auth:sanctum');

    Route::post('/account/link', [StripeController::class, 'createAccountLink'])
        ->middleware('auth:sanctum');

    Route::get('/status', [StripeController::class, 'checkAccountStatus'])
        ->middleware('auth:sanctum');

    Route::get('/payment-intent/{id}/status', [StripeController::class, 'checkPaymentStatus'])
        ->middleware('auth:sanctum');
});

Route::post('/stripe/webhook', [StripeController::class, 'handleWebhook']);
