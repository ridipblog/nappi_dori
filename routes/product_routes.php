<?php

use App\Http\Controllers\ConsumerPages\ProductController;
use Illuminate\Support\Facades\Route;
// ------------------- show products route -------------
Route::get('/product-lists', [ProductController::class, 'productLists']);

// ------------- start middleware grouping routes ------------
Route::group(['middleware' => ['protect_user:user_guard']], function () {
    // -------------- add to card route ----------------
    Route::post('/add-card', [ProductController::class, 'addCard']);
});
// ------------- end middleware grouping routes ------------
