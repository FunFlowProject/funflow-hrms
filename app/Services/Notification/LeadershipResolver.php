<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Models\EmployeeAssignment;
use App\Models\Hierarchy;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class LeadershipResolver
{
    private const GROUP_CEO_TITLE = 'Group CEO';
    private const SQUAD_CEO_TITLE = 'Squad CEO';
    private const SQUAD_OWNER_TITLE = 'Squad Owner';

    /**
     * Resolve the organizational leaders for a given user.
     * 
     * @return Collection<int, User>
     */
    public function getLeaders(User $user): Collection
    {
        $assignments = $user->assignments()
            ->active()
            ->get(['id', 'user_id', 'sub_company_id', 'squad_id', 'hierarchy_id', 'active']);

        if ($assignments->isEmpty()) {
            return collect();
        }

        $squadIds = $assignments->pluck('squad_id')->filter()->unique()->values();
        $subCompanyIds = $assignments->pluck('sub_company_id')->filter()->unique()->values();

        $squadHierarchyIds = Hierarchy::query()
            ->active()
            ->where('type', 'squad')
            ->whereIn('title', [self::SQUAD_OWNER_TITLE, self::SQUAD_CEO_TITLE])
            ->pluck('id');

        $groupCeoHierarchyIds = Hierarchy::query()
            ->active()
            ->where('type', 'company')
            ->where('title', self::GROUP_CEO_TITLE)
            ->pluck('id');

        $hasSquadScope = $squadIds->isNotEmpty() && $squadHierarchyIds->isNotEmpty();
        $hasCompanyScope = $subCompanyIds->isNotEmpty() && $groupCeoHierarchyIds->isNotEmpty();

        if (!$hasSquadScope && !$hasCompanyScope) {
            return collect();
        }

        $leadershipAssignments = EmployeeAssignment::query()
            ->active()
            ->where(function (Builder $query) use ($hasSquadScope, $squadIds, $squadHierarchyIds, $hasCompanyScope, $subCompanyIds, $groupCeoHierarchyIds) {
                if ($hasSquadScope) {
                    $query->orWhere(function (Builder $nested) use ($squadIds, $squadHierarchyIds) {
                        $nested->whereIn('squad_id', $squadIds)
                            ->whereIn('hierarchy_id', $squadHierarchyIds);
                    });
                }

                if ($hasCompanyScope) {
                    $query->orWhere(function (Builder $nested) use ($subCompanyIds, $groupCeoHierarchyIds) {
                        $nested->whereIn('sub_company_id', $subCompanyIds)
                            ->whereIn('hierarchy_id', $groupCeoHierarchyIds)
                            ->whereNull('squad_id');
                    });
                }
            })
            ->with(['user:id,full_name,email'])
            ->get();

        return $leadershipAssignments
            ->map(fn (EmployeeAssignment $assignment) => $assignment->user)
            ->filter()
            ->unique('id')
            ->values();
    }
}
