<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;

class VerifyToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $token = $request->bearerToken();
            if ($token) {
                JWTAuth::parseToken()->authenticate();
            } else {
                return response()->json(['message' => 'Token not provided'], 401);
            }
        }catch (TokenInvalidException $e) {
            return response()->json(['message' => 'Token is invalid'], 401);
        } catch (TokenExpiredException $e) {
            return response()->json(['message' => 'Token is expired'], 401);
        } catch (JWTException $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return $next($request);
    }
}
