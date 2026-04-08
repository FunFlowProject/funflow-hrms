/**
 * helpers.js
 * ─────────────────────────────────────────────────────────────
 * Shared utilities for every page in the application.
 * Organised into sections:
 *
 *  1. General Utilities
 *  2. AJAX / Endpoints
 *  3. Alerts  (SweetAlert2)
 *  4. Stats Cards
 *  5. Form Utilities
 *  6. Select2 Utilities
 *  7. Flatpickr Utilities
 *  8. DataTable Factory
 *  9. Modal Manager
 * ─────────────────────────────────────────────────────────────
 */

'use strict';

// ─── 1. GENERAL UTILITIES ────────────────────────────────────────────────────

/**
 * Converts snake_case / kebab-case strings to Title Case.
 * e.g. "full_time" → "Full Time"
 *
 * @param {*} value
 * @returns {string}
 */
export function humanize(value) {
    return String(value ?? '')
        .replace(/[_-]/g, ' ')
        .replace(/\b\w/g, (char) => char.toUpperCase());
}

/**
 * Creates a debounced wrapper.
 *
 * @param {Function} callback
 * @param {number} [delay=300]
 * @returns {Function}
 */
export function debounce(callback, delay = 300) {
    let timerId = null;

    return (...args) => {
        if (timerId) {
            window.clearTimeout(timerId);
        }

        timerId = window.setTimeout(() => {
            callback(...args);
        }, delay);
    };
}

// ─── 2. AJAX / ENDPOINTS ─────────────────────────────────────────────────────

/**
 * Bootstraps jQuery AJAX globally with the CSRF token and standard headers.
 * Call once at application boot, not per page.
 */
export function setupAjax() {
    if (!window.$) return;

    const csrfToken =
        document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

    window.$.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
        },
    });
}

/**
 * Replaces a placeholder inside a URL template with an id.
 * e.g. resolveUrl('/employees/__id__/edit', 5) → '/employees/5/edit'
 *
 * @param {string} template
 * @param {string|number} id
 * @param {string} [placeholder='__id__']
 * @returns {string}
 */
export function resolveUrl(template, id, placeholder = '__id__') {
    return template.replace(placeholder, String(id));
}

/**
 * Builds a standard endpoint map from a page element's dataset.
 *
 * Expects these data-* attributes on the element:
 *   store-url, options-url, stats-url, datatable-url,
 *   show-url-template, update-url-template, destroy-url-template
 *
 * Pass any additional dataset keys via `extras`:
 *   { subCompaniesAll: 'subCompaniesAllUrl', squadsAll: 'squadsAllUrl' }
 *
 * @param {DOMStringMap} dataset
 * @param {Record<string, string>} [extras={}]
 * @returns {Record<string, string|function>}
 */
export function createEndpoints(dataset, extras = {}) {
    const endpoints = {
        store: dataset.storeUrl,
        options: dataset.optionsUrl,
        stats: dataset.statsUrl,
        datatable: dataset.datatableUrl,
        show: (id) => resolveUrl(dataset.showUrlTemplate, id),
        update: (id) => resolveUrl(dataset.updateUrlTemplate, id),
        destroy: (id) => resolveUrl(dataset.destroyUrlTemplate, id),
    };

    for (const [key, datasetKey] of Object.entries(extras)) {
        endpoints[key] = dataset[datasetKey];
    }

    return endpoints;
}

// ─── 3. ALERTS (SweetAlert2) ─────────────────────────────────────────────────

const ALERT_TIMER = 3000;

const SWAL_ICON_MAP = {
    success: 'success',
    danger: 'error',
    warning: 'warning',
    info: 'info',
};

/**
 * Shows a non-blocking toast notification.
 *
 * @param {'success'|'danger'|'warning'|'info'} type
 * @param {string} message
 * @param {number} [timer]
 */
export function showAlert(type, message, timer = ALERT_TIMER) {
    if (!window.Swal) {
        console.warn('[helpers] SweetAlert2 not loaded.');
        return;
    }

    window.Swal.fire({
        icon: SWAL_ICON_MAP[type] ?? 'info',
        title: message,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer,
        timerProgressBar: true,
    });
}

/**
 * Shows a confirmation dialog and resolves to true if confirmed.
 *
 * @param {object} [options]
 * @param {string} [options.title]
 * @param {string} [options.text]
 * @param {string} [options.confirmText]
 * @param {string} [options.cancelText]
 * @returns {Promise<boolean>}
 */
export async function confirmAction({
    title = 'Are you sure?',
    text = 'This action cannot be undone.',
    confirmText = 'Yes, proceed',
    cancelText = 'Cancel',
} = {}) {
    if (!window.Swal) return false;

    const result = await window.Swal.fire({
        icon: 'warning',
        title,
        text,
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: cancelText,
        reverseButtons: true,
    });

    return result.isConfirmed;
}

// ─── 4. STATS CARDS ──────────────────────────────────────────────────────────

const VALUE_LOADING_CLASSES = ['placeholder', 'placeholder-wave', 'stats-value-loading', 'd-inline-block', 'rounded'];
const LABEL_LOADING_CLASSES = ['placeholder', 'placeholder-wave', 'stats-updated-loading', 'd-inline-block', 'rounded'];

/**
 * Toggles skeleton-loading animation on stat value nodes and "last updated" labels.
 *
 * @param {object} config
 * @param {string[]} config.valueSelectors       Selectors for the numeric value nodes.
 * @param {string}   [config.labelSelector='.summary-last-updated']
 * @param {boolean}  config.isLoading
 */
export function setStatsLoading({ valueSelectors = [], labelSelector = '.summary-last-updated', isLoading }) {
    valueSelectors.forEach((selector) => {
        const node = document.querySelector(selector);
        if (!node) return;

        if (isLoading) {
            node.classList.add(...VALUE_LOADING_CLASSES);
            node.style.minWidth = '3.25rem';
            if (!String(node.textContent ?? '').trim()) node.textContent = ' ';
        } else {
            node.classList.remove(...VALUE_LOADING_CLASSES);
            node.style.minWidth = '';
        }
    });

    document.querySelectorAll(labelSelector).forEach((node) => {
        if (!(node instanceof HTMLElement)) return;

        if (isLoading) {
            node.classList.add(...LABEL_LOADING_CLASSES);
            node.style.minWidth = '9.5rem';
            if (!String(node.textContent ?? '').trim()) node.textContent = ' ';
        } else {
            node.classList.remove(...LABEL_LOADING_CLASSES);
            node.style.minWidth = '';
        }
    });
}

/**
 * Populates stat cards from a selector → value map.
 *
 * @param {Record<string, string|number>} cardMap
 *   e.g. { '#summary-pending': 4, '#summary-joined': 12 }
 */
export function setStatCards(cardMap) {
    for (const [selector, value] of Object.entries(cardMap)) {
        const node = document.querySelector(selector);
        if (node) node.textContent = value ?? 0;
    }
}

/**
 * Updates the "last updated" label(s). Falls back to current datetime.
 *
 * @param {string|null}  [value]
 * @param {string}       [selector='.summary-last-updated']
 */
export function setLastUpdated(value = null, selector = '.summary-last-updated') {
    const text =
        typeof value === 'string' && value.trim()
            ? `Updated ${value}`
            : `Updated ${new Date().toLocaleString()}`;

    document.querySelectorAll(selector).forEach((node) => {
        node.textContent = text;
    });
}

// ─── 5. FORM UTILITIES ───────────────────────────────────────────────────────

/**
 * Renders server-side validation errors inside an error container.
 *
 * @param {string|Element} container   CSS selector or DOM node.
 * @param {Record<string, string[]>} errors
 */
export function showFormErrors(container, errors) {
    const node = typeof container === 'string' ? document.querySelector(container) : container;
    if (!node) return;

    const html = Object.values(errors)
        .flat()
        .map((msg) => `<li>${msg}</li>`)
        .join('');

    node.classList.remove('d-none');
    node.innerHTML = `<ul class="mb-0 ps-3">${html}</ul>`;
}

/**
 * Hides and empties the error container.
 *
 * @param {string|Element} container
 */
export function clearFormErrors(container) {
    const node = typeof container === 'string' ? document.querySelector(container) : container;
    if (!node) return;

    node.classList.add('d-none');
    node.innerHTML = '';
}

/**
 * Disables or re-enables all non-hidden inputs within a form.
 *
 * @param {string|Element} form
 * @param {boolean} isLoading
 */
export function setFormLoading(form, isLoading) {
    const node = typeof form === 'string' ? document.querySelector(form) : form;
    if (!node) return;

    node.querySelectorAll(':not([type="hidden"])').forEach((el) => {
        el.disabled = isLoading;
    });
}

/**
 * Strips entries from a jQuery serializeArray() payload whose `name`
 * starts with any of the given prefixes.
 *
 * @param {Array<{name: string, value: string}>} payload
 * @param {string[]} prefixes
 * @returns {Array<{name: string, value: string}>}
 */
export function filterPayload(payload, prefixes = []) {
    if (!prefixes.length) return payload;
    return payload.filter(({ name }) => !prefixes.some((p) => name.startsWith(p)));
}

// ─── 6. SELECT2 UTILITIES ────────────────────────────────────────────────────

/**
 * Initialises Select2 on every element matching `selector`.
 * Reads the placeholder from data-placeholder or the first blank option.
 *
 * @param {string}        [selector='.select2-init']
 * @param {string|jQuery} [dropdownParent='body']
 * @param {object}        [extraOptions={}]
 */
export function initSelect2(selector = '.select2-init', dropdownParent = 'body', extraOptions = {}) {
    if (!window.$?.fn?.select2) return;

    const $ = window.$;
    const $parent = typeof dropdownParent === 'string' ? $(dropdownParent) : dropdownParent;

    $(selector).each(function () {
        const $el = $(this);
        const placeholder =
            $el.data('placeholder') ||
            $el.find('option[value=""]').first().text() ||
            'Select option';

        $el.select2({
            theme: 'bootstrap-5',
            dropdownParent: $parent,
            width: '100%',
            placeholder,
            allowClear: !$el.prop('required'),
            ...extraOptions,
        });
    });
}

/**
 * Rebuilds options for a Select2 <select>, preserving the currently selected value.
 *
 * @param {string} selector
 * @param {Array<{value: string|number, label: string}>} items
 * @param {string} placeholder
 */
export function buildSelect2Options(selector, items, placeholder) {
    if (!window.$) return;

    const $ = window.$;
    const $select = $(selector);
    const currentValue = String($select.val() ?? '');

    $select.attr('data-placeholder', placeholder);
    $select.empty().append(`<option value="">${placeholder}</option>`);
    items.forEach(({ value, label }) => $select.append(new Option(label, value)));

    if (currentValue) $select.val(currentValue);
    $select.trigger('change.select2');
}

/**
 * Resets a Select2 to its blank / placeholder state.
 *
 * @param {string} selector
 */
export function clearSelect2(selector) {
    window.$(selector)?.val('').trigger('change.select2');
}

// ─── 7. FLATPICKR UTILITIES ──────────────────────────────────────────────────

/**
 * Returns the Flatpickr instance attached to a DOM element, or null.
 *
 * @param {string} selector
 * @returns {object|null}
 */
export function getFlatpickrInstance(selector) {
    return document.querySelector(selector)?._flatpickr ?? null;
}

/**
 * Initialises Flatpickr on all elements matching the selector.
 *
 * @param {string} [selector='.flatpickr-date']
 * @param {object} [options]
 */
export function initFlatpickr(selector = '.flatpickr-date', options = {}) {
    if (!window.flatpickr) return;

    window.flatpickr(selector, {
        dateFormat: 'Y-m-d',
        allowInput: true,
        ...options,
    });
}

// ─── 8. DATATABLE FACTORY ────────────────────────────────────────────────────

/**
 * Creates a server-side DataTable with sensible defaults.
 * Pass `columnDefs` to override individual column configs.
 *
 * @param {string} selector             e.g. '#employees-table'
 * @param {string} ajaxUrl              DataTable server-side URL.
 * @param {Array}  columns              DataTables column definitions.
 * @param {object} [extraOptions={}]    Any additional DataTables options.
 * @returns {DataTables.Api|null}
 */
export function createDataTable(selector, ajaxUrl, columns, extraOptions = {}) {
    if (!window.$ || !window.$.fn?.DataTable) return null;

    return window.$(selector).DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: ajaxUrl,
        pageLength: 10,
        lengthChange: false,
        layout: {
            topStart: null,
            topEnd: null,
            bottomStart: 'info',
            bottomEnd: 'paging',
        },
        columns,
        ...extraOptions,
    });
}

// ─── 9. MODAL MANAGER ────────────────────────────────────────────────────────

/**
 * Creates an isolated PerfectScrollbar manager for Bootstrap modals.
 *
 * Each page/component should create its own instance.
 *
 * Usage:
 *   const modal = createModalManager();
 *   modalNode.addEventListener('shown.bs.modal',  () => modal.init(modalNode));
 *   modalNode.addEventListener('hidden.bs.modal', () => modal.destroy(modalNode));
 *   // After content changes:
 *   modal.update(modalNode);
 *
 * @returns {{ init, update, destroy, hideModal }}
 */
export function createModalManager() {
    /** @type {Map<Element, object>} */
    const instances = new Map();

    const init = (modalNode, psOptions = {}) => {
        if (!window.PerfectScrollbar || instances.has(modalNode)) return;

        const body = modalNode.querySelector('.modal-body');
        if (!body) return;

        const ps = new window.PerfectScrollbar(body, {
            suppressScrollX: true,
            wheelPropagation: false,
            ...psOptions,
        });

        instances.set(modalNode, ps);
    };

    const update = (modalNode) => {
        instances.get(modalNode)?.update();
    };

    const destroy = (modalNode) => {
        const ps = instances.get(modalNode);
        if (!ps) return;
        ps.destroy();
        instances.delete(modalNode);
    };

    const hideModal = (modalNode) => {
        window.bootstrap?.Modal.getInstance(modalNode)?.hide();
    };

    return { init, update, destroy, hideModal };
}

// ─── 10. PAGE FLOW UTILITIES ────────────────────────────────────────────────

/**
 * Reloads a DataTable while preserving the current page.
 *
 * @param {DataTables.Api|null} table
 * @returns {Promise<void>}
 */
export function reloadDataTable(table) {
    return new Promise((resolve) => {
        if (!table || typeof table.ajax?.reload !== 'function') {
            resolve();

            return;
        }

        table.ajax.reload(() => {
            resolve();
        }, false);
    });
}

/**
 * Refreshes table and stats together.
 *
 * @param {object} config
 * @param {DataTables.Api|null} config.table
 * @param {Function} config.refreshStats
 * @returns {Promise<void>}
 */
export async function refreshTableAndStats({ table, refreshStats }) {
    await Promise.all([
        reloadDataTable(table),
        Promise.resolve().then(() => {
            if (typeof refreshStats === 'function') {
                return refreshStats();
            }

            return undefined;
        }),
    ]);
}

/**
 * Wires realtime search/filter form behavior.
 *
 * @param {object} config
 * @param {string} config.formSelector
 * @param {Function} config.onReload
 * @param {string|null} [config.resetButtonSelector]
 * @param {Function|null} [config.onReset]
 * @param {number} [config.debounceMs=300]
 * @param {string} [config.inputSelector='input']
 * @param {string} [config.selectSelector='select']
 */
export function initRealtimeFilters({
    formSelector,
    onReload,
    resetButtonSelector = null,
    onReset = null,
    debounceMs = 300,
    inputSelector = 'input',
    selectSelector = 'select',
}) {
    if (!window.$ || typeof onReload !== 'function') {
        return;
    }

    const $ = window.$;
    const $form = $(formSelector);

    if (!$form.length) {
        return;
    }

    const triggerRealtimeReload = debounce(() => {
        onReload();
    }, debounceMs);

    $form.on('submit', (event) => {
        event.preventDefault();
        onReload();
    });

    if (inputSelector) {
        $form.on('input', inputSelector, () => {
            triggerRealtimeReload();
        });
    }

    if (selectSelector) {
        $form.on('change', selectSelector, () => {
            onReload();
        });
    }

    if (resetButtonSelector) {
        $(resetButtonSelector).on('click', () => {
            $form.get(0)?.reset();

            if (typeof onReset === 'function') {
                onReset();
            }

            onReload();
        });
    }
}