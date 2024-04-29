<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
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
    //Auth Routes
Route::group(['middleware' => 'api'], function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
});
 //Category Routes
Route::group(['middleware' => ['verify.token'], 'prefix' => 'category'], function () {
    Route::get('/index', [CategoryController::class, 'index']);
    Route::post('/add', [CategoryController::class, 'store']);
    Route::post('/delete/{id}', [CategoryController::class, 'delete']);
    Route::post('/edit/{id}', [CategoryController::class, 'edit']);
});
//Product Routes
Route::group(['middleware'=>['verify.token'],'prefix' => 'product'],function () {
    Route::get('/index', [ProductController::class, 'index']);
    Route::get('/get-product-by-id/{id}', [ProductController::class, 'getProductById']);
    Route::post('/add', [ProductController::class, 'store']);
    Route::post('/delete/{id}', [ProductController::class, 'delete']);
    Route::post('/edit/{id}', [ProductController::class, 'edit']);
});
// Favorites Products Routes
Route::get('/favorite-products',[FavoriteController::class,'getFavoriteProducts'])
        ->middleware('verify.token'); // Get ALL Favorite Products For Logged User
Route::post('/add-favorite-products',[FavoriteController::class,'storeFavoriteProducts'])
    ->middleware('verify.token'); // Store Favorite Products For Logged User
// Popular Products on Home Page
Route::get('display-popular-product',[ProductController::class,'displayPopularProduct'])
                ->middleware('verify.token');
//Products By Category Id
Route::get('get-product-by-categoryId/{id}',[ProductController::class,'getProductsByCategoryId'])
    ->middleware('verify.token');

// Get Authenticated User
Route::get('/authenticated-user', [UserController::class, 'getAuthenticatedUser'])
            ->middleware('verify.token');


//Edit Profile Information
Route::post('edit-profile-data',[UserController::class,'editProfileData'])
            ->middleware('verify.token');

Route::get('/search-product-by-name', [ProductController::class, 'searchProductByName'])->middleware('verify.token');
