<x-mail.email-layout :subject="$subject" :mail-message="isset($message) ? $message : null">
    <div style="margin-bottom: 24px;">
        <h1 style="font-size: 18px; font-weight: 700; color: #0f172a; margin: 0 0 8px;">Employee Status Updated</h1>
        <p style="margin: 0; color: #64748b;">The status for <strong>{{ $employee->full_name }}</strong> has been
            updated.</p>
    </div>

    <div
        style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 24px;">
        <div style="margin-bottom: 12px; display: flex; align-items: baseline;">
            <span
                style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; width: 70px; display: inline-block;">Email</span>
            <span style="font-size: 14px; color: #334155;">{{ $employee->email }}</span>
        </div>
        <div style="display: flex; align-items: baseline;">
            <span
                style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; width: 70px; display: inline-block;">Status</span>
            <div style="font-size: 14px; color: #334155;">
                <span style="color: #64748b; text-decoration: line-through;">{{ $fromStatusLabel ?: '-' }}</span>
                <span style="margin: 0 8px; color: #94a3b8;">&rarr;</span>
                <span style="font-weight: 700; color: #0ea5e9;">{{ $toStatusLabel }}</span>
            </div>
        </div>
    </div>

    @if (!empty($assignmentSnapshot))
        <div style="margin-bottom: 24px;">
            <h2 style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; margin: 0 0 8px;">
                Current Assignments</h2>
            @foreach ($assignmentSnapshot as $assignment)
                <div
                    style="font-size: 13px; color: #334155; margin-bottom: 4px; padding-left: 12px; border-left: 2px solid #e2e8f0;">
                    <strong>{{ $assignment['sub_company_name'] ?: 'Company' }}</strong> &middot;
                    {{ $assignment['squad_name'] ?: 'Squad' }} &middot;
                    <span style="color: #64748b;">{{ $assignment['hierarchy_title'] ?: 'Role' }}</span>
                </div>
            @endforeach
        </div>
    @endif

    @if (filled($note))
        <div
            style="border-left: 3px solid #0ea5e9; background-color: #f0f9ff; padding: 12px 16px; border-radius: 4px; margin-bottom: 24px;">
            <p style="margin: 0; font-size: 14px; color: #0369a1; font-style: italic;">"{{ $note }}"</p>
        </div>
    @endif

    <div style="border-top: 1px solid #e2e8f0; padding-top: 24px;">
        <p style="margin: 0; font-size: 13px; color: #94a3b8;">
            Regards,<br><strong>{{ config('app.name', 'Funflow HRMS') }}</strong>
        </p>
    </div>
</x-mail.email-layout>