<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

class PasswordResetService
{
    /**
     * Send a password reset link to the given user.
     *
     * @param  array  $credentials
     * @return array
     */
    public function sendResetLink(array $credentials): array
    {
        $status = Password::broker()->sendResetLink($credentials);

        if ($status === Password::RESET_LINK_SENT) {
            return [
                'success' => true,
                'status'  => __($status),
            ];
        }

        return [
            'success' => false,
            'status'  => __($status),
        ];
    }

    /**
     * Reset the given user's password.
     *
     * @param  array  $credentials
     * @return array
     */
    public function resetPassword(array $credentials): array
    {
        $status = Password::broker()->reset(
            $credentials,
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return [
                'success' => true,
                'status'  => __($status),
            ];
        }

        return [
            'success' => false,
            'status'  => __($status),
        ];
    }
}
