<?php

declare(strict_types=1);

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Services\Employee\EmployeeService;
use Illuminate\View\View;
use Throwable;


class EmployeeController extends Controller
{
    public function __construct(
        protected readonly EmployeeService $employeeService,
    ) {}

    /*
    |--------------------------------------------------------------------------
    | VIEW METHODS
    |--------------------------------------------------------------------------
    */

    /** Display the employee management page. */
    public function index(): View
    {
        return view('employees.index');
    }

    /*
    |--------------------------------------------------------------------------
    | READ METHODS
    |--------------------------------------------------------------------------
    */

    /** Display the specified employee. */
    public function show(int $id)
    {
        try {
            $employee = $this->employeeService->show($id);
            return $this->apiResponse(
                data: $employee,
                message: 'Employee fetched successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to fetch employee');
        }
    }

    /** Retrieve all employees. */
    public function all()
    {
        try {
            $employees = $this->employeeService->all();
            return $this->apiResponse(
                data: $employees,
                message: 'Employees fetched successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to fetch employees');
        }
    }

    /** Retrieve active employees. */
    public function active()
    {
        try {
            $employees = $this->employeeService->active();
            return $this->apiResponse(
                data: $employees,
                message: 'Active employees fetched successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to fetch active employees');
        }
    }

    /** Get employee statistics. */
    public function stats()
    {
        try {
            $stats = $this->employeeService->stats();
            return $this->apiResponse(
                data: $stats,
                message: 'Employee statistics fetched successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to fetch employee statistics');
        }
    }

    /** Get employee form options. */
    public function options()
    {
        try {
            $options = $this->employeeService->options();
            return $this->apiResponse(
                data: $options,
                message: 'Employee options fetched successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to fetch employee options');
        }
    }

    /** Backward-compatible meta endpoint alias for options. */
    public function meta()
    {
        return $this->options();
    }



    /** DataTables endpoint for employees. */
    public function datatable()
    {
        try {
            return $this->employeeService->datatable();
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to fetch employee data');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | WRITE METHODS
    |--------------------------------------------------------------------------
    */

    /** Store a newly created employee. */
    public function store(StoreEmployeeRequest $request)
    {
        try {
            $data = $request->validated();
            $employee = $this->employeeService->create($data);
            return $this->apiResponse(
                data: $employee,
                message: 'Employee created successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to create employee');
        }
    }

    /** Update the specified employee. */
    public function update(UpdateEmployeeRequest $request, int $id)
    {
        try {
            $data = $request->validated();

            $employee = $this->employeeService->update($id, $data);

            return $this->apiResponse(
                data: $employee,
                message: 'Employee updated successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to update employee');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE METHODS
    |--------------------------------------------------------------------------
    */

    /** Remove the specified employee. */
    public function destroy(int $id)
    {
        try {
            $this->employeeService->destroy($id);
            return $this->apiResponse(
                data: null,
                message: 'Employee deleted successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to delete employee');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ACTION METHODS
    |--------------------------------------------------------------------------
    */

    /** Move a pending employee to onboarding. */
    public function moveToOnboarding(int $id)
    {
        try {
            $employee = $this->employeeService->moveToOnboarding($id);

            return $this->apiResponse(
                data: $employee,
                message: 'Employee moved to onboarding successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to move employee to onboarding');
        }
    }

    /** Confirm an onboarding employee as joined. */
    public function confirmJoin(int $id)
    {
        try {
            $employee = $this->employeeService->confirmJoin($id);

            return $this->apiResponse(
                data: $employee,
                message: 'Employee join confirmed successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to confirm employee join');
        }
    }
}