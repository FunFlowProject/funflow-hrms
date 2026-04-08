<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Dashboard\AdminDashboardController;
use App\Http\Controllers\Employee\EmployeeController;
use App\Http\Controllers\Organization\SquadController;
use App\Http\Controllers\Organization\SubCompanyController;
use App\Http\Controllers\ServiceRequest\ServiceCatalogController;
use App\Http\Controllers\ServiceRequest\ServiceRequestController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.attempt');
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

    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});
