<x-mail.email-layout :subject="$subject" :mail-message="isset($message) ? $message : null">
    <p style="margin:0 0 14px;">Hello {{ $recipientName ?: 'Team' }},</p>

    <p style="margin:0 0 14px;">
        The status of your service request has been updated in {{ config('app.name', 'Funflow HRMS') }}.
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
        style="border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;margin:0 0 18px;">
        <tr>
            <td style="padding:12px 14px;background-color:#f8fafc;font-weight:700;color:#334155;" colspan="2">Status
                Change Summary</td>
        </tr>
        <tr>
            <td style="padding:10px 14px;width:180px;color:#475569;">Service</td>
            <td style="padding:10px 14px;color:#0f172a;font-weight:600;">{{ $serviceName }}</td>
        </tr>
        <tr>
            <td style="padding:10px 14px;width:180px;color:#475569;">Category</td>
            <td style="padding:10px 14px;color:#0f172a;">{{ $serviceCategory }}</td>
        </tr>
        <tr>
            <td style="padding:10px 14px;width:180px;color:#475569;">From Status</td>
            <td style="padding:10px 14px;color:#0f172a;">{{ $fromStatusLabel ?: '-' }}</td>
        </tr>
        <tr>
            <td style="padding:10px 14px;width:180px;color:#475569;">To Status</td>
            <td style="padding:10px 14px;color:#0f172a;font-weight:700;">{{ $toStatusLabel }}</td>
        </tr>
        <tr>
            <td style="padding:10px 14px;width:180px;color:#475569;">Action</td>
            <td style="padding:10px 14px;color:#0f172a;">{{ $actionLabel }}</td>
        </tr>
        @if (filled($actorName))
            <tr>
                <td style="padding:10px 14px;width:180px;color:#475569;">Handled By</td>
                <td style="padding:10px 14px;color:#0f172a;">{{ $actorName }}</td>
            </tr>
        @endif
    </table>

    @if (filled($rejectionReason))
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
            style="border:1px solid #fecaca;border-radius:10px;overflow:hidden;margin:0 0 18px;">
            <tr>
                <td style="padding:12px 14px;background-color:#fef2f2;font-weight:700;color:#b91c1c;">Rejection Reason</td>
            </tr>
            <tr>
                <td style="padding:10px 14px;color:#7f1d1d;">{{ $rejectionReason }}</td>
            </tr>
        </table>
    @endif

    @if (filled($fulfillmentNote))
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
            style="border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;margin:0 0 18px;">
            <tr>
                <td style="padding:12px 14px;background-color:#f8fafc;font-weight:700;color:#334155;">Fulfillment Note</td>
            </tr>
            <tr>
                <td style="padding:10px 14px;color:#0f172a;">{{ $fulfillmentNote }}</td>
            </tr>
        </table>
    @endif

    @if (filled($note))
        <p style="margin:0 0 18px;padding:10px 12px;border-left:4px solid #0284c7;background-color:#f0f9ff;color:#0c4a6e;">
            {{ $note }}
        </p>
    @endif

    <p style="margin:0;color:#475569;">Regards,<br>{{ config('app.name', 'Funflow HRMS') }} Team</p>
</x-mail.email-layout>