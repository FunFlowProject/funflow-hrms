<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class UserObserver
{
    /**
     * Handle the User "creating" event.
     */
    public function creating(User $user): void
    {
        if (blank($user->username)) {
            $user->username = $this->generateUniqueUsername($user);
        }
    }

    /**
     * Generate a unique username based on full name and hire date.
     */
    private function generateUniqueUsername(User $user): string
    {
        $base = Str::slug((string) $user->full_name, '.') ?: 'employee';

        $year = $user->hire_date 
            ? Carbon::parse($user->hire_date)->format('Y') 
            : now()->format('Y');

        $candidate = "{$base}.{$year}";
        $suffix = 1;

        while (User::query()->where('username', $candidate)->exists()) {
            $candidate = "{$base}.{$year}.{$suffix}";
            $suffix++;
        }

        return $candidate;
    }
}