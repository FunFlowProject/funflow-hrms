@props([
    'subject' => config('app.name', 'Funflow HRMS'),
    'mailMessage' => null,
])

@php
    $fullLogoPath = public_path('assets/img/funflow-logo.png');
    $iconLogoPath = public_path('assets/img/ff-logo-icon.png');

    $fullLogoFallback = asset('assets/img/funflow-logo.png');
    $iconLogoFallback = asset('assets/img/ff-logo-icon.png');

    $canEmbed = is_object($mailMessage) && method_exists($mailMessage, 'embed');

    $fullLogoSrc = ($canEmbed && is_file($fullLogoPath))
        ? $mailMessage->embed($fullLogoPath)
        : $fullLogoFallback;

    $iconLogoSrc = ($canEmbed && is_file($iconLogoPath))
        ? $mailMessage->embed($iconLogoPath)
        : $iconLogoFallback;
@endphp

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subject }}</title>
 </head>
<body style="margin:0;padding:0;background-color:#f3f5f8;font-family:'Segoe UI',Tahoma,Arial,sans-serif;color:#1f2a37;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f5f8;padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:680px;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e5e7eb;">
                <tr>
                    <td style="padding:0;">
                        <x-mail.email-header
                            :full-logo-src="$fullLogoSrc"
                            :icon-logo-src="$iconLogoSrc"
                        />
                    </td>
                </tr>
                <tr>
                    <td style="padding:28px 32px 18px;line-height:1.6;font-size:14px;">
                        {{ $slot }}
                    </td>
                </tr>
                <tr>
                    <td style="padding:0;">
                        <x-mail.email-footer />
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>