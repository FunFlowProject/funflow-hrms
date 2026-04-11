@props([
    'mode' => 'both',
    'id' => null,
    'type' => 'Employee',
    'actions' => [],
    'singleActions' => [],
    'deleteName' => null,
    'showSingleLabels' => false,
])

@php
    $normalizedActions = array_values(array_unique(array_filter($actions)));
    $typeSuffix = trim((string) $type) !== '' ? trim((string) $type) : 'Entity';
    $canRenderDropdown = in_array($mode, ['dropdown', 'both'], true);
    $canRenderSingle = in_array($mode, ['single', 'both'], true);
@endphp

<div class="d-flex gap-2 justify-content-end align-items-center flex-wrap">
    @if ($canRenderDropdown)
        <div class="btn-group">
            <button type="button" class="btn btn-primary btn-icon rounded-pill dropdown-toggle hide-arrow"
                data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}' aria-expanded="false"
                aria-label="Actions" title="Actions">
                <i class="bx bx-dots-vertical-rounded"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                @foreach ($normalizedActions as $actionItem)
                    @if (is_array($actionItem))
                        <li>
                            <a class="dropdown-item {{ $actionItem['class'] ?? '' }}"
                                href="{{ $actionItem['href'] ?? 'javascript:void(0);' }}"
                                @if (isset($actionItem['modal_target'])) data-bs-target="{{ $actionItem['modal_target'] }}" @endif
                                @if (isset($actionItem['modal_toggle'])) data-bs-toggle="{{ $actionItem['modal_toggle'] }}" @endif
                                @if (isset($actionItem['data']) && is_array($actionItem['data'])) @foreach ($actionItem['data'] as $attr => $value) data-{{ $attr }}="{{ $value }}" @endforeach @endif>
                                @if (isset($actionItem['icon']))
                                    <i class="{{ $actionItem['icon'] }} me-1"></i>
                                @endif
                                {{ __($actionItem['label'] ?? '') }}
                            </a>
                        </li>
                    @elseif ($actionItem === 'view')
                        <li>
                            <a class="dropdown-item btn-view-employee view{{ $typeSuffix }}Btn" href="javascript:void(0);"
                                @if ($id) data-id="{{ $id }}" @endif data-bs-toggle="modal"
                                data-bs-target="{{ '#' . lcfirst($typeSuffix) . 'ViewModal' }}">
                                <i class="bx bx-show me-1"></i> {{ __('view') }}
                            </a>
                        </li>
                    @elseif($actionItem === 'edit')
                        <li>
                            <a class="dropdown-item btn-edit-employee edit{{ $typeSuffix }}Btn" href="javascript:void(0);"
                                @if ($id) data-id="{{ $id }}" @endif data-modal-mode="edit"
                                data-bs-toggle="modal" data-bs-target="{{ '#' . lcfirst($typeSuffix) . 'FormModal' }}">
                                <i class="bx bx-edit-alt me-1"></i> {{ __('edit') }}
                            </a>
                        </li>
                    @elseif($actionItem === 'delete')
                        <li>
                            <a class="dropdown-item text-danger btn-delete-employee delete{{ $typeSuffix }}Btn"
                                href="javascript:void(0);" @if ($id) data-id="{{ $id }}" @endif
                                @if ($deleteName) data-name="{{ $deleteName }}" @endif>
                                <i class="bx bx-trash me-1"></i> {{ __('delete') }}
                            </a>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
    @endif

    @if ($canRenderSingle)
        @foreach ($singleActions as $singleAction)
            @php
                $singleLabel = (string) ($singleAction['label'] ?? 'action');
                $showLabel = (bool) ($singleAction['show_label'] ?? $showSingleLabels);
                $singleTone = (string) ($singleAction['tone'] ?? 'primary');
                $singleToneClass = in_array($singleTone, ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'dark', 'light'], true)
                    ? 'btn-outline-' . $singleTone
                    : 'btn-outline-primary';
                $singleButtonClasses = trim(implode(' ', array_filter([
                    'btn',
                    $singleToneClass,
                    $showLabel ? 'rounded-pill px-3 py-2' : 'btn-icon rounded-circle',
                    (string) ($singleAction['class'] ?? ''),
                    (string) ($singleAction['action'] ?? '') . $typeSuffix . 'Btn',
                ])));
            @endphp
            <div>
                @if (isset($singleAction['href']))
                    <a href="{{ $singleAction['href'] }}"
                        class="{{ $singleButtonClasses }}"
                        title="{{ __($singleLabel) }}" aria-label="{{ __($singleLabel) }}">
                        <i class="{{ $singleAction['icon'] }}"></i>
                        @if ($showLabel)
                            <span class="ms-1">{{ __($singleLabel) }}</span>
                        @endif
                    </a>
                @else
                    <button type="button"
                        class="{{ $singleButtonClasses }}"
                        @if ($id) data-id="{{ $id }}" @endif title="{{ __($singleLabel) }}"
                        aria-label="{{ __($singleLabel) }}"
                        @if (isset($singleAction['modal_target'])) data-bs-target="{{ $singleAction['modal_target'] }}" @endif
                        @if (isset($singleAction['modal_toggle'])) data-bs-toggle="{{ $singleAction['modal_toggle'] }}" @endif
                        @if (isset($singleAction['data']) && is_array($singleAction['data'])) @foreach ($singleAction['data'] as $attr => $value) data-{{ $attr }}="{{ $value }}" @endforeach @endif>
                        <i class="{{ $singleAction['icon'] }}"></i>
                        @if ($showLabel)
                            <span class="ms-1">{{ __($singleLabel) }}</span>
                        @endif
                    </button>
                @endif
            </div>
        @endforeach
    @endif
</div>
