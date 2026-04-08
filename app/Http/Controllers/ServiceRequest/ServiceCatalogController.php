<?php

declare(strict_types=1);

namespace App\Http\Controllers\ServiceRequest;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceRequest\StoreServiceCatalogRequest;
use App\Http\Requests\ServiceRequest\UpdateServiceCatalogRequest;
use App\Services\ServiceRequest\ServiceCatalogService;
use Illuminate\View\View;
use Throwable;

class ServiceCatalogController extends Controller
{
    public function __construct(
        protected readonly ServiceCatalogService $serviceCatalogService,
    ) {}

    /** Display the service catalog page. */
    public function index(): View
    {
        return view('service-catalog.index');
    }

    /** Retrieve all service catalog items. */
    public function all()
    {
        try {
            return $this->apiResponse(
                data: $this->serviceCatalogService->all(),
                message: 'Service catalog items fetched successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to fetch service catalog items');
        }
    }

    /** Retrieve active service catalog items. */
    public function active()
    {
        try {
            return $this->apiResponse(
                data: $this->serviceCatalogService->active(),
                message: 'Active service catalog items fetched successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to fetch active service catalog items');
        }
    }

    /** Retrieve service catalog stats. */
    public function stats()
    {
        try {
            return $this->apiResponse(
                data: $this->serviceCatalogService->stats(),
                message: 'Service catalog statistics fetched successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to fetch service catalog statistics');
        }
    }

    /** Display the specified service catalog item. */
    public function show(int $id)
    {
        try {
            return $this->apiResponse(
                data: $this->serviceCatalogService->show($id),
                message: 'Service catalog item fetched successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to fetch service catalog item');
        }
    }

    /** DataTables endpoint for service catalog items. */
    public function datatable()
    {
        try {
            return $this->serviceCatalogService->datatable();
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to fetch service catalog data');
        }
    }

    /** Store a newly created service catalog item. */
    public function store(StoreServiceCatalogRequest $request)
    {
        try {
            return $this->apiResponse(
                data: $this->serviceCatalogService->create($request->validated()),
                message: 'Service catalog item created successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to create service catalog item');
        }
    }

    /** Update the specified service catalog item. */
    public function update(UpdateServiceCatalogRequest $request, int $id)
    {
        try {
            return $this->apiResponse(
                data: $this->serviceCatalogService->update($id, $request->validated()),
                message: 'Service catalog item updated successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to update service catalog item');
        }
    }

    /** Remove the specified service catalog item. */
    public function destroy(int $id)
    {
        try {
            $this->serviceCatalogService->destroy($id);

            return $this->apiResponse(
                data: null,
                message: 'Service catalog item deleted successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to delete service catalog item');
        }
    }
}
