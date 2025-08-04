<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthJwt
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (TokenExpiredException $e) {
            return response()->json([
                'message' => 'Token expired',
                'error' => 'TOKEN_EXPIRED',
                'status' => 401
            ], 401);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'message' => 'Token invalid',
                'error' => 'TOKEN_INVALID',
                'status' => 401
            ], 401);
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Token absent or invalid',
                'error' => 'TOKEN_ABSENT',
                'status' => 401
            ], 401);
        }

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
                'error' => 'UNAUTHORIZED',
                'status' => 401
            ], 401);
        }

        $role = $user->role?->name ?? 'user';
        $request->attributes->set('user_role', $role);

        return $next($request);
    }
}
