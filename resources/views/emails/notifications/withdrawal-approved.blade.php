<x-mail.email-layout :subject="$subject" :mail-message="isset($message) ? $message : null">
    <div style="margin-bottom: 24px;">
        <h1 style="font-size: 18px; font-weight: 700; color: #0f172a; margin: 0 0 8px;">Withdrawal Approved</h1>
        <p style="margin: 0; color: #64748b;">Your withdrawal request has been approved.</p>
    </div>

    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 24px;">
        <div style="margin-bottom: 12px; display: flex; align-items: baseline;">
            <span style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; width: 120px; display: inline-block;">Amount</span>
            <span style="font-size: 14px; font-weight: 600; color: #0f172a;">{{ $amount }}</span>
        </div>
        <div style="margin-bottom: 12px; display: flex; align-items: baseline;">
            <span style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; width: 120px; display: inline-block;">Balance After</span>
            <span style="font-size: 14px; color: #334155;">{{ $balanceAfter }}</span>
        </div>
        <div style="display: flex; align-items: baseline;">
            <span style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; width: 120px; display: inline-block;">Approved By</span>
            <span style="font-size: 14px; color: #334155;">{{ $adminName ?? '-' }}</span>
        </div>
    </div>

    <div style="margin-bottom: 24px;">
        <a href="{{ $requestUrl }}" style="display: inline-block; background-color: #2563eb; color: #ffffff; text-decoration: none; padding: 10px 16px; border-radius: 6px; font-weight: 600;">View Profit Dashboard</a>
    </div>

    <div style="border-top: 1px solid #e2e8f0; padding-top: 24px;">
        <p style="margin: 0; font-size: 13px; color: #94a3b8;">Regards,<br><strong>{{ config('app.name', 'Funflow HRMS') }}</strong></p>
    </div>
</x-mail.email-layout>
