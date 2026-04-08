@props([
    'title',
    'description' => null,
    'showBreadcrumb' => true,
])

<div {{ $attributes->merge(['class' => 'd-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 ui-page-header']) }}>
    <div class="ui-page-header-main">
        @if ($showBreadcrumb)
            <x-ui.breadcrumb variant="page" />
        @endif

        <h3 class="fw-bold text-dark mb-1">{{ $title }}</h3>

        @if ($description)
            <p class="text-secondary mb-0">{{ $description }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="mt-3 mt-md-0">
            {{ $actions }}
        </div>
    @endisset
</div>
