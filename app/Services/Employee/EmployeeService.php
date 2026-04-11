<?php

declare(strict_types=1);

namespace App\Services\Employee;

use App\DTOs\Employee\EmployeeDto;
use App\DTOs\Employee\EmployeeStatsDto;
use App\Enums\ContractType;
use App\Enums\EmployeeStatus;
use App\Enums\SystemRole;
use App\Events\Employee\EmployeeCreated;
use App\Events\Employee\EmployeeStatusChanged;
use App\Exceptions\BusinessException;
use App\Models\EmployeeAssignment;
use App\Models\Hierarchy;
use App\Models\StatusHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class EmployeeService
{
    private const CACHE_PREFIX = 'employees';

    /*
    |--------------------------------------------------------------------------
    | RETRIEVAL METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Get all employees.
     */
    public function all(): array
    {
        return Cache::rememberForever(self::CACHE_PREFIX . '.all', function () {
            return User::employees()
                ->orderBy('full_name')
                ->get()
                ->map(fn(User $user): array => [
                    'id' => $user->id,
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'phone_number' => $user->phone_number,
                    'contract_type' => $user->contract_type->value,
                    'system_role' => $user->system_role->value,
                    'status' => $user->status->value,
                ])
                ->toArray();
        });
    }

    /**
     * Get only active employees.
     */
    public function active(): array
    {
        return Cache::rememberForever(self::CACHE_PREFIX . '.active', function () {
            return User::employees()
                ->orderBy('full_name')
                ->where('status', EmployeeStatus::Joined->value)
                ->get()
                ->map(fn(User $user): array => [
                    'id' => $user->id,
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'phone_number' => $user->phone_number,
                    'contract_type' => $user->contract_type->value,
                    'system_role' => $user->system_role->value,
                    'status' => $user->status->value,
                ])
                ->toArray();
        });
    }

    /**
     * Get employee statistics.
     */
    public function stats(): EmployeeStatsDto
    {
        $metrics = Cache::rememberForever(self::CACHE_PREFIX . '.stats.metrics', function () {
            $pendingValue = EmployeeStatus::Pending->value;
            $onboardingValue = EmployeeStatus::Onboarding->value;
            $joinedValue = EmployeeStatus::Joined->value;
            $terminatedValue = EmployeeStatus::Terminated->value;

            $stats = User::employees()->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as onboarding_count,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as joined_count,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as terminated_count,
                MAX(updated_at) as last_update,
                MAX(CASE WHEN status = ? THEN updated_at END) as pending_last_update,
                MAX(CASE WHEN status = ? THEN updated_at END) as onboarding_last_update,
                MAX(CASE WHEN status = ? THEN updated_at END) as joined_last_update,
                MAX(CASE WHEN status = ? THEN updated_at END) as terminated_last_update
            ", [
                $pendingValue,
                $onboardingValue,
                $joinedValue,
                $terminatedValue,
                $pendingValue,
                $onboardingValue,
                $joinedValue,
                $terminatedValue,
            ])
                ->first();

            return [
                'total' => (int) ($stats->total ?? 0),
                'pending_count' => (int) ($stats->pending_count ?? 0),
                'onboarding_count' => (int) ($stats->onboarding_count ?? 0),
                'joined_count' => (int) ($stats->joined_count ?? 0),
                'terminated_count' => (int) ($stats->terminated_count ?? 0),
                'last_update' => $stats->last_update ?? null,
                'pending_last_update' => $stats->pending_last_update ?? null,
                'onboarding_last_update' => $stats->onboarding_last_update ?? null,
                'joined_last_update' => $stats->joined_last_update ?? null,
                'terminated_last_update' => $stats->terminated_last_update ?? null,
            ];
        });

        return EmployeeStatsDto::fromQueryRow((object) $metrics);
    }

    /**
     * Get employee form options for frontend selects.
     */
    public function options(): array
    {
        return Cache::rememberForever(self::CACHE_PREFIX . '.options', function () {
            return [
                'contractTypes' => array_map(
                    static fn(ContractType $type): string => $type->value,
                    ContractType::cases()
                ),
                'systemRoles' => array_map(
                    static fn(SystemRole $role): string => $role->value,
                    SystemRole::cases()
                ),
                'hierarchies' => Hierarchy::query()
                    ->select(['id', 'level', 'title', 'type'])
                    ->orderBy('type')
                    ->orderBy('level')
                    ->get()
                    ->toArray(),
            ];
        });
    }

    /**
     * Retrieve a single employee with full details.
     */
    public function show(int $id): EmployeeDto
    {
        $user = User::employees()
            ->with(['assignments.subCompany:id,name', 'assignments.squad:id,name', 'assignments.hierarchy:id,title,level,type'])
            ->find($id);

        if (!$user) {
            throw new BusinessException(
                __(':field not found.', ['field' => __('employee')])
            );
        }

        return EmployeeDto::fromModel($user);
    }

    /**
     * Get employee data for DataTables.
     */
    public function datatable(): JsonResponse
    {
        $query = User::employees()->with(['assignments.subCompany:id,name', 'assignments.squad:id,name', 'assignments.hierarchy:id,title,level,type']);
        $query = $this->applySearchFilters($query);
        $query->orderByDesc('created_at');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('username', fn(User $user): string => $user->username ?? '-')
            ->addColumn('email', fn(User $user): string => $user->email)
            ->addColumn('phone', fn(User $user): string => $user->phone_number ?? '-')
            ->addColumn('contract_type', fn(User $user): string => $user->contract_type->label())
            ->addColumn('system_role', fn(User $user): string => $user->system_role->label())
            ->addColumn('status', fn(User $user): string => $user->status->label())
            ->addColumn('hire_date', fn(User $user): string => $user->hire_date?->format('d M Y') ?? '-')
            ->addColumn('actions', fn(User $user): string => $this->renderActionButtons($user))
            ->rawColumns(['actions'])
            ->make(true);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATION / UPDATE / DELETION METHODS
    |--------------------------------------------------------------------------
    */

    public function create(array $data): EmployeeDto
    {
        if ($this->isEmailExists($data['email'])) {
            throw new BusinessException(__('Employee already exists with this email.'));
        }

        $contractType = ContractType::safeFrom($data['contract_type'] ?? null) ?? ContractType::FullTime;
        $systemRole = SystemRole::safeFrom($data['system_role'] ?? null) ?? SystemRole::Employee;

        // Admin and HR users join directly; employees go through the approval pipeline.
        $isPrivilegedRole = in_array($systemRole, [SystemRole::Admin, SystemRole::Hr], true);
        $status = $isPrivilegedRole ? EmployeeStatus::Joined : EmployeeStatus::Pending;
        $creationNote = $isPrivilegedRole
            ? 'Automatically joined as a privileged role (' . $systemRole->value . ').'
            : 'Initial employment status assigned during employee creation.';
        $plainPassword = Str::password(14);

        $user = DB::transaction(function () use ($data, $contractType, $status, $systemRole, $creationNote, $plainPassword): User {
            $username = $data['username'] ?? $this->generateUsername($data['full_name'] ?? $data['name']);

            $created = User::query()->create([
                'full_name' => $data['full_name'] ?? $data['name'],
                'email' => $data['email'],
                'username' => $username,
                'phone_number' => $data['phone_number'] ?? $data['phone'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'hire_date' => $data['hire_date'] ?? null,
                'contract_type' => $contractType,
                'system_role' => $systemRole,
                'status' => $status,
                'password' => Hash::make($plainPassword),
            ]);

            $created->syncRoles([$systemRole->value]);

            if ($systemRole === SystemRole::Employee) {
                $this->syncAssignments($created, $data['assignments'] ?? []);
            }

            $this->recordStatusHistory(
                employee: $created,
                fromStatus: null,
                toStatus: $status,
                action: 'employee_created',
                note: $creationNote,
            );

            return $created;
        });

        $this->clearCache();

        $loadedUser = $user->load(['assignments.subCompany:id,name', 'assignments.squad:id,name', 'assignments.hierarchy:id,title,level,type']);

        event(new EmployeeCreated(
            employee: $loadedUser,
            actor: $this->resolveActor(),
            assignmentSnapshot: $this->buildAssignmentSnapshot($loadedUser),
            status: $status,
            action: 'employee_created',
            note: $creationNote,
            details: [
                'initial_password' => $plainPassword,
            ],
        ));

        return EmployeeDto::fromModel(
            $loadedUser
        );
    }

    public function update(int $id, array $data): EmployeeDto
    {
        $user = User::findOrFail($id);

        if ($this->isEmailExists($data['email'] ?? $user->email, $id)) {
            throw new BusinessException(__('An employee with the same email already exists.'));
        }

        $updatedUser = DB::transaction(function () use ($data, $user): User {
            $payload = [
                'full_name' => $data['full_name'] ?? $data['name'] ?? $user->full_name,
                'email' => $data['email'] ?? $user->email,
                'phone_number' => $data['phone_number'] ?? $data['phone'] ?? $user->phone_number,
                'date_of_birth' => $data['date_of_birth'] ?? $user->date_of_birth,
                'hire_date' => $data['hire_date'] ?? $user->hire_date,
                'contract_type' => ContractType::safeFrom($data['contract_type'] ?? null) ?? $user->contract_type,
                'system_role' => SystemRole::safeFrom($data['system_role'] ?? null) ?? $user->system_role,
                'username' => $data['username'] ?? $user->username,
            ];

            $oldRole = $user->system_role;
            $user->update($payload);

            $newRole = $user->system_role;

            if (isset($payload['system_role'])) {
                $role = $payload['system_role'] instanceof SystemRole
                    ? $payload['system_role']->value
                    : (string) $payload['system_role'];

                $user->syncRoles([$role]);
            }

            if ($user->system_role === SystemRole::Employee) {
                $this->syncAssignments($user, $data['assignments'] ?? []);
            } else {
                $user->assignments()->delete();
            }

            return $user;
        });

        $this->clearCache();

        return EmployeeDto::fromModel(
            $updatedUser->fresh(['assignments.subCompany:id,name', 'assignments.squad:id,name', 'assignments.hierarchy:id,title,level,type'])
        );
    }

    public function moveToOnboarding(int $id): EmployeeDto
    {
        $user = User::findOrFail($id);

        if ($user->status !== EmployeeStatus::Pending) {
            throw new BusinessException(__('Only pending employees can be moved to onboarding.'));
        }

        return $this->transitionEmployeeStatus(
            user: $user,
            newStatus: EmployeeStatus::Onboarding,
            action: 'moved_to_onboarding',
            note: 'Employee moved from pending to onboarding.',
        );
    }

    public function confirmJoin(int $id): EmployeeDto
    {
        $user = User::findOrFail($id);

        if ($user->status !== EmployeeStatus::Onboarding) {
            throw new BusinessException(__('Only onboarding employees can be marked as joined.'));
        }

        return $this->transitionEmployeeStatus(
            user: $user,
            newStatus: EmployeeStatus::Joined,
            action: 'confirmed_join',
            note: 'Employee onboarding completed and joined confirmed.',
        );
    }

    public function destroy(int $id): void
    {
        $user = User::findOrFail($id);

        $user->delete();

        $this->clearCache();
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER METHODS
    |--------------------------------------------------------------------------
    */

    private function clearCache(): void
    {
        Cache::forget(self::CACHE_PREFIX . '.all');
        Cache::forget(self::CACHE_PREFIX . '.active');
        Cache::forget(self::CACHE_PREFIX . '.stats.metrics');
        Cache::forget(self::CACHE_PREFIX . '.options');
    }

    private function applySearchFilters(Builder $query): Builder
    {
        $searchUsername = request('search_username');
        if (filled($searchUsername)) {
            $query->where('username', 'like', "%{$searchUsername}%");
        }

        $searchFullName = request('search_full_name');
        if (filled($searchFullName)) {
            $query->where('full_name', 'like', "%{$searchFullName}%");
        }

        $searchEmail = request('search_email');
        if (filled($searchEmail)) {
            $query->where('email', 'like', "%{$searchEmail}%");
        }

        $searchStatus = request('search_status');
        if (filled($searchStatus)) {
            $status = EmployeeStatus::safeFrom($searchStatus);
            if ($status) {
                $query->where('status', $status->value);
            }
        }

        $searchRole = request('search_role');
        if (filled($searchRole)) {
            $role = SystemRole::safeFrom($searchRole);
            if ($role) {
                $query->where('system_role', $role->value);
            }
        }

        return $query;
    }

    protected function renderActionButtons(User $user): string
    {
        $authUser = Auth::user();
        if (!$authUser instanceof User) {
            return '';
        }

        $actions = $this->buildActions($authUser);
        $singleActions = $this->buildSingleActions($authUser, $user);

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
            'id' => $user->id,
            'type' => 'Employee',
            'deleteName' => $user->full_name,
        ])->render();
    }

    protected function buildActions(User $authUser): array
    {
        $actions = [];

        if ($authUser->hasPermissionTo('employees.view')) {
            $actions[] = 'view';
        }

        if ($authUser->hasPermissionTo('employees.update')) {
            $actions[] = 'edit';
        }

        if ($authUser->hasPermissionTo('employees.destroy')) {
            $actions[] = 'delete';
        }

        return $actions;
    }

    protected function buildSingleActions(User $authUser, User $user): array
    {
        return match ($user->status) {
            EmployeeStatus::Pending => $authUser->hasPermissionTo('employees.move-to-onboarding') ? [
                [
                    'action' => 'onboard',
                    'label' => 'Start Onboarding',
                    'icon' => 'bx bx-loader-circle',
                    'tone' => 'warning',
                    'show_label' => true,
                    'class' => 'btn-onboard-employee',
                    'data' => [
                        'name' => $user->full_name,
                    ],
                ]
            ] : [],
            EmployeeStatus::Onboarding => $authUser->hasPermissionTo('employees.confirm-join') ? [
                [
                    'action' => 'join',
                    'label' => 'Confirm Join',
                    'icon' => 'bx bx-user-check',
                    'tone' => 'success',
                    'show_label' => true,
                    'class' => 'btn-join-employee',
                    'data' => [
                        'name' => $user->full_name,
                    ],
                ]
            ] : [],
            default => [],
        };
    }

    private function transitionEmployeeStatus(
        User $user,
        EmployeeStatus $newStatus,
        string $action,
        ?string $note = null,
        array $details = [],
    ): EmployeeDto {
        $fromStatus = $user->status;

        if ($fromStatus === $newStatus) {
            return EmployeeDto::fromModel(
                $user->fresh(['assignments.subCompany:id,name', 'assignments.squad:id,name', 'assignments.hierarchy:id,title,level,type'])
                ?? $user
            );
        }

        $user->update([
            'status' => $newStatus,
        ]);

        $this->recordStatusHistory(
            employee: $user,
            fromStatus: $fromStatus,
            toStatus: $newStatus,
            action: $action,
            note: $note,
            details: $details,
        );

        $this->clearCache();

        $loadedUser = $user->fresh(['assignments.subCompany:id,name', 'assignments.squad:id,name', 'assignments.hierarchy:id,title,level,type'])
            ?? $user->load(['assignments.subCompany:id,name', 'assignments.squad:id,name', 'assignments.hierarchy:id,title,level,type']);

        event(new EmployeeStatusChanged(
            employee: $loadedUser,
            actor: $this->resolveActor(),
            fromStatus: $fromStatus,
            toStatus: $newStatus,
            action: $action,
            note: $note,
            assignmentSnapshot: $this->buildAssignmentSnapshot($loadedUser),
            details: $details,
        ));

        return EmployeeDto::fromModel(
            $loadedUser
        );
    }

    private function recordStatusHistory(
        User $employee,
        ?EmployeeStatus $fromStatus,
        EmployeeStatus $toStatus,
        string $action,
        ?string $note = null,
        array $details = [],
    ): void {
        $request = request();

        $contextDetails = array_filter([
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ], static fn($value): bool => filled($value));

        $payloadDetails = array_merge($contextDetails, $details);

        StatusHistory::query()->create([
            'statusable_type' => User::class,
            'statusable_id' => $employee->id,
            'actor_id' => Auth::id(),
            'from_status' => $fromStatus?->value,
            'to_status' => $toStatus->value,
            'action' => $action,
            'note' => $note,
            'details' => !empty($payloadDetails) ? $payloadDetails : null,
            'recorded_at' => now(),
        ]);
    }

    /**
     * @param array<int, array<string, mixed>>|null $assignments
     */
    private function syncAssignments(User $user, ?array $assignments): void
    {
        $preparedAssignments = collect($assignments ?? [])
            ->filter(static function ($assignment): bool {
                return filled($assignment['hierarchy_id'] ?? null);
            })
            ->map(static function ($assignment, $index): array {
                return [
                    'sub_company_id' => filled($assignment['sub_company_id'] ?? null) ? (int) $assignment['sub_company_id'] : null,
                    'squad_id' => filled($assignment['squad_id'] ?? null) ? (int) $assignment['squad_id'] : null,
                    'hierarchy_id' => (int) $assignment['hierarchy_id'],
                    'is_primary' => $index === 0,
                ];
            })
            ->unique(static function (array $assignment): string {
                $subCompanyId = $assignment['sub_company_id'] ?? 'null';
                $squadId = $assignment['squad_id'] ?? 'null';

                return $subCompanyId . '|' . $squadId . '|' . $assignment['hierarchy_id'];
            })
            ->values()
            ->all();

        $user->assignments()->delete();

        if (!empty($preparedAssignments)) {
            $user->assignments()->createMany($preparedAssignments);
        }
    }

    private function resolveActor(): ?User
    {
        $actor = Auth::user();

        return $actor instanceof User ? $actor : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildAssignmentSnapshot(User $employee): array
    {
        $employee->loadMissing([
            'assignments.subCompany:id,name',
            'assignments.squad:id,name',
            'assignments.hierarchy:id,title,level,type',
        ]);

        return $employee->assignments
            ->map(static function (EmployeeAssignment $assignment): array {
                return [
                    'sub_company_id' => $assignment->sub_company_id,
                    'sub_company_name' => $assignment->subCompany?->name,
                    'squad_id' => $assignment->squad_id,
                    'squad_name' => $assignment->squad?->name,
                    'hierarchy_id' => $assignment->hierarchy_id,
                    'hierarchy_title' => $assignment->hierarchy?->title,
                    'hierarchy_type' => $assignment->hierarchy?->type,
                ];
            })
            ->values()
            ->all();
    }

    public function isEmailExists(string $email, ?int $id = null): bool
    {
        $query = User::query()->where('email', $email);

        if ($id !== null) {
            $query->where('id', '!=', $id);
        }

        return $query->exists();
    }

    /**
     * Generate a unique username based on the full name and the estimated next ID.
     */
    private function generateUsername(string $fullName): string
    {
        $nameParts = array_values(array_filter(preg_split('/\s+/', trim($fullName))));

        $firstInitial = '';
        $lastName = 'employee';

        if (!empty($nameParts)) {
            $firstInitial = mb_substr(mb_strtolower($nameParts[0]), 0, 1);
            $lastName = mb_strtolower($nameParts[count($nameParts) - 1]);
        }

        $base = Str::of($firstInitial . $lastName)
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', '')
            ->whenEmpty(fn () => 'employee');

        // Estimate next ID
        $nextId = (User::max('id') ?? 0) + 1;

        $username = (string) $base . $nextId;

        // Ensure uniqueness if for some reason collisions occur
        $originalUsername = $username;
        $counter = 1;
        while (User::where('username', $username)->exists()) {
            $username = $originalUsername . $counter;
            $counter++;
        }

        return $username;
    }
}