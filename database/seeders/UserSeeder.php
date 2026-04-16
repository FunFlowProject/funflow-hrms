<?php

namespace Database\Seeders;

use App\Enums\ContractType;
use App\Enums\EmployeeStatus;
use App\Enums\SystemRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    private const GUARD_NAME = 'web';

    public function run(): void
    {
        Role::findOrCreate(SystemRole::Admin->value, self::GUARD_NAME);

        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@funflow.local'],
            [
                'full_name' => 'System Administrator',
                'username' => 'admin',
                'phone_number' => '+10000000000',
                'date_of_birth' => '1990-01-01',
                'hire_date' => now()->toDateString(),
                'contract_type' => ContractType::FullTime->value,
                'system_role' => SystemRole::Admin->value,
                'status' => EmployeeStatus::Joined->value,
                'email_verified_at' => now(),
                'password' => 'password',
            ]
        );

        if ($admin->system_role !== SystemRole::Admin || $admin->status !== EmployeeStatus::Joined) {
            $admin->forceFill([
                'system_role' => SystemRole::Admin,
                'status' => EmployeeStatus::Joined,
            ])->save();
        }

        $admin->syncRoles([SystemRole::Admin->value]);
    }
}
