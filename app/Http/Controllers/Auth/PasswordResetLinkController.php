<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PasswordEmailRequest;
use App\Services\Auth\PasswordResetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function __construct(
        private readonly PasswordResetService $passwordResetService,
    ) {
    }

    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     */
    public function store(PasswordEmailRequest $request): RedirectResponse
    {
        $result = $this->passwordResetService->sendResetLink($request->only('email'));

        if ($result['success']) {
            return back()->with('status', $result['status']);
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => $result['status']]);
    }
}
