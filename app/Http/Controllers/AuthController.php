<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'status' => 200,
            'message' => 'User registered successfully',
            'data' => [
                'user'  => $user,
                'token' => $token,
            ]
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (! $token = JWTAuth::attempt($credentials)) {
            return response()->json(['status' => 401, 'message' => 'Invalid credentials'], 401);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Login success',
            'data' => [
                'token' => $token,
                'user'  => auth()->user(),
            ]
        ]);
    }

    public function logout()
    {
        auth('api')->logout();

        return response()->json(['status' => 200, 'message' => 'Logged out successfully']);
    }

    public function refresh()
    {
        return response()->json([
            'status' => 200,
            'message' => 'Token refreshed',
            'data' => [
                'token' => auth('api')->refresh()
            ]
        ]);
    }

    public function me()
    {
        return response()->json([
            'status' => 200,
            'message' => 'Authenticated user',
            'data' => auth()->user()
        ]);
    }
}
