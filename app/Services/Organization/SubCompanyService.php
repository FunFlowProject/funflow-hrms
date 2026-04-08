<?php

declare(strict_types=1);

namespace App\Services\Organization;

use App\DTOs\Organization\SubCompanyDto;
use App\DTOs\Organization\SubCompanyStatsDto;
use App\Enums\ActiveStatus;
use App\Exceptions\BusinessException;
use App\Models\SubCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Yajra\DataTables\Facades\DataTables;

class SubCompanyService
{
    private const CACHE_PREFIX = 'sub-companies';

    /**
     * Get all sub-companies in a lightweight list.
     */
    public function all(): array
    {
        return Cache::rememberForever(self::CACHE_PREFIX . '.all', function () {
            return SubCompany::query()
                ->withCount(['squads', 'assignments'])
                ->orderBy('name')
                ->get()
                ->map(function (SubCompany $subCompany): array {
                    $active = ActiveStatus::safeFrom($subCompany->active) ?? ActiveStatus::INACTIVE;

                    return [
                        'id' => $subCompany->id,
                        'name' => $subCompany->name,
                        'description' => $subCompany->description,
                        'active' => $active->value,
                        'active_label' => $active->label(),
                        'squads_count' => (int) $subCompany->squads_count,
                        'assignments_count' => (int) $subCompany->assignments_count,
                    ];
                })
                ->toArray();
        });
    }

    /**
     * Get active sub-companies.
     */
    public function active(): array
    {
        return Cache::rememberForever(self::CACHE_PREFIX . '.active', function () {
            return SubCompany::query()
                ->withCount(['squads', 'assignments'])
                ->active()
                ->orderBy('name')
                ->get()
                ->map(function (SubCompany $subCompany): array {
                    $active = ActiveStatus::safeFrom($subCompany->active) ?? ActiveStatus::INACTIVE;

                    return [
                        'id' => $subCompany->id,
                        'name' => $subCompany->name,
                        'description' => $subCompany->description,
                        'active' => $active->value,
                        'active_label' => $active->label(),
                        'squads_count' => (int) $subCompany->squads_count,
                        'assignments_count' => (int) $subCompany->assignments_count,
                    ];
                })
                ->toArray();
        });
    }

    /**
     * Get sub-company stats for stat cards.
     */
    public function stats(): SubCompanyStatsDto
    {
        $metrics = Cache::rememberForever(self::CACHE_PREFIX . '.stats.metrics', function () {
            $lastUpdate = SubCompany::query()->max('updated_at');

            return [
                'total' => SubCompany::query()->count(),
                'with_squads' => SubCompany::query()->has('squads')->count(),
                'without_squads' => SubCompany::query()->doesntHave('squads')->count(),
                'with_assignments' => SubCompany::query()->has('assignments')->count(),
                'last_update' => formatDateTime($lastUpdate),
            ];
        });

        return SubCompanyStatsDto::fromMetrics($metrics);
    }

    /**
     * Get full details for one sub-company.
     */
    public function show(int $id): SubCompanyDto
    {
        $subCompany = SubCompany::query()
            ->with(['squads:id,sub_company_id,name,active'])
            ->withCount(['squads', 'assignments'])
            ->find($id);

        if (!$subCompany) {
            throw new BusinessException(
                __(':field not found.', ['field' => __('sub-company')])
            );
        }

        return SubCompanyDto::fromModel($subCompany);
    }

    /**
     * Get datatable payload for sub-companies.
     */
    public function datatable(): JsonResponse
    {
        $query = SubCompany::query()->withCount(['squads', 'assignments']);
        $query = $this->applySearchFilters($query);
        $query->orderByDesc('created_at');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('name', fn (SubCompany $subCompany): string => $subCompany->name)
            ->addColumn('description', fn (SubCompany $subCompany): string => $subCompany->description ?? '-')
            ->addColumn('squads_count', fn (SubCompany $subCompany): string => (string) $subCompany->squads_count)
            ->addColumn('assignments_count', fn (SubCompany $subCompany): string => (string) $subCompany->assignments_count)
            ->addColumn('created_at', fn (SubCompany $subCompany): string => $subCompany->created_at->format('d M Y, h:i A'))
            ->addColumn('actions', fn (SubCompany $subCompany): string => $this->renderActionButtons($subCompany))
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Create a new sub-company.
     */
    public function create(array $data): SubCompanyDto
    {
        if ($this->isNameExists($data['name'])) {
            throw new BusinessException(__('Sub-company already exists with this name.'));
        }

        $subCompany = SubCompany::query()->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'active' => ActiveStatus::safeFrom($data['active'] ?? null) ?? ActiveStatus::ACTIVE,
        ]);

        $this->clearCache();

        return SubCompanyDto::fromModel(
            $subCompany->fresh(['squads'])->loadCount(['squads', 'assignments'])
        );
    }

    /**
     * Update an existing sub-company.
     */
    public function update(int $id, array $data): SubCompanyDto
    {
        $subCompany = SubCompany::query()->findOrFail($id);

        if ($this->isNameExists($data['name'] ?? $subCompany->name, $id)) {
            throw new BusinessException(__('A sub-company with the same name already exists.'));
        }

        $subCompany->update([
            'name' => $data['name'] ?? $subCompany->name,
            'description' => $data['description'] ?? $subCompany->description,
            'active' => ActiveStatus::safeFrom($data['active'] ?? null) ?? $subCompany->active,
        ]);

        $this->clearCache();

        return SubCompanyDto::fromModel(
            $subCompany->fresh(['squads'])->loadCount(['squads', 'assignments'])
        );
    }

    /**
     * Delete a sub-company if safe.
     */
    public function destroy(int $id): void
    {
        $subCompany = SubCompany::query()->findOrFail($id);

        if ($subCompany->squads()->exists()) {
            throw new BusinessException(
                __('This sub-company cannot be deleted because it still has squads.')
            );
        }

        if ($subCompany->assignments()->exists()) {
            throw new BusinessException(
                __('This sub-company cannot be deleted because it has worker assignments.')
            );
        }

        $subCompany->delete();

        $this->clearCache();
    }

    /**
     * Get one sub-company with ordered squads.
     */
    public function getWithSquads(SubCompany $subCompany): SubCompany
    {
        return $subCompany->load([
            'squads' => fn ($query) => $query->orderBy('name'),
        ]);
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

        $searchDescription = request('search_description');
        if (filled($searchDescription)) {
            $query->where('description', 'like', "%{$searchDescription}%");
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

    private function renderActionButtons(SubCompany $subCompany): string
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
            'id' => $subCompany->id,
            'type' => 'SubCompany',
            'deleteName' => $subCompany->name,
        ])->render();
    }

    private function buildActions(User $authUser): array
    {
        $actions = [];

        if ($authUser->can('sub-companies.view')) {
            $actions[] = 'view';
        }

        if ($authUser->can('sub-companies.update')) {
            $actions[] = 'edit';
        }

        if ($authUser->can('sub-companies.destroy')) {
            $actions[] = 'delete';
        }

        return $actions;
    }

    private function isNameExists(string $name, ?int $id = null): bool
    {
        $query = SubCompany::query()->where('name', $name);

        if ($id !== null) {
            $query->where('id', '!=', $id);
        }

        return $query->exists();
    }
}
