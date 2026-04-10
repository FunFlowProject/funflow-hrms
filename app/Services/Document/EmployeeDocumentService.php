<?php

declare(strict_types=1);

namespace App\Services\Document;

use App\DTOs\Document\DocumentDto;
use App\DTOs\Document\DocumentUserStatusDto;
use App\Enums\DocumentEmployeeStatus;
use App\Enums\DocumentScope;
use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class EmployeeDocumentService
{
    public function getDocumentsForUser(User $user, array $filters = []): array
    {
        $assignments = $user->assignments()->with(['subCompany', 'squad'])->get();
        
        $subCompanyIds = $assignments->pluck('sub_company_id')->filter()->unique()->toArray();
        $squadIds = $assignments->pluck('squad_id')->filter()->unique()->toArray();

        $query = Document::query()
            ->with(['subCompany', 'squad'])
            ->where(function (Builder $q) use ($subCompanyIds, $squadIds) {
                // Company Scope
                $q->where('scope_type', DocumentScope::Company->value)
                // SubCompany Scope
                ->orWhere(function (Builder $sq) use ($subCompanyIds) {
                    $sq->where('scope_type', DocumentScope::SubCompany->value)
                        ->whereIn('scope_id', $subCompanyIds);
                })
                // Squad Scope
                ->orWhere(function (Builder $sq) use ($squadIds) {
                    $sq->where('scope_type', DocumentScope::Squad->value)
                        ->whereIn('scope_id', $squadIds);
                });
            });

        // Eager load pivot for status check
        $query->with(['users' => function ($q) use ($user) {
            $q->where('user_id', $user->id);
        }]);

        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        if (!empty($filters['classification'])) {
            $query->where('classification', $filters['classification']);
        }

        $documents = $query->orderByDesc('created_at')->get();

        return $documents->map(function (Document $document) {
            $pivot = $document->users->first()?->pivot;
            
            $statusValue = $pivot?->status->value ?? DocumentEmployeeStatus::New->value;
            $statusLabel = DocumentEmployeeStatus::from($statusValue)->label();
            
            $statusDto = new DocumentUserStatusDto(
                status: $statusValue,
                status_label: $statusLabel,
                acknowledged_at: $pivot?->acknowledged_at ? $pivot->acknowledged_at->format('d M Y, h:i A') : null,
            );

            return DocumentDto::fromModel($document, $statusDto);
        })->toArray();
    }

    public function markAsViewed(User $user, Document $document): void
    {
        $pivot = $document->users()->where('user_id', $user->id)->first()?->pivot;
        
        if (!$pivot) {
            $document->users()->attach($user->id, [
                'status' => 'viewed',
            ]);
        } elseif ($pivot->status === DocumentEmployeeStatus::New) {
            $document->users()->updateExistingPivot($user->id, [
                'status' => 'viewed',
            ]);
        }
    }

    public function acknowledgeDocument(User $user, Document $document): void
    {
        // Only require acknowledgment if the document demands it
        if (!$document->requires_acknowledgment) {
            return;
        }

        $pivot = $document->users()->where('user_id', $user->id)->first()?->pivot;

        if (!$pivot) {
            $document->users()->attach($user->id, [
                'status' => 'acknowledged',
                'acknowledged_at' => Carbon::now(),
            ]);
        } elseif ($pivot->status !== DocumentEmployeeStatus::Acknowledged) {
            $document->users()->updateExistingPivot($user->id, [
                'status' => 'acknowledged',
                'acknowledged_at' => Carbon::now(),
            ]);
        }
    }
}
