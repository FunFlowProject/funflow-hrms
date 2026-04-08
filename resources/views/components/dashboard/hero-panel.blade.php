@props([
    'kicker' => 'System Pulse',
    'title' => 'Welcome back',
    'description' => null,
    'illustration' => asset('assets/img/illustrations/man-with-laptop-light.png'),
    'illustrationAlt' => 'Dashboard illustration',
])

<div {{ $attributes->merge(['class' => 'card border-0 rounded-4 ui-dashboard-hero']) }}>
    <div class="card-body p-4 p-lg-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <span class="ui-dashboard-kicker">{{ $kicker }}</span>
                <h4 class="fw-bold text-dark mb-2">{{ $title }}</h4>

                @if ($description)
                    <p class="text-secondary mb-0">{{ $description }}</p>
                @endif

                @isset($highlights)
                    <div class="d-flex flex-wrap gap-2 mt-4">
                        {{ $highlights }}
                    </div>
                @endisset

                @isset($actions)
                    <div class="d-flex flex-wrap align-items-center gap-2 mt-4">
                        {{ $actions }}
                    </div>
                @endisset
            </div>

            <div class="col-lg-5">
                <div class="ui-dashboard-hero-illustration-wrap">
                    <img src="{{ $illustration }}" alt="{{ $illustrationAlt }}" class="ui-dashboard-hero-illustration" />
                </div>
            </div>
        </div>
    </div>
</div>