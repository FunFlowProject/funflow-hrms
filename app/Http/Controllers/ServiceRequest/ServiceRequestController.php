<?php

declare(strict_types=1);

namespace App\Http\Controllers\ServiceRequest;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceRequest\CompleteServiceRequestRequest;
use App\Http\Requests\ServiceRequest\MoveServiceRequestToInProgressRequest;
use App\Http\Requests\ServiceRequest\RejectServiceRequestRequest;
use App\Http\Requests\ServiceRequest\StoreServiceRequestRequest;
use App\Http\Requests\ServiceRequest\UpdateServiceRequestRequest;
use App\Services\ServiceRequest\ServiceRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Throwable;

class ServiceRequestController extends Controller
{
    public function __construct(
        protected readonly ServiceRequestService $serviceRequestService,
    ) {}

    /** Display the service requests page. */
    public function index(): View
    {
        return view('service-requests.index');
    }

    /** Display the employee service requests page. */
    public function employeeIndex(): View|RedirectResponse
    {
        if (Auth::user()?->can('service-requests.manage')) {
            return redirect()->route('service-requests.index');
        }

        return view('service-requests.my-index');
    }

    /** Retrieve service request options. */
    public function options()
    {
        try {
            return $this->apiResponse(
                data: $this->serviceRequestService->options(),
                message: 'Service request options fetched successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to fetch service request options');
        }
    }

    /** Retrieve service request stats. */
    public function stats()
    {
        try {
            return $this->apiResponse(
                data: $this->serviceRequestService->stats(),
                message: 'Service request statistics fetched successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to fetch service request statistics');
        }
    }

    /** Display the specified service request. */
    public function show(int $id)
    {
        try {
            return $this->apiResponse(
                data: $this->serviceRequestService->show($id),
                message: 'Service request fetched successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to fetch service request');
        }
    }

    /** DataTables endpoint for service requests. */
    public function datatable()
    {
        try {
            return $this->serviceRequestService->datatable();
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to fetch service request data');
        }
    }

    /** Store a newly created service request. */
    public function store(StoreServiceRequestRequest $request)
    {
        try {
            return $this->apiResponse(
                data: $this->serviceRequestService->create($request->validated()),
                message: 'Service request submitted successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to submit service request');
        }
    }

    /** Update an existing submitted service request. */
    public function update(UpdateServiceRequestRequest $request, int $id)
    {
        try {
            return $this->apiResponse(
                data: $this->serviceRequestService->update($id, $request->validated()),
                message: 'Service request updated successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to update service request');
        }
    }

    /** Move a submitted request to in-progress. */
    public function moveToInProgress(MoveServiceRequestToInProgressRequest $request, int $id)
    {
        try {
            return $this->apiResponse(
                data: $this->serviceRequestService->moveToInProgress($id, $request->validated()['fulfillment_note'] ?? null),
                message: 'Service request moved to in progress successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to move service request to in progress');
        }
    }

    /** Complete an in-progress request. */
    public function complete(CompleteServiceRequestRequest $request, int $id)
    {
        try {
            return $this->apiResponse(
                data: $this->serviceRequestService->complete($id, $request->validated()['fulfillment_note'] ?? null),
                message: 'Service request completed successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to complete service request');
        }
    }

    /** Reject an in-progress request. */
    public function reject(RejectServiceRequestRequest $request, int $id)
    {
        try {
            return $this->apiResponse(
                data: $this->serviceRequestService->reject(
                    $id,
                    $request->validated()['rejection_reason'],
                    $request->validated()['fulfillment_note'] ?? null,
                ),
                message: 'Service request rejected successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to reject service request');
        }
    }
}
