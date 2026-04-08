<x-mail.email-layout :subject="$subject" :mail-message="isset($message) ? $message : null">
    <p style="margin:0 0 14px;">Hello {{ $recipientName ?: 'Team' }},</p>

    <p style="margin:0 0 14px;">
        An employee status has been updated in {{ config('app.name', 'Funflow HRMS') }}.
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
        style="border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;margin:0 0 18px;">
        <tr>
            <td style="padding:12px 14px;background-color:#f8fafc;font-weight:700;color:#334155;" colspan="2">Status
                Change Summary</td>
        </tr>
        <tr>
            <td style="padding:10px 14px;width:180px;color:#475569;">Employee</td>
            <td style="padding:10px 14px;color:#0f172a;font-weight:600;">{{ $employee->full_name }}</td>
        </tr>
        <tr>
            <td style="padding:10px 14px;width:180px;color:#475569;">Email</td>
            <td style="padding:10px 14px;color:#0f172a;">{{ $employee->email }}</td>
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
                <td style="padding:10px 14px;width:180px;color:#475569;">Triggered By</td>
                <td style="padding:10px 14px;color:#0f172a;">{{ $actorName }}</td>
            </tr>
        @endif
    </table>

    @if (!empty($assignmentSnapshot))
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
            style="border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;margin:0 0 18px;">
            <tr>
                <td style="padding:12px 14px;background-color:#f8fafc;font-weight:700;color:#334155;" colspan="3">Current
                    Assignments</td>
            </tr>
            <tr>
                <td style="padding:10px 14px;font-size:12px;font-weight:700;color:#64748b;">Sub-Company</td>
                <td style="padding:10px 14px;font-size:12px;font-weight:700;color:#64748b;">Squad</td>
                <td style="padding:10px 14px;font-size:12px;font-weight:700;color:#64748b;">Hierarchy</td>
            </tr>
            @foreach ($assignmentSnapshot as $assignment)
                <tr>
                    <td style="padding:10px 14px;color:#0f172a;">{{ $assignment['sub_company_name'] ?: '-' }}</td>
                    <td style="padding:10px 14px;color:#0f172a;">{{ $assignment['squad_name'] ?: '-' }}</td>
                    <td style="padding:10px 14px;color:#0f172a;">{{ $assignment['hierarchy_title'] ?: '-' }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    @if (filled($note))
        <p style="margin:0 0 18px;padding:10px 12px;border-left:4px solid #0284c7;background-color:#f0f9ff;color:#0c4a6e;">
            {{ $note }}
        </p>
    @endif

    <p style="margin:0;color:#475569;">Regards,<br>{{ config('app.name', 'Funflow HRMS') }} Team</p>
</x-mail.email-layout>