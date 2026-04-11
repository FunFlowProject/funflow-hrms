<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Str;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        if (blank($user->username)) {
            $user->username = $this->generateUsernameFromId($user);
            $user->saveQuietly();
        }
    }

    /**
     * Generate a username based on first name initial, last name, and the generated ID.
     */
    private function generateUsernameFromId(User $user): string
    {
        $nameParts = array_values(array_filter(preg_split('/\s+/', trim((string) $user->full_name))));

        $firstInitial = '';
        $lastName = 'employee';

        if (!empty($nameParts)) {
            $firstInitial = mb_substr(mb_strtolower($nameParts[0]), 0, 1);
            $lastName = mb_strtolower($nameParts[count($nameParts) - 1]);
        }

        $base = Str::of($firstInitial . $lastName)
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', '')
            ->whenEmpty(fn () => 'employee');

        return (string) $base . $user->id;
    }
}