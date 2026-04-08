<?php

declare(strict_types=1);

namespace App\Services\Organization;

use App\DTOs\Organization\SquadDto;
use App\DTOs\Organization\SquadStatsDto;
use App\Enums\ActiveStatus;
use App\Exceptions\BusinessException;
use App\Models\Squad;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Yajra\DataTables\Facades\DataTables;

class SquadService
{
    private const CACHE_PREFIX = 'squads';

    /**
     * Get all squads in lightweight format.
     */
    public function all(): array
    {
        return Cache::rememberForever(self::CACHE_PREFIX . '.all', function () {
            return Squad::query()
                ->with('subCompany:id,name')
                ->withCount('assignments')
                ->orderBy('name')
                ->get()
                ->map(function (Squad $squad): array {
                    $active = ActiveStatus::safeFrom($squad->active) ?? ActiveStatus::INACTIVE;

                    return [
                        'id' => $squad->id,
                        'name' => $squad->name,
                        'sub_company_id' => $squad->sub_company_id,
                        'sub_company_name' => $squad->subCompany?->name,
                        'active' => $active->value,
                        'active_label' => $active->label(),
                        'assignments_count' => (int) $squad->assignments_count,
                    ];
                })
                ->toArray();
        });
    }

    /**
     * Get active squads.
     */
    public function active(): array
    {
        return Cache::rememberForever(self::CACHE_PREFIX . '.active', function () {
            return Squad::query()
                ->with('subCompany:id,name')
                ->withCount('assignments')
                ->active()
                ->orderBy('name')
                ->get()
                ->map(function (Squad $squad): array {
                    $active = ActiveStatus::safeFrom($squad->active) ?? ActiveStatus::INACTIVE;

                    return [
                        'id' => $squad->id,
                        'name' => $squad->name,
                        'sub_company_id' => $squad->sub_company_id,
                        'sub_company_name' => $squad->subCompany?->name,
                        'active' => $active->value,
                        'active_label' => $active->label(),
                        'assignments_count' => (int) $squad->assignments_count,
                    ];
                })
                ->toArray();
        });
    }

    /**
     * Get squad stats for stat cards.
     */
    public function stats(): SquadStatsDto
    {
        $metrics = Cache::rememberForever(self::CACHE_PREFIX . '.stats.metrics', function () {
            $lastUpdate = Squad::query()->max('updated_at');

            return [
                'total' => Squad::query()->count(),
                'with_assignments' => Squad::query()->has('assignments')->count(),
                'without_assignments' => Squad::query()->doesntHave('assignments')->count(),
                'covered_sub_companies' => Squad::query()->distinct('sub_company_id')->count('sub_company_id'),
                'last_update' => formatDateTime($lastUpdate),
            ];
        });

        return SquadStatsDto::fromMetrics($metrics);
    }

    /**
     * Get full details for one squad.
     */
    public function show(int $id): SquadDto
    {
        $squad = Squad::query()
            ->with('subCompany:id,name')
            ->withCount('assignments')
            ->find($id);

        if (!$squad) {
            throw new BusinessException(
                __(':field not found.', ['field' => __('squad')])
            );
        }

        return SquadDto::fromModel($squad);
    }

    /**
     * Get datatable payload for squads.
     */
    public function datatable(): JsonResponse
    {
        $query = Squad::query()
            ->with('subCompany:id,name')
            ->withCount('assignments');

        $query = $this->applySearchFilters($query);
        $query->orderByDesc('created_at');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('name', fn (Squad $squad): string => $squad->name)
            ->addColumn('sub_company', fn (Squad $squad): string => $squad->subCompany?->name ?? '-')
            ->addColumn('assignments_count', fn (Squad $squad): string => (string) $squad->assignments_count)
            ->addColumn('created_at', fn (Squad $squad): string => $squad->created_at->format('d M Y, h:i A'))
            ->addColumn('actions', fn (Squad $squad): string => $this->renderActionButtons($squad))
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Create a new squad.
     */
    public function create(array $data): SquadDto
    {
        if ($this->isNameExistsInSubCompany($data['name'], (int) $data['sub_company_id'])) {
            throw new BusinessException(__('Squad already exists with this name under the selected sub-company.'));
        }

        $squad = Squad::query()->create([
            'sub_company_id' => (int) $data['sub_company_id'],
            'name' => $data['name'],
            'active' => ActiveStatus::safeFrom($data['active'] ?? null) ?? ActiveStatus::ACTIVE,
        ]);

        $this->clearCache();

        return SquadDto::fromModel(
            $squad->fresh(['subCompany'])->loadCount('assignments')
        );
    }

    /**
     * Update an existing squad.
     */
    public function update(int $id, array $data): SquadDto
    {
        $squad = Squad::query()->findOrFail($id);

        $targetSubCompanyId = (int) ($data['sub_company_id'] ?? $squad->sub_company_id);
        $targetName = (string) ($data['name'] ?? $squad->name);

        if ($this->isNameExistsInSubCompany($targetName, $targetSubCompanyId, $id)) {
            throw new BusinessException(__('A squad with the same name already exists under the selected sub-company.'));
        }

        $squad->update([
            'sub_company_id' => $targetSubCompanyId,
            'name' => $targetName,
            'active' => ActiveStatus::safeFrom($data['active'] ?? null) ?? $squad->active,
        ]);

        $this->clearCache();

        return SquadDto::fromModel(
            $squad->fresh(['subCompany'])->loadCount('assignments')
        );
    }

    /**
     * Delete a squad if safe.
     */
    public function destroy(int $id): void
    {
        $squad = Squad::query()->findOrFail($id);

        if ($squad->assignments()->exists()) {
            throw new BusinessException(
                __('This squad cannot be deleted because it has worker assignments.')
            );
        }

        $squad->delete();

        $this->clearCache();
    }

    private function clearCache(): void
    {
        Cache::forget(self::CACHE_PREFIX . '.all');
        Cache::forget(self::CACHE_PREFIX . '.active');
        Cache::forget(self::CACHE_PREFIX . '.stats.metrics');
    }

    private function applySearchFilters(Builder $query): Builder
    {
        $searchName = request('search_name');
        if (filled($searchName)) {
            $query->where('name', 'like', "%{$searchName}%");
        }

        $searchSubCompanyId = request('search_sub_company_id');
        if (filled($searchSubCompanyId)) {
            $query->where('sub_company_id', (int) $searchSubCompanyId);
        }

        $searchActive = request('search_active');
        if (filled($searchActive)) {
            $active = ActiveStatus::safeFrom($searchActive);
            if ($active) {
                $query->where('active', $active->value);
            }
        }

        return $query;
    }

    private function renderActionButtons(Squad $squad): string
    {
        $authUser = Auth::user();
        if (!$authUser instanceof User) {
            return '';
        }

        $actions = $this->buildActions($authUser);
        if (empty($actions)) {
            return '';
        }

        return view('components.ui.table-actions', [
            'mode' => 'dropdown',
            'actions' => $actions,
            'id' => $squad->id,
            'type' => 'Squad',
            'deleteName' => $squad->name,
        ])->render();
    }

    private function buildActions(User $authUser): array
    {
        $actions = [];

        if ($authUser->can('squads.view')) {
            $actions[] = 'view';
        }

        if ($authUser->can('squads.update')) {
            $actions[] = 'edit';
        }

        if ($authUser->can('squads.destroy')) {
            $actions[] = 'delete';
        }

        return $actions;
    }

    private function isNameExistsInSubCompany(string $name, int $subCompanyId, ?int $id = null): bool
    {
        $query = Squad::query()
            ->where('name', $name)
            ->where('sub_company_id', $subCompanyId);

        if ($id !== null) {
            $query->where('id', '!=', $id);
        }

        return $query->exists();
    }
}
