<x-mail.email-layout :subject="$subject" :mail-message="isset($message) ? $message : null">

    {{-- ============================================================
    Employee Profile Created — Notification Email
    Variables:
    - $employee : Employee model (full_name, email)
    - $statusLabel : Human-readable status string
    - $assignmentSnapshot : Array of assignment rows (optional)
    - $initialPassword : Plaintext temp password (optional)
    - $employeeUsername : Login username (optional)
    - $note : Admin note (optional)
    ============================================================ --}}


    {{-- ══════════════════════════════════════════
    1. GREETING
    ══════════════════════════════════════════ --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 28px;">
        <tr>
            <td>
                <h1 style="
                    margin: 0 0 8px;
                    font-family: 'Inter', Arial, sans-serif;
                    font-size: 20px;
                    font-weight: 700;
                    color: #0f172a;
                    line-height: 1.3;
                ">Welcome to {{ config('app.name', 'Funflow HRMS') }},<br>{{ $employee->full_name }}!</h1>
                <p style="
                    margin: 0;
                    font-family: 'Inter', Arial, sans-serif;
                    font-size: 14px;
                    color: #64748b;
                    line-height: 1.6;
                ">
                    Your employee profile has been created and your account is ready. Below you'll find your profile
                    details and everything you need to get started.
                </p>
            </td>
        </tr>
    </table>


    {{-- ══════════════════════════════════════════
    2. LOGIN CREDENTIALS (shown first — most actionable)
    ══════════════════════════════════════════ --}}
    @if (filled($initialPassword))
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="
                    background-color: #fefce8;
                    border: 1px solid #fde047;
                    border-radius: 8px;
                    margin-bottom: 24px;
                ">
            <tr>
                <td style="padding: 20px;">

                    {{-- Section label --}}
                    <p style="
                            margin: 0 0 16px;
                            font-family: 'Inter', Arial, sans-serif;
                            font-size: 11px;
                            font-weight: 700;
                            color: #854d0e;
                            text-transform: uppercase;
                            letter-spacing: 0.8px;
                        ">Your Login Credentials</p>

                    {{-- Two-column credential grid --}}
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 16px;">
                        <tr>

                            {{-- Username --}}
                            <td width="48%" valign="top" style="padding-right: 8px;">
                                <p style="
                                        margin: 0 0 6px;
                                        font-family: 'Inter', Arial, sans-serif;
                                        font-size: 11px;
                                        font-weight: 600;
                                        color: #a16207;
                                        text-transform: uppercase;
                                        letter-spacing: 0.5px;
                                    ">Username</p>
                                <code style="
                                        display: block;
                                        font-family: ui-monospace, 'SFMono-Regular', Menlo, Monaco, Consolas, monospace;
                                        font-size: 14px;
                                        color: #0f172a;
                                        background-color: #ffffff;
                                        padding: 8px 10px;
                                        border-radius: 6px;
                                        border: 1px solid #fde047;
                                        word-break: break-all;
                                    ">{{ $employeeUsername ?: '—' }}</code>
                            </td>

                            {{-- Temporary Password --}}
                            <td width="4%">&nbsp;</td>
                            <td width="48%" valign="top">
                                <p style="
                                        margin: 0 0 6px;
                                        font-family: 'Inter', Arial, sans-serif;
                                        font-size: 11px;
                                        font-weight: 600;
                                        color: #a16207;
                                        text-transform: uppercase;
                                        letter-spacing: 0.5px;
                                    ">Temporary Password</p>
                                <code style="
                                        display: block;
                                        font-family: ui-monospace, 'SFMono-Regular', Menlo, Monaco, Consolas, monospace;
                                        font-size: 14px;
                                        color: #0f172a;
                                        background-color: #ffffff;
                                        padding: 8px 10px;
                                        border-radius: 6px;
                                        border: 1px solid #fde047;
                                        word-break: break-all;
                                    ">{{ $initialPassword }}</code>
                            </td>

                        </tr>
                    </table>

                    {{-- Sign-in hint --}}
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="
                                    background-color: #fef9c3;
                                    border-radius: 6px;
                                    padding: 10px 14px;
                                    border: 1px solid #fde047;
                                ">
                                <p style="
                                        margin: 0 0 4px;
                                        font-family: 'Inter', Arial, sans-serif;
                                        font-size: 13px;
                                        color: #713f12;
                                        font-weight: 600;
                                        line-height: 1.5;
                                    ">You can sign in using either your <strong>username</strong> or your <strong>email
                                        address</strong>.</p>
                                <p style="
                                        margin: 0;
                                        font-family: 'Inter', Arial, sans-serif;
                                        font-size: 12px;
                                        color: #a16207;
                                        font-style: italic;
                                    ">Please change this temporary password immediately after your first login.</p>
                            </td>
                        </tr>
                    </table>

                </td>
            </tr>
        </table>
    @endif


    {{-- ══════════════════════════════════════════
    3. PROFILE SUMMARY
    ══════════════════════════════════════════ --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 24px;
        ">
        <tr>
            <td style="padding: 16px 20px;">

                <p style="
                    margin: 0 0 14px;
                    font-family: 'Inter', Arial, sans-serif;
                    font-size: 11px;
                    font-weight: 700;
                    color: #94a3b8;
                    text-transform: uppercase;
                    letter-spacing: 0.8px;
                ">Profile Details</p>

                {{-- Full Name --}}
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 12px;">
                    <tr>
                        <td width="110" valign="top">
                            <span
                                style="font-family: 'Inter', Arial, sans-serif; font-size: 12px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.4px;">Full
                                Name</span>
                        </td>
                        <td valign="top">
                            <span
                                style="font-family: 'Inter', Arial, sans-serif; font-size: 14px; color: #0f172a; font-weight: 600;">{{ $employee->full_name }}</span>
                        </td>
                    </tr>
                </table>

                {{-- Divider --}}
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 12px;">
                    <tr>
                        <td style="border-top: 1px solid #e2e8f0; line-height: 0; font-size: 0;">&nbsp;</td>
                    </tr>
                </table>

                {{-- Email --}}
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 12px;">
                    <tr>
                        <td width="110" valign="top">
                            <span
                                style="font-family: 'Inter', Arial, sans-serif; font-size: 12px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.4px;">Email</span>
                        </td>
                        <td valign="top">
                            <span
                                style="font-family: 'Inter', Arial, sans-serif; font-size: 14px; color: #334155;">{{ $employee->email }}</span>
                        </td>
                    </tr>
                </table>

                {{-- Divider --}}
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 12px;">
                    <tr>
                        <td style="border-top: 1px solid #e2e8f0; line-height: 0; font-size: 0;">&nbsp;</td>
                    </tr>
                </table>

                {{-- Status --}}
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td width="110" valign="top">
                            <span
                                style="font-family: 'Inter', Arial, sans-serif; font-size: 12px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.4px;">Status</span>
                        </td>
                        <td valign="top">
                            <span style="
                                display: inline-block;
                                font-family: 'Inter', Arial, sans-serif;
                                font-size: 12px;
                                font-weight: 600;
                                color: #166534;
                                background-color: #dcfce7;
                                padding: 2px 10px;
                                border-radius: 20px;
                                border: 1px solid #bbf7d0;
                            ">{{ $statusLabel }}</span>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>


    {{-- ══════════════════════════════════════════
    4. ASSIGNMENTS
    ══════════════════════════════════════════ --}}
    @if (!empty($assignmentSnapshot))
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 24px;">
            <tr>
                <td>
                    <p style="
                            margin: 0 0 10px;
                            font-family: 'Inter', Arial, sans-serif;
                            font-size: 11px;
                            font-weight: 700;
                            color: #94a3b8;
                            text-transform: uppercase;
                            letter-spacing: 0.8px;
                        ">Assignments</p>

                    @foreach ($assignmentSnapshot as $assignment)
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 6px;">
                            <tr>
                                <td style="
                                            padding: 11px 14px;
                                            border-left: 3px solid #94a3b8;
                                            background-color: #f8fafc;
                                            border-radius: 0 6px 6px 0;
                                            font-family: 'Inter', Arial, sans-serif;
                                            font-size: 13px;
                                            color: #334155;
                                            line-height: 1.5;
                                        ">
                                    <strong style="color: #0f172a;">{{ $assignment['sub_company_name'] ?: 'Company' }}</strong>
                                    <span style="color: #cbd5e1;">&nbsp;&middot;&nbsp;</span>
                                    {{ $assignment['squad_name'] ?: 'Squad' }}
                                    <span style="color: #cbd5e1;">&nbsp;&middot;&nbsp;</span>
                                    <span
                                        style="color: #64748b; font-style: italic;">{{ $assignment['hierarchy_title'] ?: 'Role' }}</span>
                                </td>
                            </tr>
                        </table>
                    @endforeach

                </td>
            </tr>
        </table>
    @endif


    {{-- ══════════════════════════════════════════
    5. ADMIN NOTE
    ══════════════════════════════════════════ --}}
    @if (filled($note))
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 24px;">
            <tr>
                <td style="
                        border-left: 3px solid #38bdf8;
                        background-color: #f0f9ff;
                        padding: 12px 16px;
                        border-radius: 0 6px 6px 0;
                    ">
                    <p style="
                            margin: 0 0 4px;
                            font-family: 'Inter', Arial, sans-serif;
                            font-size: 11px;
                            font-weight: 600;
                            color: #0284c7;
                            text-transform: uppercase;
                            letter-spacing: 0.5px;
                        ">Note from Admin</p>
                    <p style="
                            margin: 0;
                            font-family: 'Inter', Arial, sans-serif;
                            font-size: 14px;
                            color: #0369a1;
                            font-style: italic;
                            line-height: 1.6;
                        ">&ldquo;{{ $note }}&rdquo;</p>
                </td>
            </tr>
        </table>
    @endif


    {{-- ══════════════════════════════════════════
    6. FOOTER SIGN-OFF
    ══════════════════════════════════════════ --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td style="border-top: 1px solid #e2e8f0; padding-top: 20px;">
                <p style="
                    margin: 0;
                    font-family: 'Inter', Arial, sans-serif;
                    font-size: 13px;
                    color: #94a3b8;
                    line-height: 1.7;
                ">
                    Regards,<br>
                    <strong style="color: #64748b;">{{ config('app.name', 'Funflow HRMS') }}</strong>
                </p>
            </td>
        </tr>
    </table>

</x-mail.email-layout>