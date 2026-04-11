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
                ">Account Details Updated</h1>
                
                <p style="
                    margin: 0 0 16px;
                    font-family: 'Inter', Arial, sans-serif;
                    font-size: 14px;
                    color: #64748b;
                    line-height: 1.6;
                ">
                    Hello {{ $user->full_name }},
                </p>

                <p style="
                    margin: 0 0 24px;
                    font-family: 'Inter', Arial, sans-serif;
                    font-size: 14px;
                    color: #64748b;
                    line-height: 1.6;
                ">
                    This is an automated notification to inform you that certain details of your <strong>{{ config('app.name') }}</strong> account have been updated.
                </p>

                <div style="
                    margin: 24px 0;
                    background-color: #ffffff;
                    border: 1px solid #e2e8f0;
                    border-radius: 8px;
                    overflow: hidden;
                ">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                        <thead>
                            <tr style="background-color: #f8fafc;">
                                <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 600; color: #64748b; border-bottom: 1px solid #e2e8f0; text-transform: uppercase;">Field</th>
                                <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 600; color: #64748b; border-bottom: 1px solid #e2e8f0; text-transform: uppercase;">Previous</th>
                                <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 600; color: #64748b; border-bottom: 1px solid #e2e8f0; text-transform: uppercase;">Updated To</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($changes as $field => $values)
                                <tr>
                                    <td style="padding: 12px 16px; font-size: 14px; color: #0f172a; font-weight: 500; border-bottom: @if(!$loop->last) 1px solid #f1f5f9 @else none @endif;">
                                        {{ $field }}
                                    </td>
                                    <td style="padding: 12px 16px; font-size: 13px; color: #ef4444; text-decoration: line-through; border-bottom: @if(!$loop->last) 1px solid #f1f5f9 @else none @endif;">
                                        {{ $values['old'] ?? '-' }}
                                    </td>
                                    <td style="padding: 12px 16px; font-size: 13px; color: #10b981; font-weight: 600; border-bottom: @if(!$loop->last) 1px solid #f1f5f9 @else none @endif;">
                                        {{ $values['new'] ?? '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <p style="
                    margin: 24px 0 16px;
                    font-family: 'Inter', Arial, sans-serif;
                    font-size: 14px;
                    color: #64748b;
                    line-height: 1.6;
                ">
                    If you did not authorize these changes, please contact your administrator or IT support immediately to secure your account.
                </p>

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top: 32px; margin-bottom: 24px;">
                    <tr>
                        <td align="center">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="border-radius: 6px; background-color: #0284c7;">
                                        <a href="{{ url('/login') }}" target="_blank" style="
                                            display: inline-block;
                                            padding: 12px 24px;
                                            font-family: 'Inter', Arial, sans-serif;
                                            font-size: 14px;
                                            color: #ffffff;
                                            text-decoration: none;
                                            border-radius: 6px;
                                            font-weight: 600;
                                        ">Login to Account</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</x-mail.email-layout>
