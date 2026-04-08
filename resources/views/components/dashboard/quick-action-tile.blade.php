@props([
    'title',
    'description',
    'icon' => 'bx bx-right-arrow-alt',
    'href' => '#',
    'tone' => 'primary',
])

<a href="{{ $href }}"
    {{ $attributes->merge(['class' => "card border-0 rounded-4 ui-dashboard-action ui-dashboard-action-{$tone} text-decoration-none h-100"]) }}>
    <div class="card-body p-3 p-lg-4">
        <div class="d-flex align-items-start justify-content-between gap-3">
            <span class="ui-dashboard-action-icon" aria-hidden="true">
                <i class="{{ $icon }}"></i>
            </span>
            <i class="bx bx-chevron-right ui-dashboard-action-arrow" aria-hidden="true"></i>
        </div>

        <h6 class="fw-bold text-dark mt-3 mb-1">{{ $title }}</h6>
        <p class="text-secondary small mb-0">{{ $description }}</p>
    </div>
</a>