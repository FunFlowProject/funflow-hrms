<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Enums\SystemRole;
use App\Http\Controllers\Controller;
use App\Services\Dashboard\AdminDashboardService;
use App\Services\Dashboard\EmployeeDashboardService;
use App\Services\Dashboard\HrDashboardService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Throwable;

class AdminDashboardController extends Controller
{
    public function __construct(
        private readonly AdminDashboardService $adminDashboardService,
        private readonly HrDashboardService $hrDashboardService,
        private readonly EmployeeDashboardService $employeeDashboardService,
    ) {}

    /**
     * Display role-based dashboard page.
     */
    public function index(): View
    {
        $role = SystemRole::safeFrom(Auth::user()?->system_role);

        return match ($role) {
            SystemRole::Hr => view('dashboard.hr-dashboard'),
            SystemRole::Employee => view('dashboard.employee-dashboard'),
            default => view('dashboard.admin-dashboard'),
        };
    }

    /**
     * Retrieve role-based dashboard stats.
     */
    public function stats()
    {
        try {
            $role = SystemRole::safeFrom(Auth::user()?->system_role);

            $stats = match ($role) {
                SystemRole::Hr => $this->hrDashboardService->stats(),
                SystemRole::Employee => $this->employeeDashboardService->stats(),
                default => $this->adminDashboardService->stats(),
            };

            return $this->apiResponse(
                data: $stats,
                message: 'Dashboard statistics fetched successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to fetch dashboard statistics');
        }
    }
}
