<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Status;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Requests\Storefront\Auth\LoginRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use App\Services\Storefront\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, CartService $carts): JsonResponse
    {
        $user = new User;
        $user->forceFill([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'password' => $request->validated('password'),
            'role' => UserRole::Customer,
            'status' => Status::Active,
        ])->save();

        $user->profile()->create([
            'city' => 'Abidjan',
            'country' => "Côte d'Ivoire",
        ]);

        return $this->issueToken($user, $request, $carts, 201);
    }

    public function login(LoginRequest $request, CartService $carts): JsonResponse
    {
        $user = $request->userFromCredentials();

        return $this->issueToken($user, $request, $carts);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['message' => 'Déconnecté.']);
    }

    private function issueToken(User $user, Request $request, CartService $carts, int $status = 200): JsonResponse
    {
        Auth::setUser($user);
        $carts->mergeGuestCartByToken($user, $request->header('X-Cart-Token'));

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => (new UserResource($user->load('profile')))->resolve(),
        ], $status);
    }
}
