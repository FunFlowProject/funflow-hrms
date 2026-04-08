<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Enums\ActiveStatus;
use App\Enums\EmployeeStatus;
use App\Enums\ServiceRequestStatus;
use App\Models\EmployeeAssignment;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class HrDashboardService
{
    /**
     * Get live HR dashboard stats.
     *
     * @return array<string, array<string, int|string|null>>
     */
    public function stats(): array
    {
        $pendingValue = EmployeeStatus::Pending->value;
        $onboardingValue = EmployeeStatus::Onboarding->value;
        $joinedValue = EmployeeStatus::Joined->value;

        $submittedValue = ServiceRequestStatus::Submitted->value;
        $inProgressValue = ServiceRequestStatus::InProgress->value;
        $completedValue = ServiceRequestStatus::Completed->value;
        $rejectedValue = ServiceRequestStatus::Rejected->value;

        $actorId = (int) (Auth::id() ?? 0);

        $employeeStats = User::query()
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending_count', [$pendingValue])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as onboarding_count', [$onboardingValue])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as joined_count', [$joinedValue])
            ->first();

        $requestStats = ServiceRequest::query()
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as submitted_count', [$submittedValue])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as in_progress_count', [$inProgressValue])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed_count', [$completedValue])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as rejected_count', [$rejectedValue])
            ->selectRaw('SUM(CASE WHEN status IN (?, ?) AND handled_by IS NULL THEN 1 ELSE 0 END) as unassigned_open_count', [$submittedValue, $inProgressValue])
            ->selectRaw('SUM(CASE WHEN status IN (?, ?) AND handled_by = ? THEN 1 ELSE 0 END) as my_open_count', [$submittedValue, $inProgressValue, $actorId])
            ->first();

        $activeAssignmentsCount = EmployeeAssignment::query()
            ->where('active', ActiveStatus::ACTIVE->value)
            ->count();

        $lastUpdate = collect([
            User::query()->max('updated_at'),
            EmployeeAssignment::query()->max('updated_at'),
            ServiceRequest::query()->max('updated_at'),
        ])->filter()->sortDesc()->first();

        $lastUpdateTime = formatDateTime($lastUpdate);

        return [
            'dashboard' => [
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
            'activeAssignments' => [
                'count' => $activeAssignmentsCount,
                'lastUpdateTime' => $lastUpdateTime,
            ],
            'totalRequests' => [
                'count' => (int) ($requestStats->total_count ?? 0),
                'lastUpdateTime' => $lastUpdateTime,
            ],
            'submittedRequests' => [
                'count' => (int) ($requestStats->submitted_count ?? 0),
                'lastUpdateTime' => $lastUpdateTime,
            ],
            'inProgressRequests' => [
                'count' => (int) ($requestStats->in_progress_count ?? 0),
                'lastUpdateTime' => $lastUpdateTime,
            ],
            'completedRequests' => [
                'count' => (int) ($requestStats->completed_count ?? 0),
                'lastUpdateTime' => $lastUpdateTime,
            ],
            'rejectedRequests' => [
                'count' => (int) ($requestStats->rejected_count ?? 0),
                'lastUpdateTime' => $lastUpdateTime,
            ],
            'unassignedOpenRequests' => [
                'count' => (int) ($requestStats->unassigned_open_count ?? 0),
                'lastUpdateTime' => $lastUpdateTime,
            ],
            'myOpenRequests' => [
                'count' => (int) ($requestStats->my_open_count ?? 0),
                'lastUpdateTime' => $lastUpdateTime,
            ],
        ];
    }
}
