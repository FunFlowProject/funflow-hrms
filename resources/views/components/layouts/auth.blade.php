@props([
    'title' => 'Sign In',
])

@php
    $assetsPath = rtrim(asset('assets'), '/') . '/';
@endphp

<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="light-style customizer-hide"
    dir="ltr"
    data-theme="theme-default"
    data-scrollbar-tone="primary"
    data-assets-path="{{ $assetsPath }}"
>
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />

        <title>{{ $title }} | {{ config('app.name', 'Funflow HRMS') }}</title>
        <meta name="description" content="{{ config('app.name', 'Funflow HRMS') }} authentication" />

        <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @stack('head')
    </head>
    <body class="auth-page">
        <main class="container-xxl">
            <div class="authentication-wrapper authentication-basic container-p-y">
                <div class="authentication-inner">
                    {{ $slot }}
                </div>
            </div>
        </main>

        @stack('scripts')
    </body>
</html>