<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create($validated);

        $token = $user->createToken('api-token', ['*'], now()->addMinutes(30))->plainTextToken;

        return response()->api([
            'token' => $token,
            'user' => new UserResource($user),
        ], true, 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
          $validated = $request->validated();

          $user = User::where('email', $validated['email'])->first();

          if (!$user || !Hash::check($validated['password'], $user->password)) {
              return response()->api([
                  'message' => 'The provided credentials are incorrect.',
              ], false, 401);
          }

          $token = $user->createToken('api-token', ['*'], now()->addMinutes(30))->plainTextToken;

        return response()->api([
            'token' => $token,
            'user'  => new UserResource($user),
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

        return response()->api([
            'message' => 'You have been logged out.',
        ]);
    }
}
