<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Enums\ActiveStatus;
use App\Enums\ServiceRequestStatus;
use App\Models\EmployeeAssignment;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class EmployeeDashboardService
{
    /**
     * Get live employee dashboard stats.
     *
     * @return array<string, mixed>
     */
    public function stats(): array
    {
        $actor = Auth::user();

        if (!$actor instanceof User) {
            return $this->emptyStats();
        }

        $primaryAssignment = $this->resolvePrimaryAssignment($actor);

        $submittedValue = ServiceRequestStatus::Submitted->value;
        $inProgressValue = ServiceRequestStatus::InProgress->value;
        $completedValue = ServiceRequestStatus::Completed->value;
        $rejectedValue = ServiceRequestStatus::Rejected->value;

        $baseRequestQuery = ServiceRequest::query()->where('requester_id', $actor->id);

        $requestStats = (clone $baseRequestQuery)
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as submitted_count', [$submittedValue])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as in_progress_count', [$inProgressValue])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed_count', [$completedValue])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as rejected_count', [$rejectedValue])
            ->first();

        $activeAssignmentsCount = EmployeeAssignment::query()
            ->where('user_id', $actor->id)
            ->where('active', ActiveStatus::ACTIVE->value)
            ->count();

        $totalAssignmentsCount = EmployeeAssignment::query()
            ->where('user_id', $actor->id)
            ->count();

        $lastUpdate = collect([
            (clone $baseRequestQuery)->max('updated_at'),
            EmployeeAssignment::query()->where('user_id', $actor->id)->max('updated_at'),
        ])->filter()->sortDesc()->first();

        $lastUpdateTime = formatDateTime($lastUpdate);

        $profile = $this->buildProfileData($actor, $primaryAssignment);
        $leadership = $this->buildLeadershipData($actor, $primaryAssignment);

        return [
            'dashboard' => [
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
            'activeAssignments' => [
                'count' => $activeAssignmentsCount,
                'lastUpdateTime' => $lastUpdateTime,
            ],
            'totalAssignments' => [
                'count' => $totalAssignmentsCount,
                'lastUpdateTime' => $lastUpdateTime,
            ],
            'profile' => $profile,
            'leadership' => $leadership,
        ];
    }

    private function resolvePrimaryAssignment(User $actor): ?EmployeeAssignment
    {
        $assignmentQuery = EmployeeAssignment::query()
            ->with([
                'hierarchy:id,level,title,type',
                'subCompany:id,name',
                'squad:id,name',
            ])
            ->where('user_id', $actor->id);

        return (clone $assignmentQuery)->active()->latest('updated_at')->first()
            ?? (clone $assignmentQuery)->latest('updated_at')->first();
    }

    /**
     * @return array<string, string>
     */
    private function buildProfileData(User $actor, ?EmployeeAssignment $primaryAssignment): array
    {
        return [
            'fullName' => $actor->full_name,
            'email' => $actor->email,
            'position' => $primaryAssignment?->hierarchy?->title ?? '-',
            'subCompany' => $primaryAssignment?->subCompany?->name ?? '-',
            'squad' => $primaryAssignment?->squad?->name ?? '-',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildLeadershipData(User $actor, ?EmployeeAssignment $primaryAssignment): array
    {
        if (!$primaryAssignment instanceof EmployeeAssignment) {
            return [
                'ceo' => null,
                'leader' => null,
                'upperPositions' => [],
            ];
        }

        $baseRelations = [
            'user:id,full_name',
            'hierarchy:id,level,title,type',
            'subCompany:id,name',
            'squad:id,name',
        ];

        $companyLeaders = EmployeeAssignment::query()
            ->with($baseRelations)
            ->where('user_id', '!=', $actor->id)
            ->where('sub_company_id', $primaryAssignment->sub_company_id)
            ->whereHas('hierarchy', function ($query): void {
                $query->where('type', 'company');
            })
            ->get()
            ->sortBy(static fn (EmployeeAssignment $assignment): int => (int) ($assignment->hierarchy?->level ?? 999))
            ->values();

        if ($companyLeaders->isEmpty()) {
            $companyLeaders = EmployeeAssignment::query()
                ->with($baseRelations)
                ->where('user_id', '!=', $actor->id)
                ->whereHas('hierarchy', function ($query): void {
                    $query->where('type', 'company');
                })
                ->get()
                ->sortBy(static fn (EmployeeAssignment $assignment): int => (int) ($assignment->hierarchy?->level ?? 999))
                ->values();
        }

        $upperSquadLeaders = collect();
        $currentLevel = (int) ($primaryAssignment->hierarchy?->level ?? 0);

        if ($primaryAssignment->squad_id !== null && $currentLevel > 0) {
            $upperSquadLeaders = EmployeeAssignment::query()
                ->with($baseRelations)
                ->where('user_id', '!=', $actor->id)
                ->where('squad_id', $primaryAssignment->squad_id)
                ->whereHas('hierarchy', function ($query) use ($currentLevel): void {
                    $query->where('type', 'squad')
                        ->where('level', '<', $currentLevel);
                })
                ->get()
                ->sortByDesc(static fn (EmployeeAssignment $assignment): int => (int) ($assignment->hierarchy?->level ?? 0))
                ->values();
        }

        $ceoAssignment = $companyLeaders->first(static function (EmployeeAssignment $assignment): bool {
            $title = strtolower((string) ($assignment->hierarchy?->title ?? ''));
            return str_contains($title, 'ceo');
        }) ?? $companyLeaders->first();

        $directLeaderAssignment = $upperSquadLeaders->first();

        $upperPositions = $companyLeaders
            ->concat($upperSquadLeaders)
            ->unique(static fn (EmployeeAssignment $assignment): string => $assignment->user_id . ':' . $assignment->hierarchy_id)
            ->map(static function (EmployeeAssignment $assignment): array {
                return [
                    'name' => $assignment->user?->full_name ?? '-',
                    'position' => $assignment->hierarchy?->title ?? '-',
                    'scope' => $assignment->hierarchy?->type === 'company'
                        ? ($assignment->subCompany?->name ?? '-')
                        : ($assignment->squad?->name ?? '-'),
                    'type' => $assignment->hierarchy?->type ?? 'company',
                    'level' => (int) ($assignment->hierarchy?->level ?? 999),
                ];
            })
            ->sortBy(static fn (array $item): int => ($item['type'] === 'company' ? 0 : 1000) + (int) $item['level'])
            ->values()
            ->all();

        return [
            'ceo' => $ceoAssignment
                ? [
                    'name' => $ceoAssignment->user?->full_name ?? '-',
                    'position' => $ceoAssignment->hierarchy?->title ?? '-',
                ]
                : null,
            'leader' => $directLeaderAssignment
                ? [
                    'name' => $directLeaderAssignment->user?->full_name ?? '-',
                    'position' => $directLeaderAssignment->hierarchy?->title ?? '-',
                ]
                : null,
            'upperPositions' => $upperPositions,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyStats(): array
    {
        return [
            'dashboard' => ['lastUpdateTime' => null],
            'totalRequests' => ['count' => 0, 'lastUpdateTime' => null],
            'submittedRequests' => ['count' => 0, 'lastUpdateTime' => null],
            'inProgressRequests' => ['count' => 0, 'lastUpdateTime' => null],
            'completedRequests' => ['count' => 0, 'lastUpdateTime' => null],
            'rejectedRequests' => ['count' => 0, 'lastUpdateTime' => null],
            'activeAssignments' => ['count' => 0, 'lastUpdateTime' => null],
            'totalAssignments' => ['count' => 0, 'lastUpdateTime' => null],
            'profile' => [
                'fullName' => '-',
                'email' => '-',
                'position' => '-',
                'subCompany' => '-',
                'squad' => '-',
            ],
            'leadership' => [
                'ceo' => null,
                'leader' => null,
                'upperPositions' => [],
            ],
        ];
    }
}
