@props([
    'label',
    'valueId',
    'value' => 0,
    'valueClass' => 'text-dark',
    'loading' => false,
    'borderTone' => 'total',
    'iconTone' => 'primary',
    'updatedClass' => 'summary-last-updated',
    'updatedText' => 'Updated --',
])
@php
    $valueClasses = 'fw-bolder mb-0 stat-number ' . $valueClass;
    $metaClasses = $updatedClass;

    if ($loading) {
        $valueClasses .= ' placeholder placeholder-wave d-inline-block rounded stats-value-loading';
        $metaClasses .= ' placeholder placeholder-wave d-inline-block rounded stats-updated-loading';
    }

    $displayValue = $loading ? ' ' : $value;
    $displayUpdatedText = $loading ? ' ' : $updatedText;
@endphp

<div {{ $attributes->merge(['class' => "card border-0 shadow-sm rounded-4 ui-stat-card border-{$borderTone} h-100"]) }}>
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <span class="text-secondary fw-bold d-block mb-1 text-uppercase tracking-wide small">{{ $label }}</span>
                <h2 id="{{ $valueId }}" class="{{ trim($valueClasses) }}">{{ $displayValue }}</h2>
            </div>
            <div class="stat-icon-wrapper stat-icon-{{ $iconTone }}" aria-hidden="true">
                {{ $slot }}
            </div>
        </div>

        <div class="stat-meta mt-3">
            <i class="bx bx-time-five"></i>
            <span class="{{ trim($metaClasses) }}">{{ $displayUpdatedText }}</span>
        </div>
    </div>
</div>
