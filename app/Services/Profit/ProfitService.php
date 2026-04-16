<?php

declare(strict_types=1);

namespace App\Services\Profit;

use App\DTOs\Profit\ProfitBalanceDto;
use App\DTOs\Profit\WithdrawalRequestDto;
use App\Enums\ProfitTransactionType;
use App\Enums\SystemRole;
use App\Enums\WithdrawalRequestStatus;
use App\Events\Profit\ProfitDistributed;
use App\Events\Profit\WithdrawalApproved;
use App\Events\Profit\WithdrawalRejected;
use App\Exceptions\BusinessException;
use App\Models\EmployeeAssignment;
use App\Models\ProfitBalance;
use App\Models\ProfitTransaction;
use App\Models\Squad;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ProfitService
{
    private const CACHE_PREFIX = 'profit';

    public function options(): array
    {
        return [
            'squads' => Squad::query()
                ->active()
                ->with('subCompany:id,name')
                ->orderBy('name')
                ->get()
                ->map(static fn (Squad $squad): array => [
                    'id' => (int) $squad->id,
                    'name' => $squad->name,
                    'sub_company_id' => $squad->sub_company_id !== null ? (int) $squad->sub_company_id : null,
                    'sub_company_name' => $squad->subCompany?->name,
                    'label' => trim(($squad->subCompany?->name ? $squad->subCompany->name . ' - ' : '') . $squad->name),
                ])
                ->values()
                ->all(),
            'withdrawalStatuses' => WithdrawalRequestStatus::options(),
            'transactionTypes' => ProfitTransactionType::options(),
        ];
    }

    public function employees(): array
    {
        return User::query()
            ->where('system_role', SystemRole::Employee->value)
            ->with([
                'profitBalance',
                'assignments' => static function (Builder $query): void {
                    $query->active()
                        ->with(['subCompany:id,name', 'squad:id,name'])
                        ->orderByDesc('is_primary')
                        ->orderBy('id');
                },
            ])
            ->orderBy('full_name')
            ->get()
            ->map(fn (User $user): array => $this->toEmployeeSelectionPayload($user))
            ->values()
            ->all();
    }

    public function withdrawalRequests(): JsonResponse
    {
        $query = WithdrawalRequest::query()
            ->with(['user:id,full_name,email', 'actor:id,full_name,email'])
            ->orderByDesc('created_at');

        $query = $this->applyWithdrawalRequestFilters($query);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('employee_name', static fn (WithdrawalRequest $withdrawalRequest): string => $withdrawalRequest->user?->full_name ?? '-')
            ->addColumn('amount', static fn (WithdrawalRequest $withdrawalRequest): string => format_money((float) $withdrawalRequest->amount))
            ->addColumn('status', fn (WithdrawalRequest $withdrawalRequest): string => $this->renderWithdrawalStatusBadge($withdrawalRequest->status))
            ->addColumn('request_date', static fn (WithdrawalRequest $withdrawalRequest): string => $withdrawalRequest->created_at->format('d M Y, h:i A'))
            ->addColumn('acted_by_name', static fn (WithdrawalRequest $withdrawalRequest): string => $withdrawalRequest->actor?->full_name ?? '-')
            ->addColumn('actions', fn (WithdrawalRequest $withdrawalRequest): string => $this->renderWithdrawalActions($withdrawalRequest))
            ->rawColumns(['status', 'actions'])
            ->make(true);
    }

    public function myBalance(User $user): ProfitBalanceDto
    {
        $balance = $this->ensureProfitBalance($user)->loadMissing('user');

        return ProfitBalanceDto::fromModel($balance);
    }

    public function requestWithdrawal(User $user, float $amount): WithdrawalRequestDto
    {
        if ($amount <= 0) {
            throw new BusinessException(__('The withdrawal amount must be greater than zero.'));
        }

        $withdrawalRequest = DB::transaction(function () use ($user, $amount): WithdrawalRequest {
            $balance = ProfitBalance::query()
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            $currentBalance = (float) ($balance?->balance ?? 0);

            if ($amount > $currentBalance) {
                throw new BusinessException(__('Requested amount must be less than or equal to your current profit balance.'));
            }

            return WithdrawalRequest::query()->create([
                'user_id' => $user->id,
                'amount' => round($amount, 2),
                'status' => WithdrawalRequestStatus::Pending,
            ]);
        });

        return WithdrawalRequestDto::fromModel(
            $withdrawalRequest->fresh(['user:id,full_name,email', 'actor:id,full_name,email'])
        );
    }

    public function myTransactions(User $user): JsonResponse
    {
        $query = ProfitTransaction::query()
            ->where('user_id', $user->id)
            ->with(['user:id,full_name,email', 'performedBy:id,full_name,email', 'related'])
            ->orderByDesc('created_at');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('type', fn (ProfitTransaction $profitTransaction): string => $this->renderTransactionTypeBadge($profitTransaction->type))
            ->addColumn('amount', static fn (ProfitTransaction $profitTransaction): string => format_money((float) $profitTransaction->amount))
            ->addColumn('balance_after', static fn (ProfitTransaction $profitTransaction): string => format_money((float) $profitTransaction->balance_after))
            ->addColumn('description', static fn (ProfitTransaction $profitTransaction): string => $profitTransaction->description ?? '-')
            ->addColumn('performed_by_name', static fn (ProfitTransaction $profitTransaction): string => $profitTransaction->performedBy?->full_name ?? '-')
            ->addColumn('status', fn (ProfitTransaction $profitTransaction): string => $this->renderTransactionStatusLabel($profitTransaction))
            ->addColumn('created_at', static fn (ProfitTransaction $profitTransaction): string => $profitTransaction->created_at->format('d M Y, h:i A'))
            ->rawColumns(['type', 'status'])
            ->make(true);
    }

    public function myWithdrawalRequests(User $user): JsonResponse
    {
        $query = WithdrawalRequest::query()
            ->where('user_id', $user->id)
            ->with(['user:id,full_name,email', 'actor:id,full_name,email'])
            ->orderByDesc('created_at');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('amount', static fn (WithdrawalRequest $withdrawalRequest): string => format_money((float) $withdrawalRequest->amount))
            ->addColumn('status', fn (WithdrawalRequest $withdrawalRequest): string => $this->renderWithdrawalStatusBadge($withdrawalRequest->status))
            ->addColumn('request_date', static fn (WithdrawalRequest $withdrawalRequest): string => $withdrawalRequest->created_at->format('d M Y, h:i A'))
            ->addColumn('acted_at', static fn (WithdrawalRequest $withdrawalRequest): string => $withdrawalRequest->acted_at?->format('d M Y, h:i A') ?? '-')
            ->addColumn('rejection_reason', static fn (WithdrawalRequest $withdrawalRequest): string => $withdrawalRequest->rejection_reason ?? '-')
            ->rawColumns(['status'])
            ->make(true);
    }

    public function distribute(array $userIds, float $amount, User $admin): void
    {
        $amount = round($amount, 2);
        $selectedUserIds = $this->normalizeIds($userIds);

        if ($amount <= 0) {
            throw new BusinessException(__('The distribution amount must be greater than zero.'));
        }

        if ($selectedUserIds === []) {
            throw new BusinessException(__('Please select at least one employee.'));
        }

        $employees = User::query()
            ->where('system_role', SystemRole::Employee->value)
            ->whereIn('id', $selectedUserIds)
            ->orderBy('id')
            ->get();

        if ($employees->count() !== count($selectedUserIds)) {
            throw new BusinessException(__('One or more selected employees could not be found.'));
        }

        $dispatchPayloads = DB::transaction(function () use ($employees, $amount, $admin): array {
            $dispatchPayloads = [];

            foreach ($employees as $employee) {
                $balance = ProfitBalance::query()->firstOrCreate(
                    ['user_id' => $employee->id],
                    ['balance' => 0]
                );

                $currentBalance = (float) $balance->balance;
                $newBalance = round($currentBalance + $amount, 2);

                $balance->balance = $newBalance;
                $balance->save();

                $transaction = ProfitTransaction::query()->create([
                    'user_id' => $employee->id,
                    'type' => ProfitTransactionType::Credit,
                    'amount' => $amount,
                    'balance_after' => $newBalance,
                    'description' => __('Profit distribution'),
                    'performed_by' => $admin->id,
                ]);

                $dispatchPayloads[] = [
                    'employee' => $employee->fresh(),
                    'transaction' => $transaction->fresh(['user:id,full_name,email', 'performedBy:id,full_name,email']),
                ];
            }

            return $dispatchPayloads;
        });

        $this->clearBalanceCache($selectedUserIds);

        foreach ($dispatchPayloads as $payload) {
            event(new ProfitDistributed(
                employee: $payload['employee'],
                transaction: $payload['transaction'],
                admin: $admin,
            ));
        }
    }

    public function approveWithdrawal(int $withdrawalRequestId, User $admin): WithdrawalRequestDto
    {
        $transaction = null;

        $withdrawalRequest = DB::transaction(function () use ($withdrawalRequestId, $admin, &$transaction): WithdrawalRequest {
            $withdrawalRequest = WithdrawalRequest::query()
                ->with(['user:id,full_name,email', 'actor:id,full_name,email'])
                ->whereKey($withdrawalRequestId)
                ->lockForUpdate()
                ->first();

            if (!$withdrawalRequest instanceof WithdrawalRequest) {
                throw new BusinessException(__(':field not found.', ['field' => __('withdrawal request')]));
            }

            if (WithdrawalRequestStatus::safeFrom($withdrawalRequest->status) !== WithdrawalRequestStatus::Pending) {
                throw new BusinessException(__('Only pending withdrawal requests can be approved.'));
            }

            $balance = ProfitBalance::query()
                ->where('user_id', $withdrawalRequest->user_id)
                ->lockForUpdate()
                ->first();

            $currentBalance = (float) ($balance?->balance ?? 0);
            $requestedAmount = (float) $withdrawalRequest->amount;

            if ($requestedAmount > $currentBalance) {
                throw new BusinessException(__('The employee does not have enough profit balance for this withdrawal.'));
            }

            if (!$balance instanceof ProfitBalance) {
                $balance = ProfitBalance::query()->create([
                    'user_id' => $withdrawalRequest->user_id,
                    'balance' => 0,
                ]);
            }

            $newBalance = round($currentBalance - $requestedAmount, 2);
            $balance->balance = $newBalance;
            $balance->save();

            $transaction = ProfitTransaction::query()->create([
                'user_id' => $withdrawalRequest->user_id,
                'type' => ProfitTransactionType::Debit,
                'amount' => $requestedAmount,
                'balance_after' => $newBalance,
                'description' => __('Withdrawal approval'),
                'performed_by' => $admin->id,
                'related_type' => WithdrawalRequest::class,
                'related_id' => $withdrawalRequest->id,
            ]);

            $withdrawalRequest->update([
                'status' => WithdrawalRequestStatus::Approved,
                'acted_by' => $admin->id,
                'acted_at' => now(),
                'rejection_reason' => null,
            ]);

            return $withdrawalRequest->fresh(['user:id,full_name,email', 'actor:id,full_name,email']);
        });

        $this->clearBalanceCache([$withdrawalRequest->user_id]);

        event(new WithdrawalApproved(
            withdrawalRequest: $withdrawalRequest,
            admin: $admin,
            transaction: $transaction?->fresh(['user:id,full_name,email', 'performedBy:id,full_name,email', 'related']) ?? $transaction,
        ));

        return WithdrawalRequestDto::fromModel($withdrawalRequest);
    }

    public function rejectWithdrawal(int $withdrawalRequestId, User $admin, ?string $reason = null): WithdrawalRequestDto
    {
        $withdrawalRequest = DB::transaction(function () use ($withdrawalRequestId, $admin, $reason): WithdrawalRequest {
            $withdrawalRequest = WithdrawalRequest::query()
                ->with(['user:id,full_name,email', 'actor:id,full_name,email'])
                ->whereKey($withdrawalRequestId)
                ->lockForUpdate()
                ->first();

            if (!$withdrawalRequest instanceof WithdrawalRequest) {
                throw new BusinessException(__(':field not found.', ['field' => __('withdrawal request')]));
            }

            if (WithdrawalRequestStatus::safeFrom($withdrawalRequest->status) !== WithdrawalRequestStatus::Pending) {
                throw new BusinessException(__('Only pending withdrawal requests can be rejected.'));
            }

            $withdrawalRequest->update([
                'status' => WithdrawalRequestStatus::Rejected,
                'acted_by' => $admin->id,
                'acted_at' => now(),
                'rejection_reason' => filled($reason) ? trim($reason) : null,
            ]);

            return $withdrawalRequest->fresh(['user:id,full_name,email', 'actor:id,full_name,email']);
        });

        event(new WithdrawalRejected(
            withdrawalRequest: $withdrawalRequest,
            admin: $admin,
        ));

        return WithdrawalRequestDto::fromModel($withdrawalRequest);
    }

    private function applyWithdrawalRequestFilters(Builder $query): Builder
    {
        $searchStatus = request('search_status');
        if (filled($searchStatus)) {
            $status = WithdrawalRequestStatus::safeFrom($searchStatus);
            if ($status) {
                $query->where('status', $status->value);
            }
        }

        $searchEmployee = request('search_employee');
        if (filled($searchEmployee)) {
            $query->whereHas('user', static function (Builder $userQuery) use ($searchEmployee): void {
                $userQuery->where('full_name', 'like', '%' . trim((string) $searchEmployee) . '%');
            });
        }

        $searchFrom = request('search_from');
        if (filled($searchFrom)) {
            $query->whereDate('created_at', '>=', $searchFrom);
        }

        $searchTo = request('search_to');
        if (filled($searchTo)) {
            $query->whereDate('created_at', '<=', $searchTo);
        }

        return $query;
    }

    private function renderTransactionTypeBadge(ProfitTransactionType|string|null $type): string
    {
        $enum = ProfitTransactionType::safeFrom($type);
        if (!$enum) {
            return '-';
        }

        $tone = match ($enum) {
            ProfitTransactionType::Credit => 'success',
            ProfitTransactionType::Debit => 'danger',
        };

        return sprintf(
            '<span class="badge bg-%s rounded-pill px-3">%s</span>',
            $tone,
            e($enum->label())
        );
    }

    private function renderTransactionStatusLabel(ProfitTransaction $profitTransaction): string
    {
        $related = $profitTransaction->relationLoaded('related') ? $profitTransaction->related : null;

        if (!$related instanceof WithdrawalRequest) {
            return '-';
        }

        $status = WithdrawalRequestStatus::safeFrom($related->status) ?? WithdrawalRequestStatus::Pending;

        return sprintf(
            '<span class="badge bg-%s rounded-pill px-3">%s</span>',
            match ($status) {
                WithdrawalRequestStatus::Pending => 'warning',
                WithdrawalRequestStatus::Approved => 'success',
                WithdrawalRequestStatus::Rejected => 'danger',
            },
            e($status->label())
        );
    }

    private function renderWithdrawalStatusBadge(WithdrawalRequestStatus|string|null $status): string
    {
        $enum = WithdrawalRequestStatus::safeFrom($status);
        if (!$enum) {
            return '-';
        }

        $tone = match ($enum) {
            WithdrawalRequestStatus::Pending => 'warning',
            WithdrawalRequestStatus::Approved => 'success',
            WithdrawalRequestStatus::Rejected => 'danger',
        };

        return sprintf(
            '<span class="badge bg-%s rounded-pill px-3">%s</span>',
            $tone,
            e($enum->label())
        );
    }

    private function renderWithdrawalActions(WithdrawalRequest $withdrawalRequest): string
    {
        $status = WithdrawalRequestStatus::safeFrom($withdrawalRequest->status) ?? WithdrawalRequestStatus::Pending;

        if ($status !== WithdrawalRequestStatus::Pending) {
            return '<span class="text-secondary">-</span>';
        }

        return sprintf(
            '<div class="d-inline-flex gap-2">\n                <button type="button" class="btn btn-success btn-sm btn-approve-withdrawal" data-id="%1$d" data-employee="%2$s" data-amount="%3$s">\n                    <i class="bx bx-check"></i> Approve\n                </button>\n                <button type="button" class="btn btn-outline-danger btn-sm btn-reject-withdrawal" data-id="%1$d" data-employee="%2$s" data-amount="%3$s">\n                    <i class="bx bx-x"></i> Reject\n                </button>\n            </div>',
            $withdrawalRequest->id,
            e($withdrawalRequest->user?->full_name ?? '-'),
            e(format_money((float) $withdrawalRequest->amount))
        );
    }

    private function toEmployeeSelectionPayload(User $user): array
    {
        $primaryAssignment = $this->resolvePrimaryAssignment($user);
        $balance = $user->relationLoaded('profitBalance') && $user->profitBalance
            ? (float) $user->profitBalance->balance
            : 0.0;

        return [
            'id' => (int) $user->id,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'status' => $user->status->value,
            'status_label' => $user->status->label(),
            'squad_id' => $primaryAssignment?->squad_id !== null ? (int) $primaryAssignment->squad_id : null,
            'squad_name' => $primaryAssignment?->squad?->name,
            'sub_company_id' => $primaryAssignment?->sub_company_id !== null ? (int) $primaryAssignment->sub_company_id : null,
            'sub_company_name' => $primaryAssignment?->subCompany?->name,
            'balance' => $balance,
            'balance_formatted' => format_money($balance),
        ];
    }

    private function resolvePrimaryAssignment(User $user): ?EmployeeAssignment
    {
        $assignments = $user->assignments;

        if ($assignments === null) {
            $assignments = $user->assignments()->with(['subCompany:id,name', 'squad:id,name'])->get();
        }

        return $assignments->firstWhere('is_primary', true)
            ?? $assignments->first();
    }

    private function ensureProfitBalance(User $user): ProfitBalance
    {
        $balance = ProfitBalance::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0]
        );

        return $balance->loadMissing('user');
    }

    private function clearBalanceCache(array|int $userIds): void
    {
        foreach ($this->normalizeIds($userIds) as $userId) {
            Cache::forget($this->balanceCacheKey($userId));
        }
    }

    private function balanceCacheKey(int $userId): string
    {
        return self::CACHE_PREFIX . '.balance.' . $userId;
    }

    private function normalizeIds(array|int $values): array
    {
        $values = Arr::wrap($values);

        return array_values(array_unique(array_filter(array_map(
            static fn ($value): int => (int) $value,
            $values
        ), static fn (int $value): bool => $value > 0)));
    }
}
