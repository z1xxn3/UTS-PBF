<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UserLoginRequest;
use App\Http\Requests\Api\UserRegisterRequest;
use App\Http\Resources\Api\UserRegisterResource;
use App\Models\User;
use App\Services\JwtService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function __construct(private JwtService $jwtService)
    {
        //
    }

    public function register(UserRegisterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = User::create($data);

        return response()->json([
            'success' => true,
            'data' => $user->toArray()
        ], 200);
    }

    public function login(UserLoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (!Auth::attempt($data)) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'message' => [
                        'Email atau Password salah.'
                    ]
                ]
            ]);
        }

        $user = Auth::user();
        $payload = [
            'user' => [
                'id' => $user->id
            ]
        ];
        $token = $this->jwtService->encode($payload);

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token
            ]
        ]);
    }

    public function oAuthRedirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function oAuthCallback()
    {
        $googleUser = Socialite::driver('google')->user();
        return response()->json($googleUser);
    }
}
