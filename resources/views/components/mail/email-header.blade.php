@props([
    'title' => config('app.name', 'Funflow HRMS'),
    'subtitle' => 'Employee Onboarding Notification',
    'fullLogoSrc' => null,
    'iconLogoSrc' => null,
])

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:linear-gradient(120deg,#1f3d5c 0%,#275d7b 65%,#2f7d9f 100%);">
    <tr>
        <td style="padding:20px 24px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>

                                               <td align="left" style="vertical-align:middle;">
                        <img src="{{ $fullLogoSrc }}" alt="{{ $title }}" style="height:36px;width:auto;display:block;">
                    </td>
                    <td align="right" style="vertical-align:middle;">
                        <img src="{{ $iconLogoSrc }}" alt="{{ $title }} icon" style="height:30px;width:auto;display:block;opacity:.95;">
                    </td>
                </tr>
            </table>
            <div style="margin-top:14px;color:#e6f2ff;">
                <div style="font-size:18px;font-weight:700;letter-spacing:.2px;">{{ $title }}</div>
                <div style="font-size:12px;opacity:.9;">{{ $subtitle }}</div>
            </div>
        </td>
    </tr>
</table>