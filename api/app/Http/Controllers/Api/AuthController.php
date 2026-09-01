<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)
                    ->where('active', true)
                    ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Identifiants incorrects.'],
            ]);
        }

        // Révoquer les tokens existants du même appareil
        $user->tokens()->where('name', $request->device_name ?? 'api')->delete();

        $token = $user->createToken(
            $request->device_name ?? 'api',
            $this->abilitiesFor($user->role)
        )->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'         => $user->id,
                'first_name' => $user->first_name,
                'last_name'  => $user->last_name,
                'email'      => $user->email,
                'role'       => $user->role,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnecté.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    private function abilitiesFor(string $role): array
    {
        return match ($role) {
            'admin'     => ['*'],
            'secretary' => ['matches:score', 'matches:events', 'matches:lineup', 'matches:read', 'teams:read'],
            'referee'   => ['matches:read', 'teams:read'],
            default     => ['matches:read', 'standings:read', 'teams:read'],
        };
    }
}
