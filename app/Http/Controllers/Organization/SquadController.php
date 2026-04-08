<?php

declare(strict_types=1);

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\StoreSquadRequest;
use App\Http\Requests\Organization\UpdateSquadRequest;
use App\Services\Organization\SquadService;
use Illuminate\View\View;
use Throwable;

class SquadController extends Controller
{
    public function __construct(
        protected readonly SquadService $squadService,
    ) {}

    /** Display the squad management page. */
    public function index(): View
    {
        return view('squads.index');
    }

    /** Retrieve all squads. */
    public function all()
    {
        try {
            return $this->apiResponse(
                data: $this->squadService->all(),
                message: 'Squads fetched successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to fetch squads');
        }
    }

    /** Retrieve active squads. */
    public function active()
    {
        try {
            return $this->apiResponse(
                data: $this->squadService->active(),
                message: 'Active squads fetched successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to fetch active squads');
        }
    }

    /** Retrieve squad stats. */
    public function stats()
    {
        try {
            return $this->apiResponse(
                data: $this->squadService->stats(),
                message: 'Squad statistics fetched successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to fetch squad statistics');
        }
    }

    /** Display the specified squad. */
    public function show(int $id)
    {
        try {
            return $this->apiResponse(
                data: $this->squadService->show($id),
                message: 'Squad fetched successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to fetch squad');
        }
    }

    /** DataTables endpoint for squads. */
    public function datatable()
    {
        try {
            return $this->squadService->datatable();
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to fetch squad data');
        }
    }

    /** Store a newly created squad. */
    public function store(StoreSquadRequest $request)
    {
        try {
            return $this->apiResponse(
                data: $this->squadService->create($request->validated()),
                message: 'Squad created successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to create squad');
        }
    }

    /** Update the specified squad. */
    public function update(UpdateSquadRequest $request, int $id)
    {
        try {
            return $this->apiResponse(
                data: $this->squadService->update($id, $request->validated()),
                message: 'Squad updated successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to update squad');
        }
    }

    /** Remove the specified squad. */
    public function destroy(int $id)
    {
        try {
            $this->squadService->destroy($id);

            return $this->apiResponse(
                data: null,
                message: 'Squad deleted successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to delete squad');
        }
    }
}
