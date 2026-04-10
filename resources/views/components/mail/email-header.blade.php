@props([
    'title'       => config('app.name', 'Funflow HRMS'),
    'subtitle'    => null,
    'fullLogoSrc' => null,
    'iconLogoSrc' => null,
])


<table
    role="presentation"
    width="100%"
    cellpadding="0"
    cellspacing="0"
    style="
        background-color: #ffffff;
        border-bottom: 1px solid #e2e8f0;
    "
>
    <tr>
        <td style="padding: 20px 24px;">

            {{-- Inner layout: Logo | Datetime | Icon --}}
            <table
                role="presentation"
                width="100%"
                cellpadding="0"
                cellspacing="0"
            >
                <tr>

                    {{-- ── LEFT: Full Logo ── --}}
                    <td
                        width="33%"
                        align="left"
                        valign="middle"
                    >
                        @if ($fullLogoSrc)
                            <img
                                src="{{ $fullLogoSrc }}"
                                alt="{{ $title }}"
                                width="auto"
                                height="32"
                                style="
                                    display: block;
                                    height: 32px;
                                    width: auto;
                                    border: 0;
                                "
                            >
                        @endif
                    </td>

                    {{-- ── CENTER: Day & Time ── --}}
                    <td
                        width="34%"
                        align="center"
                        valign="middle"
                        style="padding: 0 12px;"
                    >
                        {{-- Day name --}}
                        <p style="
                            margin: 0;
                            font-family: 'Inter', Arial, sans-serif;
                            font-size: 13px;
                            font-weight: 700;
                            color: #0f172a;
                            text-transform: uppercase;
                            letter-spacing: 1.5px;
                            line-height: 1;
                        ">
                            {{ now()->format('l') }}
                        </p>

                        {{-- Time --}}
                        <p style="
                            margin: 4px 0 0;
                            font-family: 'Inter', Arial, sans-serif;
                            font-size: 11px;
                            font-weight: 500;
                            color: #94a3b8;
                            line-height: 1;
                        ">
                            {{ now()->format('h:i A') }}
                        </p>

                        {{-- Optional subtitle --}}
                        @if ($subtitle)
                            <p style="
                                margin: 6px 0 0;
                                font-family: 'Inter', Arial, sans-serif;
                                font-size: 10px;
                                font-weight: 400;
                                color: #cbd5e1;
                                line-height: 1;
                            ">
                                {{ $subtitle }}
                            </p>
                        @endif
                    </td>

                    {{-- ── RIGHT: Icon / Monogram Logo ── --}}
                    <td
                        width="33%"
                        align="right"
                        valign="middle"
                    >
                        @if ($iconLogoSrc)
                            <img
                                src="{{ $iconLogoSrc }}"
                                alt="{{ $title }} icon"
                                width="auto"
                                height="28"
                                style="
                                    display: block;
                                    height: 28px;
                                    width: auto;
                                    border: 0;
                                    opacity: 0.80;
                                    margin-left: auto;
                                "
                            >
                        @endif
                    </td>

                </tr>
            </table>

        </td>
    </tr>
</table>