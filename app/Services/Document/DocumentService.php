<?php

declare(strict_types=1);

namespace App\Services\Document;

use App\DTOs\Document\DocumentDto;
use App\DTOs\Document\DocumentStatsDto;
use App\Enums\DocumentScope;
use App\Exceptions\BusinessException;
use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class DocumentService
{
    public function all(): array
    {
        return Document::query()
            ->with(['subCompany', 'squad'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Document $document) => DocumentDto::fromModel($document))
            ->toArray();
    }

    public function stats(): DocumentStatsDto
    {
        $lastUpdate = Document::query()->max('updated_at');

        $metrics = [
            'total' => Document::query()->count(),
            'public' => Document::query()->where('classification', 'public')->count(),
            'internal' => Document::query()->where('classification', 'internal_use_only')->count(),
            'confidential' => Document::query()->where('classification', 'confidential')->count(),
            'last_update' => formatDateTime($lastUpdate),
        ];

        return DocumentStatsDto::fromMetrics($metrics);
    }

    public function datatable(): JsonResponse
    {
        $query = Document::query()->with(['subCompany', 'squad']);
        
        $query = $this->applySearchFilters($query);
        $query->orderByDesc('created_at');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('classification_label', fn (Document $document) => $document->classification->label())
            ->addColumn('scope_label', function (Document $document) {
                $scopeStr = $document->scope_type->label();
                if ($document->scope_type === DocumentScope::SubCompany) {
                    $scopeStr .= ' - ' . ($document->subCompany?->name ?? 'N/A');
                } elseif ($document->scope_type === DocumentScope::Squad) {
                    $scopeStr .= ' - ' . ($document->squad?->name ?? 'N/A');
                }
                return $scopeStr;
            })
            ->addColumn('created_at', fn (Document $document) => $document->created_at->format('d M Y, h:i A'))
            ->addColumn('actions', fn (Document $document) => $this->renderActionButtons($document))
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function create(array $data, ?UploadedFile $file = null): DocumentDto
    {
        $filePath = null;
        if ($file) {
            $filePath = $file->store('documents', 'public');
        } elseif (isset($data['file_url'])) {
            $filePath = $data['file_url'];
        }

        if (!$filePath) {
            throw new BusinessException(__('Document file or URL is required.'));
        }

        $document = Document::create([
            'name' => $data['name'],
            'file_type' => $file ? 'upload' : 'url',
            'file_path' => $filePath,
            'classification' => $data['classification'],
            'scope_type' => $data['scope_type'],
            'scope_id' => $data['scope_id'] ?? null,
            'requires_acknowledgment' => $data['requires_acknowledgment'] ?? false,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return DocumentDto::fromModel($document->load(['subCompany', 'squad']));
    }

    public function update(int $id, array $data, ?UploadedFile $file = null): DocumentDto
    {
        $document = Document::findOrFail($id);

        $filePath = $document->file_path;
        $fileType = $document->file_type;
        
        if ($file) {
            if ($fileType === 'upload' && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            $filePath = $file->store('documents', 'public');
            $fileType = 'upload';
        } elseif (!empty($data['file_url'])) {
            if ($fileType === 'upload' && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            $filePath = $data['file_url'];
            $fileType = 'url';
        }

        $document->update([
            'name' => $data['name'] ?? $document->name,
            'file_type' => $fileType,
            'file_path' => $filePath,
            'classification' => $data['classification'] ?? $document->classification,
            'scope_type' => $data['scope_type'] ?? $document->scope_type,
            'scope_id' => array_key_exists('scope_id', $data) ? $data['scope_id'] : $document->scope_id,
            'requires_acknowledgment' => $data['requires_acknowledgment'] ?? $document->requires_acknowledgment,
            'updated_by' => Auth::id(),
        ]);

        return DocumentDto::fromModel($document->load(['subCompany', 'squad']));
    }

    public function destroy(int $id): void
    {
        $document = Document::findOrFail($id);

        if ($document->file_type === 'upload' && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();
    }

    private function applySearchFilters(Builder $query): Builder
    {
        $searchName = request('search_name');
        if (filled($searchName)) {
            $query->where('name', 'like', "%{$searchName}%");
        }

        $classification = request('search_classification');
        if (filled($classification)) {
            $query->where('classification', $classification);
        }
        
        $scope = request('search_scope');
        if (filled($scope)) {
            $query->where('scope_type', $scope);
        }

        return $query;
    }

    private function renderActionButtons(Document $document): string
    {
        $authUser = Auth::user();
        if (!$authUser instanceof User) {
            return '';
        }

        $actions = [];
        if ($authUser->can('documents.update')) {
            $actions[] = 'edit';
        }
        if ($authUser->can('documents.destroy')) {
            $actions[] = 'delete';
        }

        if (empty($actions)) {
            return '';
        }

        return view('components.ui.table-actions', [
            'mode' => 'dropdown',
            'actions' => $actions,
            'id' => $document->id,
            'type' => 'Document',
            'deleteName' => $document->name,
            'editClass' => 'editDocumentBtn',
            'deleteClass' => 'deleteDocumentBtn',
        ])->render();
    }
}
