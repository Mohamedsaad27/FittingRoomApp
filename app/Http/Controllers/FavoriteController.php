<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Product;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    use ApiResponseTrait;
    // Get ALL Favorite Products For Logged User
    public function getFavoriteProducts(Request $request)
    {
        try {
            $userId = auth()->id();
            $favoriteProducts = Favorite::where('user_id', $userId)->get();
            if ($favoriteProducts->isEmpty()) {
                return $this->errorResponse('No Favorite Products', 404);
            }
            return $this->successResponse($favoriteProducts, null, 200);
        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage(), 500);
        }
    }
    // Store Favorite Products For Logged User
    public function storeFavoriteProducts(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
            ]);
            $userId = auth()->id();
            $productId = $request->product_id;
            $existingFavorite = Favorite::where('user_id', $userId)
                ->where('product_id', $productId)
                ->exists();
            if ($existingFavorite) {
                return $this->errorResponse('Product already added to favorites', 422);
            }
           $favProduct =  Favorite::create([
                'user_id' => $userId,
                'product_id' => $productId,
            ]);
            return $this->successResponse($favProduct,'Product added to favorites successfully', 201);
        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage(),500);
        }
    }

}
