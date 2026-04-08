<?php

declare(strict_types=1);

namespace App\Events\Employee;

use App\Enums\EmployeeStatus;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmployeeStatusChanged
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param array<int, array<string, mixed>> $assignmentSnapshot
     * @param array<string, mixed> $details
     */
    public function __construct(
        public readonly User $employee,
        public readonly ?User $actor,
        public readonly ?EmployeeStatus $fromStatus,
        public readonly EmployeeStatus $toStatus,
        public readonly string $action,
        public readonly ?string $note = null,
        public readonly array $assignmentSnapshot = [],
        public readonly array $details = [],
    ) {
    }
}
