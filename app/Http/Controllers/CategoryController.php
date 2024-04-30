<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;

class CategoryController extends Controller
{
    use ApiResponseTrait;
    public function index(){
        try {
            $categoris = Category::all();
            if(!$categoris){
                return $this->errorResponse('No Categories Founded',404);
            }
            return $this->successResponse($categoris,null,200);
        }catch (\Exception $exception){
            return $this->errorResponse($exception->getMessage(),500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255'],
                'image' => ['required', 'image', 'mimes:jpg,png,jpeg'],
            ]);
            if ($validator->fails()) {
                return $this->errorResponse($validator->errors(), 422);
            }
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->storeAs('categories', $imageName);

            $category = Category::create([
                'name' => $request->name,
                'image' => $imageName,
            ]);
            if (!$category) {
                throw new \Exception('Failed to create category');
            }
            return $this->successResponse($category, 'Category created successfully', 201);
        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage(), 500);
        }
    }

    public function edit(Request $request, $id)
    {
        try {
            $category = Category::find($id);
            if (!$category) {
                return $this->errorResponse('Category not found', 404);
            }
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255'],
                'image' => ['nullable','image', 'mimes:jpg,png,jpeg'],
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors(), 422);
            }
            $category->name = $request->name;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->storeAs('categories', $imageName);
            }
            $category->save();
            return $this->successResponse($category, 'Category updated successfully', 200);
        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage(), 500);
        }
    }


    public function delete($id)
    {
        try {
            $category = Category::find($id);
            if (!$category) {
                return $this->errorResponse('Category not found', 404);
            }
            $deleted = $category->delete();
            if (!$deleted) {
                throw new \Exception('Failed to delete category');
            }
            return $this->successResponse(null, 'Category deleted successfully', 200);
        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage(), 500);
        }
    }

}
