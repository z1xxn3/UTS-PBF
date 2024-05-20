<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\JwtService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Nette\Utils\Random;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AuthController extends Controller
{
    public function __construct(private JwtService $jwtService)
    {
        //
    }

    public function oAuthRedirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function oAuthCallback(): JsonResponse
    {
        $googleUser = Socialite::driver('google')->user();

        if (!$googleUser->id) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'message' => [
                        'Terdapat kesalahan saat Otentikasi.'
                    ]
                ]
            ], 400);
        }

        $user = User::where('google_id', $googleUser->id)->first();

        if (!$user) {
            $user = new User();
            $user->name = $googleUser->name;
            $user->google_id = $googleUser->id;
            $user->google_token = $googleUser->token;
            $user->google_refresh_token = $googleUser->refreshToken;
            $user->password = Hash::make(Random::generate(10));
            $user->email = $googleUser->email;
            $user->save();
        }

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
}
