<?php

use App\Http\Controllers\API\V1\AuthController;
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