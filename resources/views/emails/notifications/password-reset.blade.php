<x-mail.email-layout :subject="$subject" :mail-message="isset($message) ? $message : null">

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 24px;">
        <tr>
            <td>
                <h1 style="
                    margin: 0 0 8px;
                    font-family: 'Inter', Arial, sans-serif;
                    font-size: 20px;
                    font-weight: 700;
                    color: #0f172a;
                    line-height: 1.3;
                ">Hello!</h1>
                <p style="
                    margin: 0 0 16px;
                    font-family: 'Inter', Arial, sans-serif;
                    font-size: 14px;
                    color: #64748b;
                    line-height: 1.6;
                ">
                    You are receiving this email because we received a password reset request for your account.
                </p>

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 24px;">
                    <tr>
                        <td align="center">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="border-radius: 6px; background-color: #0284c7;">
                                        <a href="{{ $url }}" target="_blank" style="
                                            display: inline-block;
                                            padding: 12px 24px;
                                            font-family: 'Inter', Arial, sans-serif;
                                            font-size: 14px;
                                            color: #ffffff;
                                            text-decoration: none;
                                            border-radius: 6px;
                                            font-weight: 600;
                                        ">Reset Password</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <p style="
                    margin: 0 0 16px;
                    font-family: 'Inter', Arial, sans-serif;
                    font-size: 14px;
                    color: #64748b;
                    line-height: 1.6;
                ">
                    This password reset link will expire in {{ $count }} minutes.
                </p>
                <p style="
                    margin: 0;
                    font-family: 'Inter', Arial, sans-serif;
                    font-size: 14px;
                    color: #64748b;
                    line-height: 1.6;
                ">
                    If you did not request a password reset, no further action is required.
                </p>
            </td>
        </tr>
    </table>

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

    {{-- Subcopy --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding-top: 24px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="
                    border-top: 1px dashed #e2e8f0;
                    padding-top: 24px;
                ">
                    <tr>
                        <td>
                            <p style="
                                margin: 0;
                                font-family: 'Inter', Arial, sans-serif;
                                font-size: 12px;
                                color: #64748b;
                                line-height: 1.5;
                                word-break: break-all;
                            ">
                                If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:<br>
                                <a href="{{ $url }}" style="color: #0284c7; text-decoration: none;">{{ $url }}</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</x-mail.email-layout>
