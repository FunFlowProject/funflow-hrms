@props([
    'title' => 'Dashboard',
])

@php
    $assetsPath = rtrim(asset('assets'), '/') . '/';
@endphp

<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="light-style layout-menu-fixed"
    dir="ltr"
    data-theme="theme-default"
    data-scrollbar-tone="primary"
    data-assets-path="{{ $assetsPath }}"
    data-template="vertical-menu-template-free"
>
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />

        <title>{{ $title }} | {{ config('app.name', 'Funflow HRMS') }}</title>
        <meta name="description" content="{{ config('app.name', 'Funflow HRMS') }} dashboard" />

        <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @stack('head')
    </head>
    <body>
        <div class="layout-wrapper layout-content-navbar">
            <div class="layout-container">
                <x-ui.sidebar />

                <div class="layout-page">
                    <x-ui.navbar />

                    <div class="content-wrapper">
                        <main id="layout-page-content" class="container-xxl flex-grow-1 container-p-y page-scroll-container">
                            {{ $slot }}
                        </main>

                        <x-ui.footer />
                        <div class="content-backdrop fade"></div>
                    </div>
                </div>
            </div>

            <div class="layout-overlay layout-menu-toggle"></div>
        </div>

        @stack('scripts')
    </body>
</html>