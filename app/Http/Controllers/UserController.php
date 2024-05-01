<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    use ApiResponseTrait;
    public function getAuthenticatedUser(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return $this->errorResponse('User not found', 404);
            }
            return $this->successResponse($user, 'Authenticated user data retrieved successfully', 200);
        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage(), 500);
        }
    }
    public function editProfileData(Request $request)
    {
        try {
            $user = auth()->user(); // Get the authenticated user

            // Validate the request data
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'nullable|string|email|max:255|unique:users,email,'.$user->id,
                'password' => 'nullable|string|min:8',
            ]);

            // Update user's name
            $user->name = $validatedData['name'];

            // Update user's email
            $user->email = $validatedData['email'];

            // Update user's password if provided
            if (isset($validatedData['password'])) {
                $user->password = Hash::make($validatedData['password']);
            }

            // Save the changes
            $user->save();

            return response()->json([$user,'message' => 'Profile updated successfully'], 200);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->validator->errors()->first()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update profile.'], 500);
        }
    }
}
