<?php

use App\Http\Controllers\ConsumerPages\ProductController;
use Illuminate\Support\Facades\Route;
// ------------------- show products route -------------
Route::get('/product-lists', [ProductController::class, 'productLists']);
// ------------- show product details -----------------
Route::get('/product-details', [ProductController::class, 'getProduct']);

// ------------- start middleware grouping routes ------------
Route::group(['prefix'=>'auth-user','middleware' => ['protect_user:user_guard']], function () {
    // -------------- add to card route ----------------
    Route::post('/add-card', [ProductController::class, 'addCard']);
    // ------------- view cart products ----------------------
    Route::get('/cart-products', [ProductController::class, 'cartProducts']);
    // ----------------- buyer process route -----------------
    Route::post('/buyer-process',[ProductController::class,'buyerProcess']);
});
// ------------- end middleware grouping routes ------------
