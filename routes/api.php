<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ------------------- start public routes --------------------
require __DIR__ . '/public_routes.php';
// ------------------- end public routes --------------------

// ------------------ start user auth routes -------------------
require __DIR__ . '/user_auth_routes.php';
// ----------------- end user auth routes ----------------
// ------------------- start product pages routes ------------
require __DIR__ . '/product_routes.php';
// ------------------- end product pages routes ------------

// ----------------- start admin routes -------------
Route::group(['prefix' => 'admin'], function () {
    require __DIR__ . '/admin/manage_products_routes.php';
});
// ----------------- end admin routes -----------------
