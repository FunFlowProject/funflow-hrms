<?php

namespace Database\Seeders;

use App\Enums\SystemRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    private const GUARD_NAME = 'web';

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $employeePermissions = [
            'employees.view',
            'employees.create',
            'employees.update',
            'employees.destroy',
            'employees.approve',
            'employees.reject',
            'employees.assign',
            'employees.move-to-onboarding',
            'employees.confirm-join',
        ];

        $subCompanyPermissions = [
            'sub-companies.view',
            'sub-companies.create',
            'sub-companies.update',
            'sub-companies.destroy',
        ];

        $squadPermissions = [
            'squads.view',
            'squads.create',
            'squads.update',
            'squads.destroy',
        ];

        $serviceCatalogPermissions = [
            'service-catalog.view',
            'service-catalog.create',
            'service-catalog.update',
            'service-catalog.destroy',
        ];

        $serviceRequestPermissions = [
            'service-requests.view',
            'service-requests.create',
            'service-requests.manage',
            'service-requests.transition',
        ];

        $documentPermissions = [
            'documents.view',
            'documents.my.view',
            'documents.create',
            'documents.update',
            'documents.destroy',
        ];

        $educationalPermissions = [
            'educational-objectives.manage-all',
            'educational-objectives.manage',
            'educational-objectives.my.view',
            'educational-objectives.view',
        ];

        $workLogPermissions = [
            'work-logs.my.view',
            'work-logs.my.create',
            'work-logs.view-all',
            'work-logs.manage',
        ];

        $permissions = array_values(array_unique(array_merge(
            $employeePermissions,
            $subCompanyPermissions,
            $squadPermissions,
            $serviceCatalogPermissions,
            $serviceRequestPermissions,
            $documentPermissions,
            $educationalPermissions,
            $workLogPermissions,
        )));

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(
                [
                    'name' => $permissionName,
                    'guard_name' => self::GUARD_NAME,
                ]
            );
        }

        // Ensure newly created permissions are available for immediate role sync.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $rolePermissions = [
            'admin' => array_values(array_filter($permissions, fn($p) => !str_ends_with($p, '.my.view'))),
            'hr' => array_values(array_filter([
                'employees.view',
                'employees.create',
                'employees.update',
                'employees.approve',
                'employees.reject',
                'employees.assign',
                'employees.move-to-onboarding',
                'employees.confirm-join',
                'sub-companies.view',
                'sub-companies.create',
                'sub-companies.update',
                'squads.view',
                'squads.create',
                'squads.update',
                'documents.view',
                'documents.create',
                'documents.update',
                'documents.destroy',
                'educational-objectives.manage-all', // HR can manage all
                'work-logs.my.view',
                'work-logs.my.create',
                'work-logs.view-all',
                'work-logs.manage',
            ], fn($p) => !str_ends_with($p, '.my.view'))),
            'employee' => [
                'service-requests.view',
                'documents.my.view',
                'educational-objectives.my.view',
                'educational-objectives.view',
                'work-logs.my.view',
                'work-logs.my.create',
            ],
        ];

        foreach ($rolePermissions as $roleName => $rolePermissionNames) {
            $role = Role::findOrCreate($roleName, self::GUARD_NAME);

            $permissionModels = Permission::query()
                ->where('guard_name', self::GUARD_NAME)
                ->whereIn('name', $rolePermissionNames)
                ->get();

            $role->syncPermissions($permissionModels);
        }

        User::query()
            ->select(['id', 'system_role'])
            ->whereNotNull('system_role')
            ->get()
            ->each(function (User $user): void {
                $roleValue = $user->system_role instanceof SystemRole
                    ? $user->system_role->value
                    : (string) $user->system_role;

                $roleName = match ($roleValue) {
                    SystemRole::Admin->value => 'admin',
                    SystemRole::Hr->value => 'hr',
                    SystemRole::Employee->value => 'employee',
                    default => null,
                };

                if ($roleName === null) {
                    return;
                }

                $user->syncRoles([$roleName]);
            });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}