<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profit;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profit\DistributeProfitRequest;
use App\Http\Requests\Profit\RejectWithdrawalRequest;
use App\Services\Profit\ProfitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Throwable;

class ProfitController extends Controller
{
    public function __construct(
        protected readonly ProfitService $profitService,
    ) {}

    public function index(): View
    {
        return view('profit.index');
    }

    public function options()
    {
        try {
            return $this->apiResponse(
                data: $this->profitService->options(),
                message: 'Profit options fetched successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to fetch profit options');
        }
    }

    public function employees()
    {
        try {
            return $this->apiResponse(
                data: $this->profitService->employees(),
                message: 'Profit employee list fetched successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to fetch profit employees');
        }
    }

    public function withdrawalRequests()
    {
        try {
            return $this->profitService->withdrawalRequests();
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to fetch withdrawal requests');
        }
    }

    public function distribute(DistributeProfitRequest $request)
    {
        try {
            $this->profitService->distribute(
                $request->validated()['user_ids'],
                (float) $request->validated()['amount'],
                $request->user(),
            );

            return $this->apiResponse(
                data: null,
                message: 'Profit distributed successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to distribute profit');
        }
    }

    public function approveWithdrawal(int $id)
    {
        try {
            return $this->apiResponse(
                data: $this->profitService->approveWithdrawal($id, Auth::user()),
                message: 'Withdrawal request approved successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to approve withdrawal request');
        }
    }

    public function rejectWithdrawal(RejectWithdrawalRequest $request, int $id)
    {
        try {
            return $this->apiResponse(
                data: $this->profitService->rejectWithdrawal($id, $request->user(), $request->validated()['reason'] ?? null),
                message: 'Withdrawal request rejected successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to reject withdrawal request');
        }
    }
}
