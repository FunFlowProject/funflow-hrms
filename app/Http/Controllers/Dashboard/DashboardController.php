<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Organization\SubCompanyService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly SubCompanyService $subCompanyService,
    ) {
    }

    public function index(): View
    {
        return view('dashboard', [
            'subCompanies' => $this->subCompanyService->all(),
        ]);
    }
}
