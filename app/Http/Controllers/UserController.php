<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

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
            $user = auth()->user();

            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $user->update([
                'name' => $validatedData['name'],
            ]);

            if ($request->has('email')) {
                $validatedData = $request->validate([
                    'email' => 'required|email|unique:users,email,' . $user->id,
                ]);

                $user->update([
                    'email' => $validatedData['email'],
                ]);
            }

            if ($request->has('password')) {
                $validatedData = $request->validate([
                    'password' => 'required|string|min:8',
                ]);

                $user->update([
                    'password' => bcrypt($validatedData['password']),
                ]);
            }

            return $this->successResponse($user, 'Profile updated successfully', 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
