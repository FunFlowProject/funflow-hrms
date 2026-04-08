<?php

declare(strict_types=1);

namespace App\Events\Employee;

use App\Enums\EmployeeStatus;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmployeeCreated
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
        public readonly array $assignmentSnapshot,
        public readonly EmployeeStatus $status,
        public readonly string $action = 'employee_created',
        public readonly ?string $note = null,
        public readonly array $details = [],
    ) {
    }
}
