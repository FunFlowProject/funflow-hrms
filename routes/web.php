<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Dashboard\AdminDashboardController;
use App\Http\Controllers\Employee\EmployeeController;
use App\Http\Controllers\Organization\SquadController;
use App\Http\Controllers\Organization\SubCompanyController;
use App\Http\Controllers\ServiceRequest\ServiceCatalogController;
use App\Http\Controllers\ServiceRequest\ServiceRequestController;
use App\Http\Controllers\Document\DocumentController;
use App\Http\Controllers\EducationalObjective\EducationalObjectiveController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.attempt');

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [AdminDashboardController::class, 'stats'])->name('dashboard.stats');

    Route::prefix('employees')->name('employees.')->group(function () {
        Route::middleware('permission:employees.view')->group(function () {
            Route::get('/', [EmployeeController::class, 'index'])->name('index');
            Route::get('/meta', [EmployeeController::class, 'meta'])->name('meta');
            Route::get('/options', [EmployeeController::class, 'options'])->name('options');
            Route::get('/stats', [EmployeeController::class, 'stats'])->name('stats');
            Route::get('/datatable', [EmployeeController::class, 'datatable'])->name('datatable');
            Route::get('/{employee}', [EmployeeController::class, 'show'])->name('show');
        });

        Route::post('/', [EmployeeController::class, 'store'])
            ->middleware('permission:employees.create')
            ->name('store');

        Route::put('/{employee}', [EmployeeController::class, 'update'])
            ->middleware('permission:employees.update')
            ->name('update');

        Route::post('/{employee}/onboarding', [EmployeeController::class, 'moveToOnboarding'])
            ->middleware('permission:employees.move-to-onboarding')
            ->name('move-to-onboarding');

        Route::post('/{employee}/confirm-join', [EmployeeController::class, 'confirmJoin'])
            ->middleware('permission:employees.confirm-join')
            ->name('confirm-join');

        Route::delete('/{employee}', [EmployeeController::class, 'destroy'])
            ->middleware('permission:employees.destroy')
            ->name('destroy');
    });

    Route::prefix('sub-companies')->name('sub-companies.')->group(function () {
        Route::get('/', [SubCompanyController::class, 'index'])->name('index');
        Route::get('/all', [SubCompanyController::class, 'all'])->name('all');
        Route::get('/active', [SubCompanyController::class, 'active'])->name('active');
        Route::get('/stats', [SubCompanyController::class, 'stats'])->name('stats');
        Route::get('/datatable', [SubCompanyController::class, 'datatable'])->name('datatable');
        Route::get('/{subCompany}', [SubCompanyController::class, 'show'])->name('show');

        Route::post('/', [SubCompanyController::class, 'store'])->name('store');
        Route::put('/{subCompany}', [SubCompanyController::class, 'update'])->name('update');
        Route::delete('/{subCompany}', [SubCompanyController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('squads')->name('squads.')->group(function () {
        Route::get('/', [SquadController::class, 'index'])->name('index');
        Route::get('/all', [SquadController::class, 'all'])->name('all');
        Route::get('/active', [SquadController::class, 'active'])->name('active');
        Route::get('/stats', [SquadController::class, 'stats'])->name('stats');
        Route::get('/datatable', [SquadController::class, 'datatable'])->name('datatable');
        Route::get('/{squad}', [SquadController::class, 'show'])->name('show');

        Route::post('/', [SquadController::class, 'store'])->name('store');
        Route::put('/{squad}', [SquadController::class, 'update'])->name('update');
        Route::delete('/{squad}', [SquadController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('service-catalog')->name('service-catalog.')->group(function () {
        Route::middleware('permission:service-catalog.view')->group(function () {
            Route::get('/', [ServiceCatalogController::class, 'index'])->name('index');
            Route::get('/all', [ServiceCatalogController::class, 'all'])->name('all');
            Route::get('/active', [ServiceCatalogController::class, 'active'])->name('active');
            Route::get('/stats', [ServiceCatalogController::class, 'stats'])->name('stats');
            Route::get('/datatable', [ServiceCatalogController::class, 'datatable'])->name('datatable');
            Route::get('/{serviceCatalog}', [ServiceCatalogController::class, 'show'])->name('show');
        });

        Route::post('/', [ServiceCatalogController::class, 'store'])
            ->middleware('permission:service-catalog.create')
            ->name('store');

        Route::put('/{serviceCatalog}', [ServiceCatalogController::class, 'update'])
            ->middleware('permission:service-catalog.update')
            ->name('update');

        Route::delete('/{serviceCatalog}', [ServiceCatalogController::class, 'destroy'])
            ->middleware('permission:service-catalog.destroy')
            ->name('destroy');
    });

    Route::prefix('service-requests')->name('service-requests.')->group(function () {
        Route::get('/', [ServiceRequestController::class, 'index'])
            ->middleware('permission:service-requests.manage')
            ->name('index');

        Route::middleware('permission:service-requests.view')->group(function () {
            Route::get('/options', [ServiceRequestController::class, 'options'])->name('options');
            Route::get('/stats', [ServiceRequestController::class, 'stats'])->name('stats');
            Route::get('/datatable', [ServiceRequestController::class, 'datatable'])->name('datatable');
            Route::get('/{serviceRequest}', [ServiceRequestController::class, 'show'])->name('show');
        });

        Route::post('/', [ServiceRequestController::class, 'store'])
            ->middleware('permission:service-requests.create')
            ->name('store');

        Route::put('/{serviceRequest}', [ServiceRequestController::class, 'update'])
            ->middleware('permission:service-requests.create|service-requests.manage')
            ->name('update');

        Route::post('/{serviceRequest}/in-progress', [ServiceRequestController::class, 'moveToInProgress'])
            ->middleware('permission:service-requests.transition')
            ->name('move-to-in-progress');

        Route::post('/{serviceRequest}/complete', [ServiceRequestController::class, 'complete'])
            ->middleware('permission:service-requests.transition')
            ->name('complete');

        Route::post('/{serviceRequest}/reject', [ServiceRequestController::class, 'reject'])
            ->middleware('permission:service-requests.transition')
            ->name('reject');
    });

    Route::prefix('my-service-requests')->name('my-service-requests.')->group(function () {
        Route::get('/', [ServiceRequestController::class, 'employeeIndex'])
            ->middleware('permission:service-requests.view')
            ->name('index');
    });

    Route::prefix('documents')->name('documents.')->group(function () {
        Route::middleware('permission:documents.view')->group(function () {
            Route::get('/', [DocumentController::class, 'index'])->name('index');
            Route::get('/all', [DocumentController::class, 'all'])->name('all');
            Route::get('/stats', [DocumentController::class, 'stats'])->name('stats');
            Route::get('/datatable', [DocumentController::class, 'datatable'])->name('datatable');
            Route::get('/{document}', [DocumentController::class, 'show'])->name('show');
            Route::get('/{document}/status-info', [DocumentController::class, 'statusInfo'])->name('status-info');
        });

        Route::post('/', [DocumentController::class, 'store'])
            ->middleware('permission:documents.create')
            ->name('store');

        Route::post('/{document}', [DocumentController::class, 'update']) // Use POST since we are submitting files, Laravel form override
            ->middleware('permission:documents.update')
            ->name('update');

        Route::delete('/{document}', [DocumentController::class, 'destroy'])
            ->middleware('permission:documents.destroy')
            ->name('destroy');
    });

    Route::prefix('my-documents')->name('my-documents.')->group(function () {
        Route::get('/', [DocumentController::class, 'employeeIndex'])->name('index');
        Route::get('/list', [DocumentController::class, 'myDocumentsList'])->name('list');
        Route::get('/stats', [DocumentController::class, 'myDocumentsStats'])->name('stats');
        Route::post('/{document}/mark-viewed', [DocumentController::class, 'markViewed'])->name('mark-viewed');
        Route::post('/{document}/acknowledge', [DocumentController::class, 'acknowledge'])->name('acknowledge');
    });

    Route::prefix('educational-objectives')->name('educational-objectives.')->group(function () {
        Route::middleware('permission:educational-objectives.manage|educational-objectives.manage-all')->group(function () {
            Route::get('/', [EducationalObjectiveController::class, 'index'])->name('index');
            Route::get('/stats', [EducationalObjectiveController::class, 'stats'])->name('stats');
            Route::get('/datatable', [EducationalObjectiveController::class, 'datatable'])->name('datatable');
            Route::post('/', [EducationalObjectiveController::class, 'store'])->name('store');
            Route::delete('/{objective}', [EducationalObjectiveController::class, 'destroy'])->name('destroy');
        });
    });

    Route::prefix('my-educational-objectives')->name('my-objectives.')->group(function () {
        Route::middleware('permission:educational-objectives.view')->group(function () {
            Route::get('/', [EducationalObjectiveController::class, 'myObjectives'])->name('index');
            Route::get('/list', [EducationalObjectiveController::class, 'myObjectivesList'])->name('list');
            Route::get('/stats', [EducationalObjectiveController::class, 'myObjectivesStats'])->name('stats');
            Route::post('/{objective}/progress', [EducationalObjectiveController::class, 'updateProgress'])->name('update-progress');
            Route::post('/{objective}/complete', [EducationalObjectiveController::class, 'markCompleted'])->name('complete');
        });
    });

    Route::get('/my-work-logs', [\App\Http\Controllers\WorkLog\WorkLogController::class, 'myIndex'])
        ->middleware('permission:work-logs.my.view')
        ->name('my-work-logs.index');

    Route::prefix('work-logs')->name('work-logs.')->middleware('permission:work-logs.view-all|work-logs.my.view')->group(function () {
        Route::get('/', [\App\Http\Controllers\WorkLog\WorkLogController::class, 'index'])
            ->middleware('permission:work-logs.view-all')
            ->name('index');
        Route::get('/datatable', [\App\Http\Controllers\WorkLog\WorkLogController::class, 'datatable'])->name('datatable');
        Route::get('/{workLog}', [\App\Http\Controllers\WorkLog\WorkLogController::class, 'show'])->name('show');
        Route::post('/', [\App\Http\Controllers\WorkLog\WorkLogController::class, 'store'])->name('store');
        Route::put('/{workLog}', [\App\Http\Controllers\WorkLog\WorkLogController::class, 'update'])->name('update');
        Route::delete('/{workLog}', [\App\Http\Controllers\WorkLog\WorkLogController::class, 'destroy'])->name('destroy');
    });

    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});

// ─── EMAIL PREVIEWS (LOCAL ONLY) ─────────────────────────────────────────────
if (app()->environment('local')) {
    Route::prefix('emails/preview')->group(function () {
        Route::get('/sr-submitted', function () {
            $user = \App\Models\User::first() ?? new \App\Models\User(['full_name' => 'John Doe']);
            $sr = new \App\Models\ServiceRequest([
                'id' => 123,
                'justification' => 'Need a new high-performance laptop for development.',
                'service_name_snapshot' => 'MacBook Pro 16"',
                'service_category_snapshot' => 'Hardware',
            ]);
            $sr->setRelation('requester', $user);

            return view('emails.notifications.service-request-submitted', [
                'subject' => 'New Service Request',
                'serviceRequest' => $sr,
                'serviceName' => 'MacBook Pro 16"',
                'serviceCategory' => 'Hardware',
                'statusLabel' => 'Submitted',
                'note' => 'Please prioritize this request.',
            ]);
        });

        Route::get('/password-reset', function () {
            return view('emails.notifications.password-reset', [
                'subject' => \Illuminate\Support\Facades\Lang::get('Reset Password Notification'),
                'url' => url('/reset-password/sample-token-123?email=jane@example.com'),
                'count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire'),
                'user' => new \App\Models\User(['full_name' => 'Jane Doe', 'email' => 'jane@example.com']),
            ]);
        });

        Route::get('/sr-status', function () {
            $sr = new \App\Models\ServiceRequest([
                'id' => 123,
                'service_name_snapshot' => 'Conference Room Access',
            ]);
            return view('emails.notifications.service-request-status-changed', [
                'subject' => 'Request Status Updated',
                'serviceRequest' => $sr,
                'serviceName' => 'Conference Room Access',
                'fromStatusLabel' => 'Pending',
                'toStatusLabel' => 'Approved',
                'note' => 'Access granted for tomorrow.',
                'rejectionReason' => null,
                'fulfillmentNote' => 'Keys are at the reception.',
            ]);
        });

        Route::get('/employee-added', function () {
            $user = new \App\Models\User([
                'full_name' => 'Alice Smith',
                'email' => 'alice@funflow.org',
                'username' => 'alice.smith',
            ]);
            return view('emails.notifications.employee-added', [
                'subject' => 'Employee Profile Created',
                'employee' => $user,
                'employeeUsername' => 'alice.smith',
                'initialPassword' => 'Welcome2026!',
                'statusLabel' => 'Active',
                'assignmentSnapshot' => [
                    ['sub_company_name' => 'Funflow Tech', 'squad_name' => 'DevOps', 'hierarchy_title' => 'Senior Engineer']
                ],
                'note' => 'Welcome to the team!',
            ]);
        });

        Route::get('/employee-status', function () {
            $user = new \App\Models\User([
                'full_name' => 'Bob Wilson',
                'email' => 'bob@funflow.org',
            ]);
            return view('emails.notifications.employee-status-changed', [
                'subject' => 'Employee Status Updated',
                'employee' => $user,
                'fromStatusLabel' => 'Onboarding',
                'toStatusLabel' => 'Joined',
                'assignmentSnapshot' => [
                    ['sub_company_name' => 'Funflow HQ', 'squad_name' => 'Finance', 'hierarchy_title' => 'Manager']
                ],
                'note' => 'Probation period completed successfully.',
            ]);
        });
    });
}
