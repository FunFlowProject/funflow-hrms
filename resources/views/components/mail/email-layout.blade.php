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
<body style="margin:0;padding:0;background-color:#f8fafc;font-family:'Inter', 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;color:#1e293b;-webkit-font-smoothing:antialiased;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8fafc;padding:32px 16px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:500px;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);border:1px solid #e2e8f0;">
                <tr>
                    <td style="padding:0;">
                        <x-mail.email-header
                            :full-logo-src="$fullLogoSrc"
                            :icon-logo-src="$iconLogoSrc"
                        />
                    </td>
                </tr>
                <tr>
                    <td style="padding:32px 32px 24px;line-height:1.5;font-size:14px;color:#334155;">
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