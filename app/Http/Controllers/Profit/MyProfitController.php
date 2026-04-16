<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profit;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profit\RequestWithdrawalRequest;
use App\Services\Profit\ProfitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Throwable;

class MyProfitController extends Controller
{
    public function __construct(
        protected readonly ProfitService $profitService,
    ) {}

    public function index(): View|RedirectResponse
    {
        if (Auth::user()?->can('profit.view')) {
            return redirect()->route('profit.index');
        }

        return view('profit.my-index');
    }

    public function balance()
    {
        try {
            $user = Auth::user();

            return $this->apiResponse(
                data: $user ? $this->profitService->myBalance($user) : null,
                message: 'Profit balance fetched successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to fetch profit balance');
        }
    }

    public function transactions()
    {
        try {
            $user = Auth::user();

            return $user
                ? $this->profitService->myTransactions($user)
                : $this->reportError(new \RuntimeException('Unauthenticated user.'), 'Unable to fetch profit transactions');
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to fetch profit transactions');
        }
    }

    public function withdrawalRequests()
    {
        try {
            $user = Auth::user();

            return $user
                ? $this->profitService->myWithdrawalRequests($user)
                : $this->reportError(new \RuntimeException('Unauthenticated user.'), 'Unable to fetch withdrawal requests');
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to fetch withdrawal requests');
        }
    }

    public function requestWithdrawal(RequestWithdrawalRequest $request)
    {
        try {
            $user = $request->user();

            return $this->apiResponse(
                data: $user ? $this->profitService->requestWithdrawal($user, (float) $request->validated()['amount']) : null,
                message: 'Withdrawal request submitted successfully.',
            );
        } catch (Throwable $e) {
            return $this->reportError($e, 'Unable to submit withdrawal request');
        }
    }
}
