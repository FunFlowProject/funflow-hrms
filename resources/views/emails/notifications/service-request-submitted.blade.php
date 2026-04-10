<x-mail.email-layout :subject="$subject" :mail-message="isset($message) ? $message : null">
    <div style="margin-bottom: 24px;">
        <h1 style="font-size: 18px; font-weight: 700; color: #0f172a; margin: 0 0 8px;">New Service Request</h1>
        <p style="margin: 0; color: #64748b;">A request has been submitted by <strong>{{ $serviceRequest->requester?->full_name ?? '-' }}</strong>.</p>
    </div>

    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 24px;">
        <div style="margin-bottom: 12px; display: flex; align-items: baseline;">
            <span style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; width: 80px; display: inline-block;">Service</span>
            <span style="font-size: 14px; font-weight: 600; color: #0f172a;">{{ $serviceName }}</span>
        </div>
        <div style="margin-bottom: 12px; display: flex; align-items: baseline;">
            <span style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; width: 80px; display: inline-block;">Category</span>
            <span style="font-size: 14px; color: #334155;">{{ $serviceCategory }}</span>
        </div>
        <div style="display: flex; align-items: baseline;">
            <span style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; width: 80px; display: inline-block;">Status</span>
            <span style="font-size: 14px; color: #334155;">{{ $statusLabel }}</span>
        </div>
    </div>

    @if (filled($serviceRequest->justification))
        <div style="margin-bottom: 24px;">
            <h2 style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; margin: 0 0 8px;">Justification</h2>
            <p style="margin: 0; font-size: 14px; color: #334155; line-height: 1.5;">{{ $serviceRequest->justification }}</p>
        </div>
    @endif

    @if (filled($note))
        <div style="border-left: 3px solid #0ea5e9; background-color: #f0f9ff; padding: 12px 16px; border-radius: 4px; margin-bottom: 24px;">
            <p style="margin: 0; font-size: 14px; color: #0369a1; font-style: italic;">"{{ $note }}"</p>
        </div>
    @endif

    <div style="border-top: 1px solid #e2e8f0; padding-top: 24px;">
        <p style="margin: 0; font-size: 13px; color: #94a3b8;">
            Regards,<br><strong>{{ config('app.name', 'Funflow HRMS') }}</strong>
        </p>
    </div>
</x-mail.email-layout>
