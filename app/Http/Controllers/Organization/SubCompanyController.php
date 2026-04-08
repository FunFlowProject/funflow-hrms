<?php

declare(strict_types=1);

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\StoreSubCompanyRequest;
use App\Http\Requests\Organization\UpdateSubCompanyRequest;
use App\Services\Organization\SubCompanyService;
use Illuminate\View\View;
use Throwable;

class SubCompanyController extends Controller
{
    public function __construct(
        protected readonly SubCompanyService $subCompanyService,
    ) {}

    /** Display the sub-company management page. */
    public function index(): View
    {
        return view('sub-companies.index');
    }

    /** Retrieve all sub-companies. */
    public function all()
    {
        try {
            return $this->apiResponse(
                data: $this->subCompanyService->all(),
                message: 'Sub-companies fetched successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to fetch sub-companies');
        }
    }

    /** Retrieve active sub-companies. */
    public function active()
    {
        try {
            return $this->apiResponse(
                data: $this->subCompanyService->active(),
                message: 'Active sub-companies fetched successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to fetch active sub-companies');
        }
    }

    /** Retrieve sub-company stats. */
    public function stats()
    {
        try {
            return $this->apiResponse(
                data: $this->subCompanyService->stats(),
                message: 'Sub-company statistics fetched successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to fetch sub-company statistics');
        }
    }

    /** Display the specified sub-company. */
    public function show(int $id)
    {
        try {
            return $this->apiResponse(
                data: $this->subCompanyService->show($id),
                message: 'Sub-company fetched successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to fetch sub-company');
        }
    }

    /** DataTables endpoint for sub-companies. */
    public function datatable()
    {
        try {
            return $this->subCompanyService->datatable();
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to fetch sub-company data');
        }
    }

    /** Store a newly created sub-company. */
    public function store(StoreSubCompanyRequest $request)
    {
        try {
            return $this->apiResponse(
                data: $this->subCompanyService->create($request->validated()),
                message: 'Sub-company created successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to create sub-company');
        }
    }

    /** Update the specified sub-company. */
    public function update(UpdateSubCompanyRequest $request, int $id)
    {
        try {
            return $this->apiResponse(
                data: $this->subCompanyService->update($id, $request->validated()),
                message: 'Sub-company updated successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to update sub-company');
        }
    }

    /** Remove the specified sub-company. */
    public function destroy(int $id)
    {
        try {
            $this->subCompanyService->destroy($id);

            return $this->apiResponse(
                data: null,
                message: 'Sub-company deleted successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to delete sub-company');
        }
    }
}
