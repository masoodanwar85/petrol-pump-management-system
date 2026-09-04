<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthService
{
    /**
     * @return array{user: User, token: string, token_type: string}
     */
    public function login(string $email, string $password, string $deviceName): array
    {
        $user = User::query()->where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw new BusinessException(
                'The provided credentials are incorrect.',
                Response::HTTP_UNPROCESSABLE_ENTITY,
                ['email' => ['The provided credentials are incorrect.']],
            );
        }

        $user->tokens()->where('name', $deviceName)->delete();

        return [
            'user' => $user,
            'token' => $user->createToken($deviceName)->plainTextToken,
            'token_type' => 'Bearer',
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }
}
