<?php

use App\Http\Controllers\API\V1\AuthController;
use App\Http\Controllers\API\V1\DataSeedController;
use App\Http\Controllers\API\V1\MenuController;
use App\Http\Controllers\API\V1\PlatController;
use App\Http\Controllers\API\V1\ProfilController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post("/register", [AuthController::class, "register"]);

Route::post("/login", [AuthController::class, "login"]);

Route::post("/opt", [AuthController::class, "verify"]);

Route::post("/sms", [AuthController::class, "sendSms"]);

Route::post("/send-otp-reset-password", [AuthController::class, "sendOtpforResetPassword"]);
Route::post("/reset-password", [AuthController::class, "resetPassword"]);
Route::post("/auth/google", [AuthController::class, "googleAuth"]);
Route::post("/auth/apple", [AuthController::class, "appleAuth"]);

Route::post("/update-profile", [AuthController::class, "updateProfile"]);

Route::post("/update-adresse", [ProfilController::class, "updateAdresse"]);
Route::post("/update-bancaire", [ProfilController::class, "updateBancaire"]);
Route::post("/desactive-compte", [ProfilController::class, "desactiveCompte"]);
Route::delete("/destroy-compte/{id}", [ProfilController::class, "destroy"]);
Route::put("/update-password-profil/{id}", [ProfilController::class, "updatePassword"]);

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('plats')->group(function () {
        Route::get('/', [PlatController::class, 'index']);
        Route::post('/', [PlatController::class, 'store']);
        Route::get('/{id}', [PlatController::class, 'show']);
        Route::put('/{id}', [PlatController::class, 'update']);
        Route::delete('/{id}', [PlatController::class, 'destroy']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('menus')->group(function () {
        Route::get('/', [MenuController::class, 'index']); // Récupérer tous les menus
        Route::get('{id}', [MenuController::class, 'show']); // Afficher un menu spécifique
        Route::post('/', [MenuController::class, 'store']); // Créer un menu
        Route::put('{id}', [MenuController::class, 'update']); // Mettre à jour un menu
        Route::delete('{id}', [MenuController::class, 'destroy']); // Supprimer un menu
    });
});

Route::get("/type-plat", [DataSeedController::class, "typePlat"]);
Route::get("/type-cuisine", [DataSeedController::class, "typeCuisine"]);
Route::get("/regime-alimentaire", [DataSeedController::class, "regimeAlimentaire"]);
Route::get("/theme-culinaire", [DataSeedController::class, "themeCulinaire"]);