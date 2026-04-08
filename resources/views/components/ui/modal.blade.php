@props([
    'id',
    'title' => null,
    'size' => null,
    'scrollable' => false,
    'centered' => false,
    'dialogClass' => '',
    'contentClass' => 'rounded-4 border-0 shadow',
    'headerClass' => 'border-bottom-0 pb-0 pt-4 px-4',
    'bodyClass' => 'px-4',
    'footerClass' => 'border-top-0 pb-4 px-4',
    'showCloseButton' => true,
])

@php
    $dialogClasses = trim(implode(' ', array_filter([
        'modal-dialog',
        $size ? "modal-{$size}" : null,
        $scrollable ? 'modal-dialog-scrollable' : null,
        $centered ? 'modal-dialog-centered' : null,
        $dialogClass,
    ])));
@endphp

<div {{ $attributes->merge(['class' => 'modal fade ui-modal', 'id' => $id, 'tabindex' => '-1', 'aria-hidden' => 'true']) }}>
    <div class="{{ $dialogClasses }}">
        <div class="modal-content {{ $contentClass }}">
            @if (isset($header))
                <div class="modal-header {{ $headerClass }}">
                    {{ $header }}
                </div>
            @elseif ($title !== null)
                <div class="modal-header {{ $headerClass }}">
                    <h5 class="modal-title fw-bolder text-dark">{{ $title }}</h5>
                    @if ($showCloseButton)
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    @endif
                </div>
            @endif

            <div class="modal-body {{ $bodyClass }}">
                {{ $slot }}
            </div>

            @if (isset($footer))
                <div class="modal-footer {{ $footerClass }}">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
