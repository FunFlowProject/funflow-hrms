@props([
    'title',
    'subtitle' => null,
    'icon' => 'bx bx-table',
    'bodyClass' => 'p-4 pt-3',
])

<div {{ $attributes->merge(['class' => 'card border-0 rounded-4 ui-data-table-card']) }}>
    <div class="card-header border-0 pb-2 pt-3 px-0">
        <div class="d-flex align-items-center gap-2 ui-data-table-title-wrap">
            <span class="ui-data-table-title-icon" aria-hidden="true">
                <i class="{{ $icon }}"></i>
            </span>
            <div>
                <h5 class="mb-0 fw-bold text-dark">{{ $title }}</h5>
                @if ($subtitle)
                    <small class="text-secondary">{{ $subtitle }}</small>
                @endif
            </div>
        </div>
    </div>

    <div class="card-body {{ $bodyClass }}">
        {{ $slot }}
    </div>
</div>
