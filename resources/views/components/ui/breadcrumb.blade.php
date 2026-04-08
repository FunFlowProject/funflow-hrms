@props([
    'variant' => 'navbar',
])
@php
    $routeName = request()->route()?->getName() ?? 'dashboard';
    $segments = array_values(array_filter(
        explode('.', $routeName),
        static fn (string $segment): bool => $segment !== '' && $segment !== 'index'
    ));
    $isDashboardOnly = count($segments) === 1 && $segments[0] === 'dashboard';
    $wrapperClass = $variant === 'page' ? 'ui-page-breadcrumb' : 'navbar-breadcrumb-nav ms-2 ms-sm-3';
@endphp

<nav {{ $attributes->merge(['class' => $wrapperClass]) }} aria-label="breadcrumb">
<ol class="breadcrumb mb-0">
        @if ($isDashboardOnly)
            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
        @else
        <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}">Dashboard</a>
        </li>
            @foreach ($segments as $segment)
                @php
                    $segmentLabel = \Illuminate\Support\Str::of($segment)->replace(['-', '_'], ' ')->title()->toString();
                @endphp
                @if ($loop->last)
                    <li class="breadcrumb-item active" aria-current="page">{{ $segmentLabel }}</li>
                @elseif ($segment !== 'dashboard')
                    <li class="breadcrumb-item">{{ $segmentLabel }}</li>
                @endif
            @endforeach
    @endif
    </ol>
</nav>
