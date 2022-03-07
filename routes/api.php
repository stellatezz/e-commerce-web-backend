<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\CategoriesController;
use App\Models\Product;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('products', [ProductsController::class, 'index']);
Route::get('products/category/{category}', [ProductsController::class, 'getWithCatid']);
Route::get('products/{product}', [ProductsController::class, 'getWithPid']);
Route::post('products', [ProductsController::class, 'store']);
Route::post('products/{product}', [ProductsController::class, 'update']);
Route::delete('products/{product}', [ProductsController::class, 'destroy']);

Route::get('categories', [CategoriesController::class, 'index']);
Route::get('categories/{category}', [CategoriesController::class, 'getWithCatid']);
Route::post('categories', [CategoriesController::class, 'store']);
Route::post('categories/{category}', [CategoriesController::class, 'update']);
Route::delete('categories/{category}', [CategoriesController::class, 'destroy']);
