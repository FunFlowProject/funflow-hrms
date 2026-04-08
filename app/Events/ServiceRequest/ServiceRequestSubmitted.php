<?php

declare(strict_types=1);

namespace App\Events\ServiceRequest;

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServiceRequestSubmitted
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param array<string, mixed> $details
     */
    public function __construct(
        public readonly ServiceRequest $serviceRequest,
        public readonly ?User $actor,
        public readonly string $action = 'service_request_submitted',
        public readonly ?string $note = null,
        public readonly array $details = [],
    ) {
    }
}
