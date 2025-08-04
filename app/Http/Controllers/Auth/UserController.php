<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\User;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function login(Request $request)
    {
        $credentials = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ])->validate();

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();

        // Create welcome notification
        \App\Models\Notification::create([
            'user_id' => $user->id,
            'type' => 'login_success',
            'data' => [
                'login_time' => now()->toISOString(),
                'user_name' => $user->first_name . ' ' . $user->last_name
            ]
        ]);

        return response()->json([
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60
        ], 200);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['message' => 'Successfully logged out'], 200);
    }

    public function refresh(Request $request)
    {
        try {
            $token = JWTAuth::refresh(JWTAuth::getToken());

            return response()->json([
                'message' => 'Token refreshed successfully',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60
            ], 200);
        } catch (TokenExpiredException $e) {
            return response()->json([
                'message' => 'Token expired and cannot be refreshed',
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
    }

    public function me(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return response()->json([
            'message' => 'User details retrieved successfully',
            'user' => $user
        ], 200);
    }


    public function forgotPassword(Request $request)
    {
        $credentials = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255'],
        ])->validate();

        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            return response()->json(['message' => 'Email not found'], 404);
        }

        return response()->json(['message' => 'Password reset link sent'], 200);
    }

    public function deleteAccount()
    {
        $authUser = Auth::user();

        if (!$authUser) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user = User::where('id', $authUser->id)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->id !== $authUser->id) {
            return response()->json(['message' => 'You can only delete your own account'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'Account deleted successfully'], 200);
    }

    public function register(Request $request)
    {
        $credentials = Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'username' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'birth_date' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'numeric', 'digits_between:1,20'],
            'phone' => ['nullable', 'string', 'max:20'],
        ])->validate();

        $user = User::create([
            'first_name' => $credentials['first_name'],
            'last_name' => $credentials['last_name'],
            'email' => $credentials['email'],
            'username' => $credentials['username'],
            'middle_name' => $credentials['middle_name'] ?? null,
            'password' => bcrypt($credentials['password']),
            'birth_date' => $credentials['birth_date'] ?? null,
            'address' => $credentials['address'] ?? null,
            'city' => $credentials['city'] ?? null,
            'state' => $credentials['state'] ?? null,
            'country' => $credentials['country'] ?? null,
            'postal_code' => $credentials['postal_code'] ?? null,
            'phone' => $credentials['phone'] ?? null,
        ]);
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => [
                'username' => $user->username,
                'email' => $user->email,
            ],
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60
        ], 201);
    }

    // Admin
    public function deleteUserAccount($userId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($user->role->name !== 'admin') {
            return response()->json(['message' => 'You do not have permission to delete user accounts'], 403);
        }

        if ($user->role->name === 'admin' && $user->id === $userId) {
            return response()->json(['message' => 'You cannot delete your own account'], 403);
        }

        if ($user->role->name === 'admin') {

            $userToDelete = User::where('id', $userId)->first();

            if (!$userToDelete) {
                return response()->json(['message' => 'User not found'], 404);
            }

            $userToDelete->delete();

            return response()->json(['message' => 'User account deleted successfully'], 200);
        }
    }
}
