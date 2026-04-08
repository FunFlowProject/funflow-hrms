@props([
    'title' => 'Workforce Distribution',
    'description' => 'Live status breakdown generated from employee records.',
    'rows' => [],
    'loading' => false,
    'updatedClass' => 'summary-last-updated',
    'updatedText' => 'Updated --',
    'emptyTitle' => 'No employee distribution yet',
    'emptyDescription' => 'Add employees to start tracking status ratios here.',
])

@php
    $resolvedRows = count($rows)
        ? $rows
        : [
            ['key' => 'joined', 'label' => 'Joined', 'tone' => 'success'],
            ['key' => 'pending', 'label' => 'Pending', 'tone' => 'warning'],
            ['key' => 'onboarding', 'label' => 'Onboarding', 'tone' => 'info'],
            ['key' => 'terminated', 'label' => 'Terminated', 'tone' => 'danger'],
        ];

    $updatedClasses = $updatedClass;
    if ($loading) {
        $updatedClasses .= ' placeholder placeholder-wave d-inline-block rounded stats-updated-loading';
    }

    $displayUpdatedText = $loading ? ' ' : $updatedText;
@endphp

<div {{ $attributes->merge(['class' => 'card border-0 rounded-4 ui-dashboard-progress-group h-100']) }}>
    <div class="card-header border-0 pb-0 p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
                <h5 class="fw-bold text-dark mb-1">{{ $title }}</h5>
                <p class="text-secondary small mb-0">{{ $description }}</p>
            </div>

            <span class="ui-dashboard-progress-updated">
                <i class="bx bx-time-five"></i>
                <span class="{{ trim($updatedClasses) }}">{{ $displayUpdatedText }}</span>
            </span>
        </div>
    </div>

    <div class="card-body pt-3 p-4">
        <div id="dashboard-progress-content" class="d-grid gap-3">
            @foreach ($resolvedRows as $row)
                @php
                    $key = $row['key'];
                    $label = $row['label'] ?? ucfirst((string) $key);
                    $tone = $row['tone'] ?? 'primary';
                    $countId = $row['countId'] ?? "progress-{$key}-count";
                    $percentId = $row['percentId'] ?? "progress-{$key}-percent";
                    $barId = $row['barId'] ?? "progress-{$key}-bar";

                    $countClasses = 'ui-dashboard-progress-count fw-bold';
                    $percentClasses = 'ui-dashboard-progress-percent';

                    if ($loading) {
                        $countClasses .= ' placeholder placeholder-wave d-inline-block rounded stats-value-loading';
                        $percentClasses .= ' placeholder placeholder-wave d-inline-block rounded stats-value-loading';
                    }

                    $displayCount = $loading ? ' ' : 0;
                    $displayPercent = $loading ? ' ' : '0%';
                @endphp

                <div class="ui-dashboard-progress-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="ui-dashboard-progress-label">{{ $label }}</span>
                        <div class="d-flex align-items-center gap-2">
                            <span id="{{ $countId }}" class="{{ trim($countClasses) }}">{{ $displayCount }}</span>
                            <span id="{{ $percentId }}" class="{{ trim($percentClasses) }}">{{ $displayPercent }}</span>
                        </div>
                    </div>

                    <div class="progress ui-dashboard-progress-track mt-2">
                        <div id="{{ $barId }}" class="progress-bar ui-dashboard-progress-bar ui-dashboard-progress-bar-{{ $tone }}"
                            role="progressbar" style="width: 0%;" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"></div>
                    </div>
                </div>
            @endforeach
        </div>

        <div id="dashboard-progress-empty" class="ui-dashboard-empty-state d-none">
            <i class="bx bx-data"></i>
            <h6 class="fw-bold mb-1">{{ $emptyTitle }}</h6>
            <p class="text-secondary mb-0">{{ $emptyDescription }}</p>
        </div>
    </div>
</div>