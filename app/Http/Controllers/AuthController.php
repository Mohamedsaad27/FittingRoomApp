<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    use ApiResponseTrait;
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function  register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'unique:users,email', 'email', 'max:255'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors(),422);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);

            // Generate Token
            $token = JWTAuth::fromUser($user);
        return  $this->successResponse($user,'User Registered Successfully',200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(),],500);
        }
    }
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
            ]);
            if ($validator->fails()) {
                return $this->errorResponse($validator->errors(),422);
            }

            if (!$token = JWTAuth::attempt($request->only('email', 'password'))) {
                return $this->errorResponse('Invalid email or Password',401);
            }
            $user = auth()->user();

            return $this->successResponse([
                'user' => $user,
                'token' => $token,
            ], 'User logged in successfully', 200);
        } catch (\Exception $e) {
        return $this->errorResponse($e->getMessage(),500);
        }
    }
    public function logout() {
        auth()->logout();
        return $this->successResponse(null, 'User successfully logged out', 200);
    }
    public function refresh() {
        try {
            return $this->createNewToken(auth()->refresh());
        }catch (\Exception $exception){
            return  $this->errorResponse($exception->getMessage(),500);
        }
    }
    protected function createNewToken($token){
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }
}
