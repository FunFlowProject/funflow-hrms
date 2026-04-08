@props([
    'appName' => config('app.name', 'Funflow HRMS'),
])

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8fafc;border-top:1px solid #e5e7eb;">
    <tr>
        <td style="padding:16px 24px;font-size:12px;color:#6b7280;line-height:1.5;">
            <div style="margin-bottom:6px;">This is an automated notification from {{ $appName }}.</div>
            <div style="margin-bottom:6px;">Please do not reply directly to this email. Contact HR for assistance.</div>
            <div>&copy; {{ now()->year }} {{ $appName }}. All rights reserved.</div>
        </td>
    </tr>
</table>