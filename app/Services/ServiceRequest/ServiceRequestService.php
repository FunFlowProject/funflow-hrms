<?php

declare(strict_types=1);

namespace App\Services\ServiceRequest;

use App\DTOs\ServiceRequest\ServiceRequestDto;
use App\DTOs\ServiceRequest\ServiceRequestStatsDto;
use App\Enums\ActiveStatus;
use App\Enums\ServiceRequestStatus;
use App\Events\ServiceRequest\ServiceRequestStatusChanged;
use App\Events\ServiceRequest\ServiceRequestSubmitted;
use App\Exceptions\BusinessException;
use App\Models\ServiceCatalogItem;
use App\Models\ServiceRequest;
use App\Models\StatusHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ServiceRequestService
{
    private const CACHE_PREFIX = 'service-requests';
    private const CATALOG_CACHE_PREFIX = 'service-catalog';

    /**
     * Get options required by the service request page.
     */
    public function options(): array
    {
        $authUser = $this->resolveActor();

        return [
            'statuses' => ServiceRequestStatus::options(),
            'canManage' => $authUser ? $this->canManage($authUser) : false,
            'serviceCatalogItems' => Cache::rememberForever(self::CATALOG_CACHE_PREFIX . '.active-options', function () {
                return ServiceCatalogItem::query()
                    ->active()
                    ->orderBy('category')
                    ->orderBy('name')
                    ->get(['id', 'name', 'category', 'requires_justification'])
                    ->map(static fn (ServiceCatalogItem $serviceCatalogItem): array => [
                        'id' => $serviceCatalogItem->id,
                        'name' => $serviceCatalogItem->name,
                        'category' => $serviceCatalogItem->category,
                        'requires_justification' => (bool) $serviceCatalogItem->requires_justification,
                    ])
                    ->toArray();
            }),
        ];
    }

    /**
     * Get service request statistics for stat cards.
     */
    public function stats(): ServiceRequestStatsDto
    {
        $authUser = $this->resolveActor();

        if (!$authUser) {
            return ServiceRequestStatsDto::fromMetrics([]);
        }

        $scopeKey = $this->canManage($authUser) ? 'manager' : 'user-' . $authUser->id;

        $metrics = Cache::remember(
            self::CACHE_PREFIX . '.stats.' . $scopeKey,
            now()->addMinutes(3),
            function () use ($authUser): array {
                $query = $this->baseScopedQuery($authUser);
                $lastUpdate = (clone $query)->max('updated_at');

                return [
                    'total' => (clone $query)->count(),
                    'submitted' => (clone $query)->withStatus(ServiceRequestStatus::Submitted)->count(),
                    'in_progress' => (clone $query)->withStatus(ServiceRequestStatus::InProgress)->count(),
                    'completed' => (clone $query)->withStatus(ServiceRequestStatus::Completed)->count(),
                    'rejected' => (clone $query)->withStatus(ServiceRequestStatus::Rejected)->count(),
                    'last_update' => formatDateTime($lastUpdate),
                ];
            }
        );

        return ServiceRequestStatsDto::fromMetrics($metrics);
    }

    /**
     * Retrieve one service request with full details.
     */
    public function show(int $id): ServiceRequestDto
    {
        $authUser = $this->resolveActor();

        $serviceRequest = $this->baseScopedQuery($authUser)
            ->with([
                'serviceCatalogItem:id,name,category,requires_justification',
                'requester:id,full_name,email',
                'handler:id,full_name,email',
            ])
            ->find($id);

        if (!$serviceRequest instanceof ServiceRequest) {
            throw new BusinessException(
                __(':field not found.', ['field' => __('service request')])
            );
        }

        return ServiceRequestDto::fromModel($serviceRequest);
    }

    /**
     * DataTables endpoint for service requests.
     */
    public function datatable(): JsonResponse
    {
        $authUser = $this->resolveActor();

        $query = $this->baseScopedQuery($authUser)
            ->with([
                'serviceCatalogItem:id,name,category,requires_justification',
                'requester:id,full_name,email',
                'handler:id,full_name,email',
            ]);

        $query = $this->applySearchFilters($query, $authUser);
        $query->orderByDesc('created_at');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('service_name', fn (ServiceRequest $serviceRequest): string => $this->resolveServiceName($serviceRequest))
            ->addColumn('service_category', fn (ServiceRequest $serviceRequest): string => $this->resolveServiceCategory($serviceRequest))
            ->addColumn('requester', fn (ServiceRequest $serviceRequest): string => $serviceRequest->requester?->full_name ?? '-')
            ->addColumn('status', fn (ServiceRequest $serviceRequest): string => ServiceRequestStatus::labelFor($serviceRequest->status))
            ->addColumn('handled_by_name', fn (ServiceRequest $serviceRequest): string => $serviceRequest->handler?->full_name ?? '-')
            ->addColumn('created_at', fn (ServiceRequest $serviceRequest): string => $serviceRequest->created_at->format('d M Y, h:i A'))
            ->addColumn('actions', fn (ServiceRequest $serviceRequest): string => $this->renderActionButtons($serviceRequest, $authUser))
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Submit a new service request.
     */
    public function create(array $data): ServiceRequestDto
    {
        $actor = $this->resolveActor();
        if (!$actor) {
            throw new BusinessException(__('Unable to identify the request owner.'));
        }

        $serviceCatalogItem = $this->resolveActiveCatalogItem((int) $data['service_catalog_item_id']);
        $justification = $this->normalizeJustification($data['justification'] ?? null);

        $this->ensureJustificationIsValid($serviceCatalogItem, $justification);

        $status = ServiceRequestStatus::Submitted;
        $action = 'service_request_submitted';
        $note = 'Service request submitted.';

        $serviceRequest = DB::transaction(function () use ($actor, $serviceCatalogItem, $justification, $status, $action, $note): ServiceRequest {
            $created = ServiceRequest::query()->create([
                'service_catalog_item_id' => $serviceCatalogItem->id,
                'service_name_snapshot' => $serviceCatalogItem->name,
                'service_category_snapshot' => $serviceCatalogItem->category,
                'service_requires_justification_snapshot' => (bool) $serviceCatalogItem->requires_justification,
                'requester_id' => $actor->id,
                'status' => $status,
                'justification' => $justification,
            ]);

            $this->recordStatusHistory(
                serviceRequest: $created,
                actor: $actor,
                fromStatus: null,
                toStatus: $status,
                action: $action,
                note: $note,
            );

            return $created;
        });

        $this->clearCache();

        $loadedRequest = $serviceRequest->load([
            'serviceCatalogItem:id,name,category,requires_justification',
            'requester:id,full_name,email',
            'handler:id,full_name,email',
        ]);

        event(new ServiceRequestSubmitted(
            serviceRequest: $loadedRequest,
            actor: $actor,
            action: $action,
            note: $note,
        ));

        return ServiceRequestDto::fromModel($loadedRequest);
    }

    /**
     * Update a submitted service request.
     */
    public function update(int $id, array $data): ServiceRequestDto
    {
        $actor = $this->resolveActor();
        if (!$actor) {
            throw new BusinessException(__('Unable to identify the request owner.'));
        }

        $serviceRequest = $this->baseScopedQuery($actor)
            ->with([
                'serviceCatalogItem:id,name,category,requires_justification',
                'requester:id,full_name,email',
                'handler:id,full_name,email',
            ])
            ->find($id);

        if (!$serviceRequest instanceof ServiceRequest) {
            throw new BusinessException(__('Service request not found.'));
        }

        if (ServiceRequestStatus::safeFrom($serviceRequest->status) !== ServiceRequestStatus::Submitted) {
            throw new BusinessException(__('Only submitted service requests can be edited.'));
        }

        if (!$this->canManage($actor) && $serviceRequest->requester_id !== $actor->id) {
            throw new BusinessException(__('You are not allowed to edit this service request.'));
        }

        $serviceCatalogItem = $this->resolveActiveCatalogItem((int) $data['service_catalog_item_id']);
        $justification = $this->normalizeJustification($data['justification'] ?? null);

        $this->ensureJustificationIsValid($serviceCatalogItem, $justification);

        $serviceRequest->update([
            'service_catalog_item_id' => $serviceCatalogItem->id,
            'service_name_snapshot' => $serviceCatalogItem->name,
            'service_category_snapshot' => $serviceCatalogItem->category,
            'service_requires_justification_snapshot' => (bool) $serviceCatalogItem->requires_justification,
            'justification' => $justification,
        ]);

        $this->clearCache();

        return ServiceRequestDto::fromModel(
            $serviceRequest->fresh([
                'serviceCatalogItem:id,name,category,requires_justification',
                'requester:id,full_name,email',
                'handler:id,full_name,email',
            ])
        );
    }

    /**
     * Move a submitted request to in progress.
     */
    public function moveToInProgress(int $id, ?string $fulfillmentNote = null): ServiceRequestDto
    {
        $serviceRequest = $this->findManageableServiceRequest($id);

        if (ServiceRequestStatus::safeFrom($serviceRequest->status) !== ServiceRequestStatus::Submitted) {
            throw new BusinessException(__('Only submitted requests can be moved to in progress.'));
        }

        return $this->transitionServiceRequestStatus(
            serviceRequest: $serviceRequest,
            newStatus: ServiceRequestStatus::InProgress,
            action: 'service_request_in_progress',
            note: 'Service request moved to in progress.',
            fulfillmentNote: $fulfillmentNote,
        );
    }

    /**
     * Complete an in-progress request.
     */
    public function complete(int $id, ?string $fulfillmentNote = null): ServiceRequestDto
    {
        $serviceRequest = $this->findManageableServiceRequest($id);

        if (ServiceRequestStatus::safeFrom($serviceRequest->status) !== ServiceRequestStatus::InProgress) {
            throw new BusinessException(__('Only in-progress requests can be completed.'));
        }

        return $this->transitionServiceRequestStatus(
            serviceRequest: $serviceRequest,
            newStatus: ServiceRequestStatus::Completed,
            action: 'service_request_completed',
            note: 'Service request completed.',
            fulfillmentNote: $fulfillmentNote,
        );
    }

    /**
     * Reject an in-progress request.
     */
    public function reject(int $id, string $rejectionReason, ?string $fulfillmentNote = null): ServiceRequestDto
    {
        $serviceRequest = $this->findManageableServiceRequest($id);

        if (ServiceRequestStatus::safeFrom($serviceRequest->status) !== ServiceRequestStatus::InProgress) {
            throw new BusinessException(__('Only in-progress requests can be rejected.'));
        }

        if (blank($rejectionReason)) {
            throw new BusinessException(__('A rejection reason is required.'));
        }

        return $this->transitionServiceRequestStatus(
            serviceRequest: $serviceRequest,
            newStatus: ServiceRequestStatus::Rejected,
            action: 'service_request_rejected',
            note: 'Service request rejected.',
            fulfillmentNote: $fulfillmentNote,
            rejectionReason: trim($rejectionReason),
        );
    }

    private function transitionServiceRequestStatus(
        ServiceRequest $serviceRequest,
        ServiceRequestStatus $newStatus,
        string $action,
        ?string $note = null,
        ?string $fulfillmentNote = null,
        ?string $rejectionReason = null,
        array $details = [],
    ): ServiceRequestDto {
        $fromStatus = ServiceRequestStatus::safeFrom($serviceRequest->status) ?? ServiceRequestStatus::Submitted;

        if ($fromStatus === $newStatus) {
            return ServiceRequestDto::fromModel(
                $serviceRequest->fresh([
                    'serviceCatalogItem:id,name,category,requires_justification',
                    'requester:id,full_name,email',
                    'handler:id,full_name,email',
                ]) ?? $serviceRequest
            );
        }

        $actor = $this->resolveActor();
        if (!$actor) {
            throw new BusinessException(__('Unable to identify request handler.'));
        }

        $transitionDetails = array_filter(array_merge($details, [
            'fulfillment_note' => $fulfillmentNote,
            'rejection_reason' => $rejectionReason,
        ]), static fn ($value): bool => filled($value));

        DB::transaction(function () use ($serviceRequest, $newStatus, $actor, $fulfillmentNote, $rejectionReason, $fromStatus, $action, $note, $transitionDetails): void {
            $updatePayload = [
                'status' => $newStatus,
                'handled_by' => $actor->id,
                'acted_at' => now(),
            ];

            if ($fulfillmentNote !== null) {
                $updatePayload['fulfillment_note'] = trim($fulfillmentNote) !== '' ? trim($fulfillmentNote) : null;
            }

            if ($newStatus !== ServiceRequestStatus::Rejected) {
                $updatePayload['rejection_reason'] = null;
            }

            if ($rejectionReason !== null) {
                $updatePayload['rejection_reason'] = trim($rejectionReason);
            }

            $serviceRequest->update($updatePayload);

            $this->recordStatusHistory(
                serviceRequest: $serviceRequest,
                actor: $actor,
                fromStatus: $fromStatus,
                toStatus: $newStatus,
                action: $action,
                note: $note,
                details: $transitionDetails,
            );
        });

        $this->clearCache();

        $loadedRequest = $serviceRequest->fresh([
            'serviceCatalogItem:id,name,category,requires_justification',
            'requester:id,full_name,email',
            'handler:id,full_name,email',
        ]) ?? $serviceRequest;

        event(new ServiceRequestStatusChanged(
            serviceRequest: $loadedRequest,
            actor: $actor,
            fromStatus: $fromStatus,
            toStatus: $newStatus,
            action: $action,
            note: $note,
            details: $transitionDetails,
        ));

        return ServiceRequestDto::fromModel($loadedRequest);
    }

    private function findManageableServiceRequest(int $id): ServiceRequest
    {
        $actor = $this->resolveActor();

        if (!$actor) {
            throw new BusinessException(__('Unable to identify request handler.'));
        }

        if (!$this->canManage($actor) || !$actor->can('service-requests.transition')) {
            throw new BusinessException(__('You are not allowed to transition service requests.'));
        }

        $serviceRequest = ServiceRequest::query()
            ->with([
                'serviceCatalogItem:id,name,category,requires_justification',
                'requester:id,full_name,email',
                'handler:id,full_name,email',
            ])
            ->find($id);

        if (!$serviceRequest instanceof ServiceRequest) {
            throw new BusinessException(__('Service request not found.'));
        }

        return $serviceRequest;
    }

    private function resolveActiveCatalogItem(int $id): ServiceCatalogItem
    {
        $serviceCatalogItem = ServiceCatalogItem::query()->find($id);

        if (!$serviceCatalogItem) {
            throw new BusinessException(__('Selected service was not found.'));
        }

        $active = ActiveStatus::safeFrom($serviceCatalogItem->active) ?? ActiveStatus::INACTIVE;

        if ($active !== ActiveStatus::ACTIVE) {
            throw new BusinessException(__('Selected service is currently unavailable.'));
        }

        return $serviceCatalogItem;
    }

    private function normalizeJustification(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : null;
    }

    private function ensureJustificationIsValid(ServiceCatalogItem $serviceCatalogItem, ?string $justification): void
    {
        if ($serviceCatalogItem->requires_justification && blank($justification)) {
            throw new BusinessException(__('A justification is required for the selected service.'));
        }
    }

    /**
     * @return Builder<ServiceRequest>
     */
    private function baseScopedQuery(?User $authUser): Builder
    {
        $query = ServiceRequest::query();

        if (!$authUser) {
            return $query->whereKey(-1);
        }

        if ($this->canManage($authUser)) {
            return $query;
        }

        return $query->where('requester_id', $authUser->id);
    }

    private function applySearchFilters(Builder $query, ?User $authUser): Builder
    {
        $searchService = request('search_service');
        if (filled($searchService)) {
            $query->where(function (Builder $nested) use ($searchService): void {
                $nested->where('service_name_snapshot', 'like', "%{$searchService}%")
                    ->orWhereHas('serviceCatalogItem', function (Builder $catalogQuery) use ($searchService): void {
                        $catalogQuery->where('name', 'like', "%{$searchService}%");
                    });
            });
        }

        $searchCategory = request('search_category');
        if (filled($searchCategory)) {
            $query->where(function (Builder $nested) use ($searchCategory): void {
                $nested->where('service_category_snapshot', 'like', "%{$searchCategory}%")
                    ->orWhereHas('serviceCatalogItem', function (Builder $catalogQuery) use ($searchCategory): void {
                        $catalogQuery->where('category', 'like', "%{$searchCategory}%");
                    });
            });
        }

        $searchStatus = request('search_status');
        if (filled($searchStatus)) {
            $status = ServiceRequestStatus::safeFrom($searchStatus);
            if ($status) {
                $query->where('status', $status->value);
            }
        }

        $searchRequester = request('search_requester');
        if (filled($searchRequester) && $authUser && $this->canManage($authUser)) {
            $query->whereHas('requester', function (Builder $requesterQuery) use ($searchRequester): void {
                $requesterQuery->where('full_name', 'like', "%{$searchRequester}%")
                    ->orWhere('email', 'like', "%{$searchRequester}%");
            });
        }

        return $query;
    }

    private function renderActionButtons(ServiceRequest $serviceRequest, ?User $authUser): string
    {
        if (!$authUser) {
            return '';
        }

        $actions = [];
        if ($authUser->can('service-requests.view') || $this->canManage($authUser)) {
            $actions[] = 'view';
        }

        if ($this->canEdit($serviceRequest, $authUser)) {
            $actions[] = 'edit';
        }

        $singleActions = $this->buildSingleActions($serviceRequest, $authUser);

        if (empty($actions) && empty($singleActions)) {
            return '';
        }

        $mode = match (true) {
            !empty($actions) && !empty($singleActions) => 'both',
            !empty($actions) => 'dropdown',
            default => 'single',
        };

        return view('components.ui.table-actions', [
            'mode' => $mode,
            'actions' => $actions,
            'singleActions' => $singleActions,
            'id' => $serviceRequest->id,
            'type' => 'ServiceRequest',
        ])->render();
    }

    private function canEdit(ServiceRequest $serviceRequest, User $authUser): bool
    {
        $status = ServiceRequestStatus::safeFrom($serviceRequest->status) ?? ServiceRequestStatus::Submitted;

        if ($status !== ServiceRequestStatus::Submitted) {
            return false;
        }

        if ($this->canManage($authUser)) {
            return true;
        }

        return $authUser->can('service-requests.create')
            && $serviceRequest->requester_id === $authUser->id;
    }

    private function buildSingleActions(ServiceRequest $serviceRequest, User $authUser): array
    {
        if (!$this->canManage($authUser) || !$authUser->can('service-requests.transition')) {
            return [];
        }

        $status = ServiceRequestStatus::safeFrom($serviceRequest->status) ?? ServiceRequestStatus::Submitted;
        $serviceName = $this->resolveServiceName($serviceRequest);

        return match ($status) {
            ServiceRequestStatus::Submitted => [[
                'action' => 'start',
                'label' => 'Start Progress',
                'icon' => 'bx bx-loader-circle',
                'tone' => 'warning',
                'show_label' => true,
                'class' => 'btn-start-service-request',
                'data' => [
                    'name' => $serviceName,
                ],
            ]],
            ServiceRequestStatus::InProgress => [
                [
                    'action' => 'complete',
                    'label' => 'Complete',
                    'icon' => 'bx bx-check-circle',
                    'tone' => 'success',
                    'show_label' => true,
                    'class' => 'btn-complete-service-request',
                    'data' => [
                        'name' => $serviceName,
                    ],
                ],
                [
                    'action' => 'reject',
                    'label' => 'Reject',
                    'icon' => 'bx bx-x-circle',
                    'tone' => 'danger',
                    'show_label' => true,
                    'class' => 'btn-reject-service-request',
                    'data' => [
                        'name' => $serviceName,
                    ],
                ],
            ],
            default => [],
        };
    }

    private function recordStatusHistory(
        ServiceRequest $serviceRequest,
        ?User $actor,
        ?ServiceRequestStatus $fromStatus,
        ServiceRequestStatus $toStatus,
        string $action,
        ?string $note = null,
        array $details = [],
    ): void {
        $request = request();

        $contextDetails = array_filter([
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ], static fn ($value): bool => filled($value));

        $payloadDetails = array_merge($contextDetails, $details);

        StatusHistory::query()->create([
            'statusable_type' => ServiceRequest::class,
            'statusable_id' => $serviceRequest->id,
            'actor_id' => $actor?->id,
            'from_status' => $fromStatus?->value,
            'to_status' => $toStatus->value,
            'action' => $action,
            'note' => $note,
            'details' => !empty($payloadDetails) ? $payloadDetails : null,
            'recorded_at' => now(),
        ]);
    }

    private function resolveServiceName(ServiceRequest $serviceRequest): string
    {
        if ($serviceRequest->relationLoaded('serviceCatalogItem') && $serviceRequest->serviceCatalogItem) {
            return $serviceRequest->serviceCatalogItem->name;
        }

        return $serviceRequest->service_name_snapshot;
    }

    private function resolveServiceCategory(ServiceRequest $serviceRequest): string
    {
        if ($serviceRequest->relationLoaded('serviceCatalogItem') && $serviceRequest->serviceCatalogItem) {
            return $serviceRequest->serviceCatalogItem->category;
        }

        return $serviceRequest->service_category_snapshot;
    }

    private function resolveActor(): ?User
    {
        $actor = Auth::user();

        return $actor instanceof User ? $actor : null;
    }

    private function canManage(User $user): bool
    {
        return $user->can('service-requests.manage');
    }

    private function clearCache(): void
    {
        // Stats keys expire automatically after 3 minutes.
    }
}
