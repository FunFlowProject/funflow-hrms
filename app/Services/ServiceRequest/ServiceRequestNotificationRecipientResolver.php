<?php

declare(strict_types=1);

namespace App\Services\ServiceRequest;

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Support\Collection;

class ServiceRequestNotificationRecipientResolver
{
    /**
     * Resolve recipients for a newly submitted service request.
     *
     * @return Collection<int, User>
     */
    public function resolveForSubmission(): Collection
    {
        return User::query()
            ->permission('service-requests.manage')
            ->select(['id', 'full_name', 'email'])
            ->get()
            ->unique('id')
            ->values();
    }

    /**
     * Resolve recipients for service request status updates.
     *
     * @return Collection<int, User>
     */
    public function resolveForStatusChange(ServiceRequest $serviceRequest): Collection
    {
        $serviceRequest->loadMissing('requester:id,full_name,email');

        if (!$serviceRequest->requester) {
            return collect();
        }

        return collect([$serviceRequest->requester])
            ->unique('id')
            ->values();
    }
}
