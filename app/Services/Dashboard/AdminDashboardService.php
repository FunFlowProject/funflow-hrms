<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Enums\ActiveStatus;
use App\Enums\EmployeeStatus;
use App\Models\EmployeeAssignment;
use App\Models\Squad;
use App\Models\SubCompany;
use App\Models\User;

class AdminDashboardService
{
    /**
     * Get live dashboard stats.
     *
     * @return array<string, array<string, int|string|null>>
     */
    public function stats(): array
    {
        $pendingValue = EmployeeStatus::Pending->value;
        $onboardingValue = EmployeeStatus::Onboarding->value;
        $joinedValue = EmployeeStatus::Joined->value;
        $terminatedValue = EmployeeStatus::Terminated->value;

        $employeeStats = User::employees()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending_count', [$pendingValue])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as onboarding_count', [$onboardingValue])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as joined_count', [$joinedValue])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as terminated_count', [$terminatedValue])
            ->first();

        $subCompaniesCount = SubCompany::query()->count();
        $squadsCount = Squad::query()->count();
        $activeAssignmentsCount = EmployeeAssignment::query()->where('active', ActiveStatus::ACTIVE->value)->count();

        $lastUpdate = collect([
            User::query()->max('updated_at'),
            SubCompany::query()->max('updated_at'),
            Squad::query()->max('updated_at'),
            EmployeeAssignment::query()->max('updated_at'),
        ])->filter()->sortDesc()->first();

        $lastUpdateTime = formatDateTime($lastUpdate);

        return [
            'dashboard' => [
                'lastUpdateTime' => $lastUpdateTime,
            ],
            'employees' => [
                'count' => (int) ($employeeStats->total ?? 0),
                'lastUpdateTime' => $lastUpdateTime,
            ],
            'pendingEmployees' => [
                'count' => (int) ($employeeStats->pending_count ?? 0),
                'lastUpdateTime' => $lastUpdateTime,
            ],
            'onboardingEmployees' => [
                'count' => (int) ($employeeStats->onboarding_count ?? 0),
                'lastUpdateTime' => $lastUpdateTime,
            ],
            'joinedEmployees' => [
                'count' => (int) ($employeeStats->joined_count ?? 0),
                'lastUpdateTime' => $lastUpdateTime,
            ],
            'terminatedEmployees' => [
                'count' => (int) ($employeeStats->terminated_count ?? 0),
                'lastUpdateTime' => $lastUpdateTime,
            ],
            'subCompanies' => [
                'count' => $subCompaniesCount,
                'lastUpdateTime' => $lastUpdateTime,
            ],
            'squads' => [
                'count' => $squadsCount,
                'lastUpdateTime' => $lastUpdateTime,
            ],
            'activeAssignments' => [
                'count' => $activeAssignmentsCount,
                'lastUpdateTime' => $lastUpdateTime,
            ],
        ];
    }
}
