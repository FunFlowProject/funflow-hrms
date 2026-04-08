@props([
    'id',
    'title' => 'Search & Filters',
    'description' => 'Use filters to narrow down table results quickly.',
    'show' => false,
])

<div id="{{ $id }}" class="collapse {{ $show ? 'show' : '' }} mb-4">
<div {{ $attributes->merge(['class' => 'card border-0 shadow-sm rounded-4']) }}>
        <div class="card-body p-4">
            <div class="mb-3">
                <h6 class="fw-bold text-dark mb-1">{{ $title }}</h6>
                @if ($description)
                    <p class="text-secondary mb-0">{{ $description }}</p>
                @endif
            </div>

            {{ $slot }}
        </div>
    </div>
</div>