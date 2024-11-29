<?php
// ----------------- registration route --------------

use App\Http\Controllers\publics\PublicController;
use Illuminate\Support\Facades\Route;
// ------------------ registration api --------------
Route::post('/registration',[PublicController::class,'registration']);
