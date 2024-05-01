<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    use ApiResponseTrait;

    public function index()
    {
        try {
            // Retrieve the authenticated user
            $user = auth()->user();

            // Retrieve all products and eager load the favorites relationship
            $products = Product::with(['favorites' => function ($query) use ($user) {
                if ($user) {
                    $query->where('user_id', $user->id);
                }
            }])->get();

            if ($products->isEmpty()) {
                return $this->errorResponse('No Products Found', 404);
            }

            // Add 'is_favorite' attribute to each product indicating if it's favorited by the user
            $products->each(function ($product) {
                $product->is_favorite = $product->favorites->isNotEmpty();
                unset($product->favorites);
            });

            return $this->successResponse($products, null, 200);
        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255'],
                'description' => ['required', 'string'],
                'image' => ['required', 'image', 'mimes:jpg,png,jpeg'],
                'price' => ['required', 'numeric', 'min:0'],
                'color' => ['nullable', 'string', 'max:255'],
                'size' => ['nullable', 'string', 'max:255'],
                'category_id' => ['required', 'exists:categories,id'],
            ]);
            if ($validator->fails()) {
                return $this->errorResponse($validator->errors(), 422);
            }
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->storeAs('public/products', $imageName); // Store image in the 'public' disk under 'products' directory
            $product = Product::create([
                'name' => $request->name,
                'description' => $request->description,
                'image' => url('storage/products/' . $imageName), // Construct the full URL path
                'price' => $request->price,
                'color' => $request->color,
                'size' => $request->size,
                'category_id' => $request->category_id
            ]);
            if (!$product) {
                throw new \Exception('Failed to create product');
            }
            return $this->successResponse($product, 'Product created successfully', 201);
        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage(), 500);
        }
    }


    public function edit(Request $request, $id)
    {
        try {
            $product = Product::find($id);
            if (!$product) {
                return $this->errorResponse('Product not found', 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'image' => ['nullable', 'image', 'mimes:jpg,png,jpeg'],
                'price' => ['nullable', 'numeric', 'min:0'],
                'color' => ['nullable', 'string', 'max:255'],
                'size' => ['nullable', 'string', 'max:255'],
                'category_id' => ['nullable', 'exists:categories,id'],
            ]);
            if ($validator->fails()) {
                return $this->errorResponse($validator->errors(), 422);
            }

            // Update only the provided fields
            $updatedData = [];
            if ($request->filled('name')) {
                $updatedData['name'] = $request->name;
            }
            if ($request->filled('description')) {
                $updatedData['description'] = $request->description;
            }
            if ($request->filled('price')) {
                $updatedData['price'] = $request->price;
            }
            if ($request->filled('color')) {
                $updatedData['color'] = $request->color;
            }
            if ($request->filled('size')) {
                $updatedData['size'] = $request->size;
            }
            if ($request->filled('category_id')) {
                $updatedData['category_id'] = $request->category_id;
            }

            // Update the image if provided
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->storeAs('products', $imageName);
                $updatedData['image'] = $imageName;
            }

            // Save the updated data
            $product->update($updatedData);

            return $this->successResponse($product, 'Product updated successfully', 200);
        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage(), 500);
        }
    }

    public function delete($id)
    {
        try {
            $product = Product::find($id);
            if (!$product) {
                return $this->errorResponse('Product not found', 404);
            }
            $deleted = $product->delete();
            if (!$deleted) {
                throw new \Exception('Failed to delete Product');
            }
            return $this->successResponse(null, 'Product deleted successfully', 200);
        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage(), 500);
        }
    }

    public function displayPopularProduct()
    {
        $user = auth()->user();
        try {
            $popularProducts = Product::with(['favorites' => function ($query) use ($user) {
                if ($user) {
                    $query->where('user_id', $user->id);
                }
            }])
                ->orderBy('sold_count', 'desc')
                ->take(12)
                ->get();

            if ($popularProducts->isEmpty()) {
                return $this->errorResponse('No Popular Products', 404);
            }

            $popularProducts->each(function ($product) use ($user) {
                $product->is_favorite = $product->favorites->isNotEmpty() && $user ? true : false;
                unset($product->favorites);
            });

            return $this->successResponse($popularProducts, null, 200);
        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage(), 500);
        }
    }


    public function getProductsByCategoryId(Request $request, $id)
    {
        $user = auth()->user();
        try {
            $products = Product::with(['favorites' => function ($query) use ($user) {
                if ($user) {
                    $query->where('user_id', $user->id);
                }
            }])->where('category_id', $id)
                ->with('category')
                ->get();
            // Add Attributes To Response
            $products->each(function ($product) use ($user) {
                $product->is_favorite = $product->favorites->isNotEmpty() && $user ? true : false;
                unset($product->favorites);
            });

            if ($products->isEmpty()) {
                return $this->errorResponse('No Products For This Category', 404);
            }
            return $this->successResponse($products, null, 200);
        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage(), 500);
        }
    }

    public function getProductById(Request $request, $id)
    {
        $user = auth()->user();
        try {
            $product = Product::with(['favorites'=>function($query) use ($user){
                if($user){
                    $query->where('user_id',$user->id);
                }
            }])->where('id',$id)
                ->get();

            $product->each(function ($product) use ($user) {
                $product->is_favorite = $product->favorites->isNotEmpty() && $user ? true : false;
//                unset($product->favorites);
            });
            if ($product->isEmpty()) {
                return $this->errorResponse('Product not found', 404);
            }
            return $this->successResponse($product, 'Product Retrieved Successfully', 200);
        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage(), 500);
        }
    }
    public function searchProductByName(Request $request)
    {
        try {
            $request->validate([
                'query' => 'required|string|max:255',
            ]);

            $query = $request->input('query');
            $user = auth()->user();

            $products = Product::with(['favorites' => function ($query) use ($user) {
                if ($user) {
                    $query->where('user_id', $user->id);
                }
            }])
                ->where('name', 'like', "%{$query}%")
                ->get();

            if ($products->isEmpty()) {
                return $this->errorResponse('No products found', 404);
            }

            $products->each(function ($product) use ($user) {
                $product->is_favorite = $product->favorites->isNotEmpty() && $user ? true : false;
                unset($product->favorites); // Removing favorites attribute
            });

            return $this->successResponse($products, null, 200);
        } catch (\Exception $exception) {
            // Handle any exceptions
            return $this->errorResponse($exception->getMessage(), 500);
        }
    }

}
