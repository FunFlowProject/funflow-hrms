@props([
    'label',
    'valueId',
    'value' => 0,
    'subtitle' => null,
    'icon' => 'bx bx-bar-chart-alt-2',
    'tone' => 'primary',
    'loading' => false,
    'updatedClass' => 'summary-last-updated',
    'updatedText' => 'Updated --',
])
@php
    $valueClasses = 'ui-dashboard-stat-value fw-bold mb-1';
    $metaClasses = $updatedClass;

    if ($loading) {
        $valueClasses .= ' placeholder placeholder-wave d-inline-block rounded stats-value-loading';
        $metaClasses .= ' placeholder placeholder-wave d-inline-block rounded stats-updated-loading';
    }

    $displayValue = $loading ? ' ' : $value;
    $displayUpdatedText = $loading ? ' ' : $updatedText;
@endphp

<div {{ $attributes->merge(['class' => "card border-0 rounded-4 ui-dashboard-stat ui-dashboard-stat-{$tone} h-100"]) }}>
    <div class="card-body p-4">
    <div class="ui-dashboard-stat-head d-flex align-items-center justify-content-between mb-3">
            <span class="ui-dashboard-stat-label">{{ $label }}</span>
            <span class="ui-dashboard-stat-icon" aria-hidden="true">
                <i class="{{ $icon }}"></i>
            </span>
        </div>

        <h2 id="{{ $valueId }}" class="{{ trim($valueClasses) }}">{{ $displayValue }}</h2>

        @if ($subtitle)
            <p class="text-secondary small mb-0">{{ $subtitle }}</p>
        @endif

        <div class="ui-dashboard-stat-updated mt-3">
            <i class="bx bx-time-five"></i>
            <span class="{{ trim($metaClasses) }}">{{ $displayUpdatedText }}</span>
        </div>
    </div>
</div>