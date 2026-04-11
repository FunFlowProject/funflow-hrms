/**
 * service-requests/index.js
 * Structured page controller for Service Requests listing.
 */

'use strict';

import * as helpers from '../helpers.js';

export function initServiceRequestsPage() {
    const pageNode = document.getElementById('service-requests-page')
        ?? document.getElementById('my-service-requests-page');

    if (!pageNode || !window.$ || !window.bootstrap || !window.Swal) {
        return;
    }

    const isEmployeePage = pageNode.id === 'my-service-requests-page';

    const $ = window.$;
    const Utils = helpers;

    const ROUTES = {
        serviceRequests: Utils.createEndpoints(pageNode.dataset),
    };

    ROUTES.serviceRequests.moveToInProgress = (id) => Utils.resolveUrl(pageNode.dataset.inProgressUrlTemplate, id);
    ROUTES.serviceRequests.complete = (id) => Utils.resolveUrl(pageNode.dataset.completeUrlTemplate, id);
    ROUTES.serviceRequests.reject = (id) => Utils.resolveUrl(pageNode.dataset.rejectUrlTemplate, id);

    const TRANSLATION = {
        error: {
            missingRequest: 'Unable to identify service request.',
            missingRequestForEdit: 'Unable to identify service request for editing.',
            missingRequestForView: 'Unable to identify service request details.',
            loadStats: 'Unable to load service request statistics.',
            loadOptions: 'Unable to load service request options.',
            loadRequestEdit: 'Unable to load service request for editing.',
            loadRequestView: 'Unable to load service request details.',
            saveRequest: 'Unable to save service request.',
            transitionRequest: 'Unable to update service request status.',
            justificationRequired: 'Justification is required for the selected service.',
            rejectionReasonRequired: 'Rejection reason is required.',
        },
        success: {
            saveCreate: 'Service request submitted successfully.',
            saveUpdate: 'Service request updated successfully.',
            moveToInProgress: 'Service request moved to in progress.',
            complete: 'Service request completed.',
            reject: 'Service request rejected.',
        },
        confirm: {
            start: {
                titlePrefix: 'Start progress for ',
                titleSuffix: '?',
                confirmText: 'Yes, start',
            },
        },
        modal: {
            createTitle: 'New Service Request',
            editTitle: 'Edit Service Request',
            submitButton: 'Submit Request',
            updateButton: 'Save Changes',
        },
        options: {
            service: 'Select Service',
        },
        loading: {
            text: 'Loading...',
        },
    };

    const DOM = {
        formModal: document.getElementById('serviceRequestFormModal'),
        viewModal: document.getElementById('employeeViewModal'),
        form: document.getElementById('service-request-form'),
        saveButton: document.getElementById('service-request-save-button'),
        justificationRequiredIndicator: document.getElementById('justification-required-indicator'),
    };

    if (!DOM.formModal || !DOM.viewModal || !DOM.form) {
        return;
    }

    const STATE = {
        statsSelectors: [
            '#summary-submitted',
            '#summary-in-progress',
            '#summary-completed',
            '#summary-rejected',
        ],
        canManage: false,
        serviceCatalogItems: [],
    };

    const ScrollManager = Utils.createModalManager();

    const TableManager = {
        table: null,

        init() {
            const tableColumns = [
                { data: 'service_name', name: 'service_name_snapshot' },
                { data: 'service_category', name: 'service_category_snapshot' },
                ...(
                    isEmployeePage
                        ? []
                        : [{ data: 'requester', name: 'requester_id' }]
                ),
                { data: 'status', name: 'status' },
                { data: 'handled_by_name', name: 'handled_by' },
                { data: 'created_at', name: 'created_at' },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    className: 'text-end text-nowrap',
                },
            ];

            this.table = Utils.createDataTable(
                '#service-requests-table',
                ROUTES.serviceRequests.datatable,
                tableColumns,
                {
                    ajax: {
                        url: ROUTES.serviceRequests.datatable,
                        data: (request) => {
                            const searchService = String($('#filter-service-request-service').val() ?? '').trim();
                            const searchCategory = String($('#filter-service-request-category').val() ?? '').trim();
                            const searchStatus = String($('#filter-service-request-status').val() ?? '').trim();
                            const requesterFilter = document.getElementById('filter-service-request-requester');
                            const searchRequester = requesterFilter
                                ? String($(requesterFilter).val() ?? '').trim()
                                : '';

                            request.search_service = searchService;
                            request.search_category = searchCategory;

                            if (searchStatus !== '') {
                                request.search_status = searchStatus;
                            }

                            if (!isEmployeePage && searchRequester !== '') {
                                request.search_requester = searchRequester;
                            }
                        },
                    },
                },
            );
        },

        reload() {
            return Utils.reloadDataTable(this.table);
        },
    };

    const ApiManager = {
        async fetchServiceRequest(serviceRequestId) {
            const response = await $.get(ROUTES.serviceRequests.show(serviceRequestId));

            return response?.data;
        },

        async fetchStats() {
            const response = await $.get(ROUTES.serviceRequests.stats);

            return response?.data ?? {};
        },

        async fetchOptions() {
            const response = await $.get(ROUTES.serviceRequests.options);

            return response?.data ?? {};
        },

        async createServiceRequest(payload) {
            return $.ajax({
                url: ROUTES.serviceRequests.store,
                method: 'POST',
                data: $.param(payload),
            });
        },

        async updateServiceRequest(serviceRequestId, payload) {
            return $.ajax({
                url: ROUTES.serviceRequests.update(serviceRequestId),
                method: 'POST',
                data: $.param([...payload, { name: '_method', value: 'PUT' }]),
            });
        },

        async moveToInProgress(serviceRequestId, payload = {}) {
            return $.ajax({
                url: ROUTES.serviceRequests.moveToInProgress(serviceRequestId),
                method: 'POST',
                data: payload,
            });
        },

        async complete(serviceRequestId, payload = {}) {
            return $.ajax({
                url: ROUTES.serviceRequests.complete(serviceRequestId),
                method: 'POST',
                data: payload,
            });
        },

        async reject(serviceRequestId, payload) {
            return $.ajax({
                url: ROUTES.serviceRequests.reject(serviceRequestId),
                method: 'POST',
                data: payload,
            });
        },
    };

    const StatsManager = {
        setLoading(isLoading) {
            Utils.setStatsLoading({
                valueSelectors: STATE.statsSelectors,
                isLoading,
            });
        },

        setCards(stats = {}) {
            Utils.setStatCards({
                '#summary-submitted': stats.submittedRequests?.count ?? 0,
                '#summary-in-progress': stats.inProgressRequests?.count ?? 0,
                '#summary-completed': stats.completedRequests?.count ?? 0,
                '#summary-rejected': stats.rejectedRequests?.count ?? 0,
            });

            Utils.setLastUpdated(stats.totalRequests?.lastUpdateTime ?? null);
        },

        async refresh() {
            this.setLoading(true);

            try {
                const stats = await ApiManager.fetchStats();
                this.setCards(stats);
            } catch (error) {
                this.setCards({});
                Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.loadStats);
            } finally {
                this.setLoading(false);
            }
        },
    };

    const OptionsManager = {
        renderServiceCatalogItems(selectedValue = '') {
            Utils.buildSelect2Options(
                '#form-service-request-service-catalog-item',
                STATE.serviceCatalogItems.map((item) => ({
                    value: item.id,
                    label: `${item.name} (${item.category})`,
                })),
                TRANSLATION.options.service,
            );

            $('#form-service-request-service-catalog-item').val(String(selectedValue ?? '')).trigger('change.select2');
        },

        ensureMissingServiceOption(serviceRequest) {
            const id = String(serviceRequest.service_catalog_item_id ?? '');
            if (!id) {
                return;
            }

            const found = STATE.serviceCatalogItems.some((item) => String(item.id) === id);
            if (found) {
                return;
            }

            const label = `${serviceRequest.service_name ?? '-'} (${serviceRequest.service_category ?? '-'})`;
            const select = document.getElementById('form-service-request-service-catalog-item');
            if (!(select instanceof HTMLSelectElement)) {
                return;
            }

            const option = new Option(label, id);
            select.add(option);
        },

        async load() {
            try {
                const options = await ApiManager.fetchOptions();
                STATE.serviceCatalogItems = options.serviceCatalogItems ?? [];
                STATE.canManage = Boolean(options.canManage ?? false);
                this.renderServiceCatalogItems();
            } catch (error) {
                Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.loadOptions);
            }
        },

        findSelectedService() {
            const serviceCatalogItemId = String($('#form-service-request-service-catalog-item').val() ?? '');

            if (!serviceCatalogItemId) {
                return null;
            }

            return STATE.serviceCatalogItems.find((item) => String(item.id) === serviceCatalogItemId) ?? null;
        },
    };

    const FormManager = {
        reset() {
            DOM.form.reset();

            $('#service_request_id').val('');
            Utils.clearSelect2('#form-service-request-service-catalog-item');
            $('#form-service-request-justification').val('');
            $('#service-request-save-button').text(TRANSLATION.modal.submitButton);
            Utils.clearFormErrors('#service-request-form-errors');
            this.updateJustificationIndicator();
        },

        populate(serviceRequest) {
            $('#service_request_id').val(serviceRequest.id ?? '');

            OptionsManager.ensureMissingServiceOption(serviceRequest);

            $('#form-service-request-service-catalog-item')
                .val(String(serviceRequest.service_catalog_item_id ?? ''))
                .trigger('change.select2');

            $('#form-service-request-justification').val(serviceRequest.justification ?? '');
            this.updateJustificationIndicator(serviceRequest.service_requires_justification);
        },

        updateJustificationIndicator(explicitRequired = null) {
            const selectedService = OptionsManager.findSelectedService();
            const isRequired = explicitRequired !== null
                ? Boolean(explicitRequired)
                : Boolean(selectedService?.requires_justification ?? false);

            const justification = document.getElementById('form-service-request-justification');
            if (!(justification instanceof HTMLTextAreaElement)) {
                return;
            }

            if (isRequired) {
                justification.setAttribute('required', 'required');
                DOM.justificationRequiredIndicator?.classList.remove('d-none');
            } else {
                justification.removeAttribute('required');
                DOM.justificationRequiredIndicator?.classList.add('d-none');
            }
        },

        validateJustification() {
            const selectedService = OptionsManager.findSelectedService();
            const requiresJustification = Boolean(selectedService?.requires_justification ?? false);
            const justification = String($('#form-service-request-justification').val() ?? '').trim();

            if (requiresJustification && justification === '') {
                Utils.showAlert('danger', TRANSLATION.error.justificationRequired);

                return false;
            }

            return true;
        },

        getSubmitPayload() {
            const id = $('#service_request_id').val();
            const isEdit = Boolean(id);
            const payload = $('#service-request-form').serializeArray();

            return {
                id,
                isEdit,
                payload,
            };
        },
    };

    const ViewManager = {
        setLoading() {
            [
                '#view-service-name',
                '#view-service-category',
                '#view-requester',
                '#view-status',
                '#view-handled-by',
                '#view-acted-at',
                '#view-created-at',
                '#view-updated-at',
                '#view-justification',
                '#view-fulfillment-note',
                '#view-rejection-reason',
            ].forEach((selector) => {
                const node = document.querySelector(selector);
                if (node) {
                    node.textContent = TRANSLATION.loading.text;
                }
            });
        },

        populate(serviceRequest) {
            $('#view-service-name').text(serviceRequest.service_name ?? '-');
            $('#view-service-category').text(serviceRequest.service_category ?? '-');
            $('#view-requester').text(`${serviceRequest.requester_name ?? '-'} (${serviceRequest.requester_email ?? '-'})`);
            $('#view-status').text(serviceRequest.status_label ?? '-');
            $('#view-handled-by').text(serviceRequest.handled_by_name ?? '-');
            $('#view-acted-at').text(serviceRequest.acted_at_formatted ?? '-');
            $('#view-created-at').text(serviceRequest.created_at_formatted ?? '-');
            $('#view-updated-at').text(serviceRequest.updated_at_formatted ?? '-');
            $('#view-justification').text(serviceRequest.justification || '-');
            $('#view-fulfillment-note').text(serviceRequest.fulfillment_note || '-');
            $('#view-rejection-reason').text(serviceRequest.rejection_reason || '-');
        },
    };

    const UIRefreshManager = {
        async refreshAll() {
            await Utils.refreshTableAndStats({
                table: TableManager.table,
                refreshStats: () => StatsManager.refresh(),
            });
        },
    };

    const FilterManager = {
        init() {
            Utils.initRealtimeFilters({
                formSelector: '#service-requests-search-form',
                onReload: () => {
                    void TableManager.reload();
                },
                resetButtonSelector: '#btn-reset-service-request-filters',
                onReset: () => {
                    Utils.clearSelect2('#filter-service-request-status');
                },
            });
        },
    };

    const ModalFlowManager = {
        async prepareCreate() {
            FormManager.reset();
            await OptionsManager.load();
            $('#service-request-modal-title').text(TRANSLATION.modal.createTitle);
            DOM.saveButton.textContent = TRANSLATION.modal.submitButton;
            Utils.setFormLoading('#service-request-form', false);
            ScrollManager.update(DOM.formModal);
        },

        async prepareEdit(serviceRequestId) {
            FormManager.reset();
            $('#service-request-modal-title').text(TRANSLATION.modal.editTitle);
            DOM.saveButton.textContent = TRANSLATION.modal.updateButton;
            Utils.setFormLoading('#service-request-form', true);

            try {
                await OptionsManager.load();
                const serviceRequest = await ApiManager.fetchServiceRequest(serviceRequestId);
                FormManager.populate(serviceRequest);
            } catch (error) {
                Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.loadRequestEdit);
                ScrollManager.hideModal(DOM.formModal);
            } finally {
                Utils.setFormLoading('#service-request-form', false);
                ScrollManager.update(DOM.formModal);
            }
        },

        initLifecycleEvents() {
            DOM.formModal.addEventListener('shown.bs.modal', () => {
                ScrollManager.init(DOM.formModal);
                ScrollManager.update(DOM.formModal);
            });

            DOM.formModal.addEventListener('show.bs.modal', async (event) => {
                const trigger = event.relatedTarget;

                if (!(trigger instanceof HTMLElement)) {
                    await this.prepareCreate();

                    return;
                }

                const isEdit = trigger.dataset.modalMode === 'edit' || trigger.classList.contains('editServiceRequestBtn');

                if (!isEdit) {
                    await this.prepareCreate();

                    return;
                }

                const serviceRequestId = trigger.dataset.id;

                if (!serviceRequestId) {
                    Utils.showAlert('danger', TRANSLATION.error.missingRequestForEdit);
                    event.preventDefault();

                    return;
                }

                await this.prepareEdit(serviceRequestId);
            });

            DOM.formModal.addEventListener('hidden.bs.modal', () => {
                Utils.setFormLoading('#service-request-form', false);
                Utils.clearFormErrors('#service-request-form-errors');
                ScrollManager.destroy(DOM.formModal);
            });

            DOM.viewModal.addEventListener('shown.bs.modal', () => {
                ScrollManager.init(DOM.viewModal);
                ScrollManager.update(DOM.viewModal);
            });

            DOM.viewModal.addEventListener('show.bs.modal', async (event) => {
                const trigger = event.relatedTarget;
                const serviceRequestId = trigger instanceof HTMLElement ? trigger.dataset.id : null;

                if (!serviceRequestId) {
                    event.preventDefault();
                    Utils.showAlert('danger', TRANSLATION.error.missingRequestForView);

                    return;
                }

                ViewManager.setLoading();

                try {
                    const serviceRequest = await ApiManager.fetchServiceRequest(serviceRequestId);
                    ViewManager.populate(serviceRequest);
                    ScrollManager.update(DOM.viewModal);
                } catch (error) {
                    Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.loadRequestView);
                    ScrollManager.hideModal(DOM.viewModal);
                }
            });

            DOM.viewModal.addEventListener('hidden.bs.modal', () => {
                ScrollManager.destroy(DOM.viewModal);
            });
        },
    };

    const TransitionManager = {
        init() {
            $(document).on('click', '.btn-start-service-request', async function handleStartServiceRequest() {
                const serviceRequestId = $(this).data('id');
                const serviceName = String($(this).data('name') ?? 'this request');

                if (!serviceRequestId) {
                    Utils.showAlert('danger', TRANSLATION.error.missingRequest);

                    return;
                }

                const confirmed = await Utils.confirmAction({
                    title: `${TRANSLATION.confirm.start.titlePrefix}${serviceName}${TRANSLATION.confirm.start.titleSuffix}`,
                    confirmText: TRANSLATION.confirm.start.confirmText,
                });

                if (!confirmed) {
                    return;
                }

                try {
                    const response = await ApiManager.moveToInProgress(serviceRequestId);
                    Utils.showAlert('success', response.message ?? TRANSLATION.success.moveToInProgress);
                    await UIRefreshManager.refreshAll();
                } catch (error) {
                    Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.transitionRequest);
                }
            });

            $(document).on('click', '.btn-complete-service-request', async function handleCompleteServiceRequest() {
                const serviceRequestId = $(this).data('id');

                if (!serviceRequestId) {
                    Utils.showAlert('danger', TRANSLATION.error.missingRequest);

                    return;
                }

                const result = await window.Swal.fire({
                    icon: 'question',
                    title: 'Complete this service request?',
                    text: 'You can optionally add a fulfillment note.',
                    input: 'textarea',
                    inputPlaceholder: 'Optional fulfillment note',
                    showCancelButton: true,
                    confirmButtonText: 'Mark as completed',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true,
                });

                if (!result.isConfirmed) {
                    return;
                }

                const payload = String(result.value ?? '').trim() !== ''
                    ? { fulfillment_note: String(result.value).trim() }
                    : {};

                try {
                    const response = await ApiManager.complete(serviceRequestId, payload);
                    Utils.showAlert('success', response.message ?? TRANSLATION.success.complete);
                    await UIRefreshManager.refreshAll();
                } catch (error) {
                    Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.transitionRequest);
                }
            });

            $(document).on('click', '.btn-reject-service-request', async function handleRejectServiceRequest() {
                const serviceRequestId = $(this).data('id');

                if (!serviceRequestId) {
                    Utils.showAlert('danger', TRANSLATION.error.missingRequest);

                    return;
                }

                const result = await window.Swal.fire({
                    icon: 'warning',
                    title: 'Reject this service request?',
                    input: 'textarea',
                    inputPlaceholder: 'Write the rejection reason',
                    showCancelButton: true,
                    confirmButtonText: 'Reject request',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true,
                    inputValidator: (value) => {
                        if (!String(value ?? '').trim()) {
                            return TRANSLATION.error.rejectionReasonRequired;
                        }

                        return null;
                    },
                });

                if (!result.isConfirmed) {
                    return;
                }

                try {
                    const response = await ApiManager.reject(serviceRequestId, {
                        rejection_reason: String(result.value).trim(),
                    });
                    Utils.showAlert('success', response.message ?? TRANSLATION.success.reject);
                    await UIRefreshManager.refreshAll();
                } catch (error) {
                    Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.transitionRequest);
                }
            });
        },
    };

    const FormSubmissionManager = {
        init() {
            $('#service-request-form').on('submit', async function submitServiceRequestForm(event) {
                event.preventDefault();

                const $submitBtn = Utils.getFormSubmitBtn(this);
                Utils.clearFormErrors('#service-request-form-errors');

                if (!FormManager.validateJustification()) {
                    return;
                }

                const { id, isEdit, payload } = FormManager.getSubmitPayload();
                Utils.setBtnLoading($submitBtn, true);

                try {
                    const response = isEdit
                        ? await ApiManager.updateServiceRequest(id, payload)
                        : await ApiManager.createServiceRequest(payload);

                    ScrollManager.hideModal(DOM.formModal);
                    Utils.showAlert('success', response.message ?? (isEdit ? TRANSLATION.success.saveUpdate : TRANSLATION.success.saveCreate));
                    await UIRefreshManager.refreshAll();
                } catch (error) {
                    if (error.status === 422 && error.responseJSON?.errors) {
                        Utils.showFormErrors('#service-request-form-errors', error.responseJSON.errors);

                        return;
                    }

                    Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.saveRequest);
                } finally {
                    Utils.setBtnLoading($submitBtn, false);
                }
            });
        },
    };

    const ServiceRequestApp = {
        initPlugins() {
            Utils.initSelect2('.select2-filter');
            Utils.initSelect2('.select2-init', $('#employeeFormModal'));
        },

        initEvents() {
            FilterManager.init();
            ModalFlowManager.initLifecycleEvents();
            TransitionManager.init();
            FormSubmissionManager.init();
            $('#form-service-request-service-catalog-item').on('change', () => {
                FormManager.updateJustificationIndicator();
            });
        },

        initData() {
            void StatsManager.refresh();
        },

        init() {
            this.initPlugins();
            TableManager.init();
            this.initEvents();
            this.initData();
        },
    };

    ServiceRequestApp.init();
}
