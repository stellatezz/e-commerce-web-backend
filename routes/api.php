<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Models\Product;
use App\Http\Middleware\EnsureTokenIsValid;
use App\Http\Middleware\EnsureCSRFIsValid;

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

Route::post('products', [ProductsController::class, 'store'])->middleware([EnsureTokenIsValid::class, EnsureCSRFIsValid::class]);
Route::post('products/{product}', [ProductsController::class, 'update'])->middleware([EnsureTokenIsValid::class, EnsureCSRFIsValid::class]);
Route::post('products/delete/{product}', [ProductsController::class, 'destroy'])->middleware([EnsureTokenIsValid::class, EnsureCSRFIsValid::class]);

Route::get('categories', [CategoriesController::class, 'index']);
Route::get('categories/{category}', [CategoriesController::class, 'getWithCatid']);

Route::post('categories', [CategoriesController::class, 'store'])->middleware([EnsureTokenIsValid::class, EnsureCSRFIsValid::class]);
Route::post('categories/{category}', [CategoriesController::class, 'update'])->middleware([EnsureTokenIsValid::class, EnsureCSRFIsValid::class]);
Route::post('categories/delete/{category}', [CategoriesController::class, 'destroy'])->middleware([EnsureTokenIsValid::class, EnsureCSRFIsValid::class]);

Route::post('auth/register', [AuthController::class, 'register'])->middleware([EnsureTokenIsValid::class, EnsureCSRFIsValid::class]);
Route::post('auth/login', [AuthController::class, 'login'])->middleware(EnsureCSRFIsValid::class);

Route::post('auth/logout', [AuthController::class, 'logout'])->middleware(EnsureCSRFIsValid::class);

Route::post('user/modify/pw', [UserController::class, 'changePassword'])->middleware(EnsureCSRFIsValid::class);
Route::post('user/vaildate', [UserController::class, 'vaildateSession']);

Route::post('order/checkout', [OrderController::class, 'checkout'])->middleware(EnsureCSRFIsValid::class);

Route::post('order/paypal-ipn', [OrderController::class, 'paypalIPN']);

Route::post('order/allorder', [OrderController::class, 'getAllOrder'])->middleware([EnsureTokenIsValid::class, EnsureCSRFIsValid::class]);
Route::post('order/userorder', [OrderController::class, 'getUserOrder'])->middleware(EnsureCSRFIsValid::class);



//Route::get('auth/test', [AuthController::class, 'test']);


