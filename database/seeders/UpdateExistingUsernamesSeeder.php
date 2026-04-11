<?php

namespace Database\Seeders;

use App\Models\Sequence;
use App\Models\User;
use App\Notifications\UserCredentialsUpdatedNotification;
use App\Services\SequenceService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UpdateExistingUsernamesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Initialize the sequence if it doesn't exist, or reset it if needed.
        // We'll start from 1001.
        $startingValue = 1000;
        
        // Reset the sequence for this migration to ensure we start from 1001 for everyone.
        Sequence::query()->updateOrCreate(
            ['name' => 'user_id'],
            ['current_value' => $startingValue]
        );

        // 2. Fetch all users ordered by ID to maintain a somewhat logical sequence.
        $users = User::query()->orderBy('id')->get();

        $this->command->info('Updating ' . $users->count() . ' users...');

        foreach ($users as $user) {
            // 3. Get next sequence value.
            $nextValue = SequenceService::nextValue('user_id', $startingValue);

            // 4. Generate new username.
            $newUsername = $this->generateNewUsername($user, $nextValue);

            $this->command->info("Updating {$user->full_name}: {$user->username} -> {$newUsername}");

            // 5. Update user.
            $oldUsername = $user->username;
            $user->update([
                'username' => $newUsername,
            ]);

            // 6. Notify user of the change using the generic notification.
            try {
                $user->notify(new \App\Notifications\AccountUpdatedNotification([
                    'Username' => ['old' => $oldUsername, 'new' => $newUsername],
                ]));
                $this->command->info("Notification sent to {$user->email}");
            } catch (\Exception $e) {
                $this->command->error("Failed to notify {$user->email}: " . $e->getMessage());
            }
        }

        $this->command->info('All users updated successfully!');
    }

    /**
     * Generate a unique username based on the full name and the sequence.
     */
    private function generateNewUsername(User $user, int $sequenceValue): string
    {
        $fullName = $user->full_name;
        $nameParts = array_values(array_filter(preg_split('/\s+/', trim($fullName))));

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

        return (string) $base . $sequenceValue;
    }
}
