<?php
// ----------------- registration route --------------

use App\Http\Controllers\publics\PublicController;
use App\Http\Controllers\USerAuth\UserAuthController;
use Illuminate\Support\Facades\Route;
// ------------------ registration api --------------
Route::post('/registration',[PublicController::class,'registration']);
// --------------- forgot password api route --------------
Route::post('/forgot-password',[UserAuthController::class,'forgotPassword']);
// ----------------- set new password -------------
Route::post('/set-new-password',[UserAuthController::class,'setNewPassword']);
