<?php

use App\Http\Controllers\admin\ManageProductsController;
use Illuminate\Support\Facades\Route;

// -------------- save or update products route----------
Route::post('/save-or-update-products',[ManageProductsController::class,'saveOrUpdateProducts']);
