@props([
    'title' => 'Recent Activity Stream',
    'description' => 'Activity timeline widgets can be connected here once module events are enabled.',
    'rows' => 4,
])

<div {{ $attributes->merge(['class' => 'card border-0 rounded-4 ui-dashboard-activity']) }}>
    <div class="card-header border-0 pb-0 p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="fw-bold text-dark mb-1">{{ $title }}</h5>
                <p class="text-secondary small mb-0">{{ $description }}</p>
            </div>
            <span class="badge bg-label-primary">Preview</span>
        </div>
    </div>

    <div class="card-body pt-3 p-4">
        <div class="ui-dashboard-activity-list">
            @for ($index = 0; $index < (int) $rows; $index++)
                <div class="ui-dashboard-activity-item">
                    <span class="ui-dashboard-activity-dot"></span>
                    <div class="ui-dashboard-activity-line placeholder placeholder-wave rounded"></div>
                </div>
            @endfor
        </div>
    </div>
</div>