<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        $token = $user->createToken('api-token', ['*'], now()->addMinutes(30))->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'token' => $token,
            'user' => $user,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
          $validated = $request->validate([
              'email' => 'required|string|email|max:255|exists:users',
              'password' => 'required|string|min:6',
          ]);

          $user = User::where('email', $validated['email'])->first();

          if (!$user || !Hash::check($validated['password'], $user->password)) {
              return response()->json([
                  'message' => 'The provided credentials are incorrect.',
              ]);
          }

          $token = $user->createToken('api-token', ['*'], now()->addMinutes(30))->plainTextToken;

          return response()->json([
              'message' => 'User logged in successfully',
              'token' => $token,
          ]);
    }

    public function logout(): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $token = $user->currentAccessToken();

        if ($token && method_exists($token, 'delete')) {
            $token->delete();
        }

        return response()->json([
            'message' => 'You have been logged out.',
        ]);
    }
}
