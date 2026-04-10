<?php

declare(strict_types=1);

namespace App\Http\Controllers\Document;

use App\Http\Controllers\Controller;
use App\Http\Requests\Document\StoreDocumentRequest;
use App\Http\Requests\Document\UpdateDocumentRequest;
use App\Services\Document\DocumentService;
use App\Services\Document\EmployeeDocumentService;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Throwable;

class DocumentController extends Controller
{
    public function __construct(
        protected readonly DocumentService $documentService,
        protected readonly EmployeeDocumentService $employeeDocumentService,
    ) {}

    /** Display the document management page. */
    public function index(): View
    {
        return view('documents.index');
    }

    /** Retrieve all documents. */
    public function all()
    {
        try {
            return $this->apiResponse(
                data: $this->documentService->all(),
                message: 'Documents fetched successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to fetch documents');
        }
    }

    /** Retrieve document stats. */
    public function stats()
    {
        try {
            return $this->apiResponse(
                data: $this->documentService->stats(),
                message: 'Document statistics fetched successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to fetch document statistics');
        }
    }

    /** DataTables endpoint for documents. */
    public function datatable()
    {
        try {
            return $this->documentService->datatable();
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to fetch document data');
        }
    }

    /** Store a newly created document. */
    public function store(StoreDocumentRequest $request)
    {
        try {
            return $this->apiResponse(
                data: $this->documentService->create($request->validated(), $request->file('file')),
                message: 'Document created successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to create document');
        }
    }

    /** Update the specified document. */
    public function update(UpdateDocumentRequest $request, int $id)
    {
        try {
            return $this->apiResponse(
                data: $this->documentService->update($id, $request->validated(), $request->file('file')),
                message: 'Document updated successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to update document');
        }
    }

    /** Remove the specified document. */
    public function destroy(int $id)
    {
        try {
            $this->documentService->destroy($id);

            return $this->apiResponse(
                data: null,
                message: 'Document deleted successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to delete document');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | EMPLOYEE ENDPOINTS
    |--------------------------------------------------------------------------
    */

    /** Display the my documents page. */
    public function employeeIndex(): View
    {
        return view('documents.my-documents');
    }

    /** Retrieve documents list for the authenticated employee (JSON). */
    public function myDocumentsList(Request $request)
    {
        try {
            $user = Auth::user();
            $filters = $request->only(['search', 'classification']);
            
            return $this->apiResponse(
                data: $this->employeeDocumentService->getDocumentsForUser($user, $filters),
                message: 'Documents fetched successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to fetch my documents');
        }
    }

    /** Retrieve document stats for the authenticated employee. */
    public function myDocumentsStats(Request $request)
    {
        try {
            $user = Auth::user();
            // Just computing basic stats from the list instead of an optimized query for now
            $documents = $this->employeeDocumentService->getDocumentsForUser($user, []);
            
            $stats = [
                'total' => count($documents),
                'new' => collect($documents)->where('employee_status.status', 'new')->count(),
                'requires_ack' => collect($documents)->filter(fn($d) => $d->requires_acknowledgment && $d->employee_status?->status !== 'acknowledged')->count(),
                'acknowledged' => collect($documents)->where('employee_status.status', 'acknowledged')->count(),
            ];

            return $this->apiResponse(
                data: $stats,
                message: 'My Document statistics fetched successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to fetch my document statistics');
        }
    }

    /** Mark document as viewed by the employee. */
    public function markViewed(int $id)
    {
        try {
            $document = Document::findOrFail($id);
            $this->employeeDocumentService->markAsViewed(Auth::user(), $document);

            return $this->apiResponse(
                data: null,
                message: 'Document marked as viewed.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to update document status');
        }
    }

    /** Acknowledge document by the employee. */
    public function acknowledge(int $id)
    {
        try {
            $document = Document::findOrFail($id);
            $this->employeeDocumentService->acknowledgeDocument(Auth::user(), $document);

            return $this->apiResponse(
                data: null,
                message: 'Document acknowledged successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to acknowledge document');
        }
    }
}
