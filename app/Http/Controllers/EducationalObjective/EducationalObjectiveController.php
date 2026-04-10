<?php

declare(strict_types=1);

namespace App\Http\Controllers\EducationalObjective;

use App\Http\Controllers\Controller;
use App\Http\Requests\EducationalObjective\StoreEducationalObjectiveRequest;
use App\Http\Requests\EducationalObjective\UpdateObjectiveProgressRequest;
use App\Services\EducationalObjective\EducationalObjectiveService;
use App\Services\EducationalObjective\EmployeeEducationalObjectiveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Throwable;

class EducationalObjectiveController extends Controller
{
    public function __construct(
        protected readonly EducationalObjectiveService $objectiveService,
        protected readonly EmployeeEducationalObjectiveService $employeeObjectiveService,
    ) {}

    /*
    |--------------------------------------------------------------------------
    | MANAGEMENT ENDPOINTS
    |--------------------------------------------------------------------------
    */

    public function index(): View
    {
        return view('educational-objectives.index');
    }

    public function stats()
    {
        try {
            return $this->apiResponse(
                data: $this->objectiveService->stats(),
                message: 'Educational objectives stats fetched successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to fetch educational objective stats');
        }
    }

    public function datatable()
    {
        try {
            return $this->objectiveService->datatable();
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to fetch educational objectives data');
        }
    }

    public function store(StoreEducationalObjectiveRequest $request)
    {
        try {
            return $this->apiResponse(
                data: $this->objectiveService->create(
                    $request->validated(),
                    $request->file('file')
                ),
                message: 'Educational objective created successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to create educational objective');
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->objectiveService->destroy($id);

            return $this->apiResponse(
                data: null,
                message: 'Educational objective deleted successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to delete educational objective');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | EMPLOYEE ENDPOINTS
    |--------------------------------------------------------------------------
    */

    public function myObjectives(): View
    {
        return view('educational-objectives.my-objectives');
    }

    public function myObjectivesList(Request $request)
    {
        try {
            $filters = $request->only(['search', 'status']);
            return $this->apiResponse(
                data: $this->employeeObjectiveService->getObjectivesForUser(Auth::user(), $filters),
                message: 'My objectives fetched successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to fetch my educational objectives');
        }
    }

    public function myObjectivesStats()
    {
        try {
            $documents = $this->employeeObjectiveService->getObjectivesForUser(Auth::user(), []);
            
            $stats = [
                'total' => count($documents),
                'not_started' => collect($documents)->where('employee_status.status', 'not_started')->count(),
                'in_progress' => collect($documents)->where('employee_status.status', 'in_progress')->count(),
                'completed' => collect($documents)->where('employee_status.status', 'completed')->count(),
            ];

            return $this->apiResponse(
                data: $stats,
                message: 'My educational objective statistics fetched successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to fetch my educational objective statistics');
        }
    }

    public function updateProgress(UpdateObjectiveProgressRequest $request, int $id)
    {
        try {
            $this->employeeObjectiveService->updateProgress(Auth::user(), $id, $request->validated('progress_notes'));

            return $this->apiResponse(
                data: null,
                message: 'Progress notes updated successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to update objective progress');
        }
    }

    public function markCompleted(int $id)
    {
        try {
            $this->employeeObjectiveService->markCompleted(Auth::user(), $id);

            return $this->apiResponse(
                data: null,
                message: 'Objective marked as completed.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to mark objective as completed');
        }
    }
}
