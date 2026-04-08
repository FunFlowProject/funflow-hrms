<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginService
{
    public function attempt(string $login, string $password, bool $remember = false): array
    {
        $loginValue = trim($login);

        $user = User::query()
            ->where('username', $loginValue)
            ->orWhere('email', $loginValue)
            ->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return [
                'success' => false,
                'status' => 422,
                'message' => 'The provided credentials do not match our records.',
            ];
        }

        Auth::guard('web')->login($user, $remember);

        return [
            'success' => true,
            'status' => 200,
            'message' => 'Login successful.',
            'user' => $user,
        ];
    }

    public function logout(): void
    {
        Auth::guard('web')->logout();
    }
}
