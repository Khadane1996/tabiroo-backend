<?php

use App\Http\Controllers\API\V1\AuthController;
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