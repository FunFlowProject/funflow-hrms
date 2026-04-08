<?php

declare(strict_types=1);

namespace App\Services\ServiceRequest;

use App\DTOs\ServiceRequest\ServiceCatalogDto;
use App\DTOs\ServiceRequest\ServiceCatalogStatsDto;
use App\Enums\ActiveStatus;
use App\Exceptions\BusinessException;
use App\Models\ServiceCatalogItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Yajra\DataTables\Facades\DataTables;

class ServiceCatalogService
{
    private const CACHE_PREFIX = 'service-catalog';

    /**
     * Get all services in a lightweight list.
     */
    public function all(): array
    {
        return Cache::rememberForever(self::CACHE_PREFIX . '.all', function () {
            return ServiceCatalogItem::query()
                ->orderBy('category')
                ->orderBy('name')
                ->get()
                ->map(function (ServiceCatalogItem $serviceCatalogItem): array {
                    $active = ActiveStatus::safeFrom($serviceCatalogItem->active) ?? ActiveStatus::INACTIVE;

                    return [
                        'id' => $serviceCatalogItem->id,
                        'name' => $serviceCatalogItem->name,
                        'category' => $serviceCatalogItem->category,
                        'description' => $serviceCatalogItem->description,
                        'requires_justification' => (bool) $serviceCatalogItem->requires_justification,
                        'requires_justification_label' => (bool) $serviceCatalogItem->requires_justification ? __('Yes') : __('No'),
                        'active' => $active->value,
                        'active_label' => $active->label(),
                    ];
                })
                ->toArray();
        });
    }

    /**
     * Get active services in a lightweight list.
     */
    public function active(): array
    {
        return Cache::rememberForever(self::CACHE_PREFIX . '.active', function () {
            return ServiceCatalogItem::query()
                ->active()
                ->orderBy('category')
                ->orderBy('name')
                ->get()
                ->map(function (ServiceCatalogItem $serviceCatalogItem): array {
                    return [
                        'id' => $serviceCatalogItem->id,
                        'name' => $serviceCatalogItem->name,
                        'category' => $serviceCatalogItem->category,
                        'description' => $serviceCatalogItem->description,
                        'requires_justification' => (bool) $serviceCatalogItem->requires_justification,
                        'requires_justification_label' => (bool) $serviceCatalogItem->requires_justification ? __('Yes') : __('No'),
                    ];
                })
                ->toArray();
        });
    }

    /**
     * Get service catalog stats for stat cards.
     */
    public function stats(): ServiceCatalogStatsDto
    {
        $metrics = Cache::rememberForever(self::CACHE_PREFIX . '.stats.metrics', function () {
            $lastUpdate = ServiceCatalogItem::query()->max('updated_at');

            return [
                'total' => ServiceCatalogItem::query()->count(),
                'active' => ServiceCatalogItem::query()->active()->count(),
                'requires_justification' => ServiceCatalogItem::query()->where('requires_justification', true)->count(),
                'categories' => ServiceCatalogItem::query()->distinct('category')->count('category'),
                'last_update' => formatDateTime($lastUpdate),
            ];
        });

        return ServiceCatalogStatsDto::fromMetrics($metrics);
    }

    /**
     * Get full details for one catalog item.
     */
    public function show(int $id): ServiceCatalogDto
    {
        $serviceCatalogItem = ServiceCatalogItem::query()
            ->with(['creator:id,full_name', 'updater:id,full_name'])
            ->find($id);

        if (!$serviceCatalogItem) {
            throw new BusinessException(
                __(':field not found.', ['field' => __('service catalog item')])
            );
        }

        return ServiceCatalogDto::fromModel($serviceCatalogItem);
    }

    /**
     * Get datatable payload for service catalog.
     */
    public function datatable(): JsonResponse
    {
        $query = ServiceCatalogItem::query()
            ->withCount('requests')
            ->with(['creator:id,full_name']);

        $query = $this->applySearchFilters($query);
        $query->orderByDesc('created_at');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('name', fn (ServiceCatalogItem $serviceCatalogItem): string => $serviceCatalogItem->name)
            ->addColumn('category', fn (ServiceCatalogItem $serviceCatalogItem): string => $serviceCatalogItem->category)
            ->addColumn('requires_justification', fn (ServiceCatalogItem $serviceCatalogItem): string => (bool) $serviceCatalogItem->requires_justification ? __('Yes') : __('No'))
            ->addColumn('active', fn (ServiceCatalogItem $serviceCatalogItem): string => ActiveStatus::labelFor($serviceCatalogItem->active))
            ->addColumn('requests_count', fn (ServiceCatalogItem $serviceCatalogItem): string => (string) $serviceCatalogItem->requests_count)
            ->addColumn('created_at', fn (ServiceCatalogItem $serviceCatalogItem): string => $serviceCatalogItem->created_at->format('d M Y, h:i A'))
            ->addColumn('actions', fn (ServiceCatalogItem $serviceCatalogItem): string => $this->renderActionButtons($serviceCatalogItem))
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Create a new service catalog item.
     */
    public function create(array $data): ServiceCatalogDto
    {
        $name = trim((string) $data['name']);
        $category = trim((string) $data['category']);

        if ($this->isNameCategoryExists($name, $category)) {
            throw new BusinessException(__('A service with the same name already exists in this category.'));
        }

        $serviceCatalogItem = ServiceCatalogItem::query()->create([
            'name' => $name,
            'category' => $category,
            'description' => $data['description'] ?? null,
            'requires_justification' => (bool) ($data['requires_justification'] ?? false),
            'active' => ActiveStatus::safeFrom($data['active'] ?? null) ?? ActiveStatus::ACTIVE,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        $this->clearCache();

        return ServiceCatalogDto::fromModel(
            $serviceCatalogItem->fresh(['creator:id,full_name', 'updater:id,full_name'])
        );
    }

    /**
     * Update an existing service catalog item.
     */
    public function update(int $id, array $data): ServiceCatalogDto
    {
        $serviceCatalogItem = ServiceCatalogItem::query()->findOrFail($id);

        $name = trim((string) ($data['name'] ?? $serviceCatalogItem->name));
        $category = trim((string) ($data['category'] ?? $serviceCatalogItem->category));

        if ($this->isNameCategoryExists($name, $category, $id)) {
            throw new BusinessException(__('A service with the same name already exists in this category.'));
        }

        $serviceCatalogItem->update([
            'name' => $name,
            'category' => $category,
            'description' => $data['description'] ?? $serviceCatalogItem->description,
            'requires_justification' => array_key_exists('requires_justification', $data)
                ? (bool) $data['requires_justification']
                : $serviceCatalogItem->requires_justification,
            'active' => ActiveStatus::safeFrom($data['active'] ?? null) ?? $serviceCatalogItem->active,
            'updated_by' => Auth::id(),
        ]);

        $this->clearCache();

        return ServiceCatalogDto::fromModel(
            $serviceCatalogItem->fresh(['creator:id,full_name', 'updater:id,full_name'])
        );
    }

    /**
     * Hard-delete a service catalog item.
     */
    public function destroy(int $id): void
    {
        $serviceCatalogItem = ServiceCatalogItem::query()->findOrFail($id);

        $serviceCatalogItem->delete();

        $this->clearCache();
    }

    private function clearCache(): void
    {
        Cache::forget(self::CACHE_PREFIX . '.all');
        Cache::forget(self::CACHE_PREFIX . '.active');
        Cache::forget(self::CACHE_PREFIX . '.stats.metrics');
        // also active-options for the service request service
        Cache::forget(self::CACHE_PREFIX . '.active-options');
    }

    private function applySearchFilters(Builder $query): Builder
    {
        $searchName = request('search_name');
        if (filled($searchName)) {
            $query->where('name', 'like', "%{$searchName}%");
        }

        $searchCategory = request('search_category');
        if (filled($searchCategory)) {
            $query->where('category', 'like', "%{$searchCategory}%");
        }

        $searchActive = request('search_active');
        if (filled($searchActive)) {
            $active = ActiveStatus::safeFrom($searchActive);
            if ($active) {
                $query->where('active', $active->value);
            }
        }

        $searchRequiresJustification = request('search_requires_justification');
        if ($searchRequiresJustification !== null && $searchRequiresJustification !== '') {
            $query->where('requires_justification', (int) $searchRequiresJustification === 1);
        }

        return $query;
    }

    private function renderActionButtons(ServiceCatalogItem $serviceCatalogItem): string
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
            'id' => $serviceCatalogItem->id,
            'type' => 'ServiceCatalog',
            'deleteName' => $serviceCatalogItem->name,
        ])->render();
    }

    private function buildActions(User $authUser): array
    {
        $actions = [];

        if ($authUser->can('service-catalog.view')) {
            $actions[] = 'view';
        }

        if ($authUser->can('service-catalog.update')) {
            $actions[] = 'edit';
        }

        if ($authUser->can('service-catalog.destroy')) {
            $actions[] = 'delete';
        }

        return $actions;
    }

    private function isNameCategoryExists(string $name, string $category, ?int $id = null): bool
    {
        $query = ServiceCatalogItem::query()
            ->where('name', $name)
            ->where('category', $category);

        if ($id !== null) {
            $query->where('id', '!=', $id);
        }

        return $query->exists();
    }
}
