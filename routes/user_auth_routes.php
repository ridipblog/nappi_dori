<?php

use App\Http\Controllers\USerAuth\UserAuthController;
use Illuminate\Support\Facades\Route;
// --------------- login api route -------------
Route::post('/login',[UserAuthController::class,'login']);

