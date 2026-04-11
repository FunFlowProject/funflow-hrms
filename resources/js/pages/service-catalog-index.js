/**
 * service-catalog/index.js
 * Structured page controller for Service Catalog listing.
 */

'use strict';

import * as helpers from '../helpers.js';

export function initServiceCatalogPage() {
    const pageNode = document.getElementById('service-catalog-page');

    if (!pageNode || !window.$ || !window.bootstrap || !window.Swal) {
        return;
    }

    const $ = window.$;
    const Utils = helpers;

    const ROUTES = {
        serviceCatalog: Utils.createEndpoints(pageNode.dataset),
    };

    const TRANSLATION = {
        error: {
            missingService: 'Unable to identify service catalog item.',
            missingServiceForEdit: 'Unable to identify service for editing.',
            missingServiceForView: 'Unable to identify service details.',
            loadStats: 'Unable to load service catalog statistics.',
            loadServiceEdit: 'Unable to load service for editing.',
            loadServiceView: 'Unable to load service details.',
            saveService: 'Unable to save service catalog item.',
            deleteService: 'Unable to delete service catalog item.',
        },
        success: {
            saveCreate: 'Service catalog item created successfully.',
            saveUpdate: 'Service catalog item updated successfully.',
            delete: 'Service catalog item deleted successfully.',
        },
        confirm: {
            delete: {
                titlePrefix: 'Delete ',
                confirmText: 'Yes, delete',
            },
        },
        modal: {
            createTitle: 'Create Service',
            editTitle: 'Edit Service',
        },
        loading: {
            text: 'Loading...',
        },
    };

    const DOM = {
        formModal: document.getElementById('employeeFormModal'),
        viewModal: document.getElementById('employeeViewModal'),
        form: document.getElementById('service-catalog-form'),
    };

    if (!DOM.formModal || !DOM.viewModal || !DOM.form) {
        return;
    }

    const STATE = {
        statsSelectors: [
            '#summary-service-catalog',
            '#summary-active-services',
            '#summary-requires-justification',
            '#summary-categories',
        ],
    };

    const ScrollManager = Utils.createModalManager();

    const TableManager = {
        table: null,

        init() {
            this.table = Utils.createDataTable(
                '#service-catalog-table',
                ROUTES.serviceCatalog.datatable,
                [
                    { data: 'name', name: 'name' },
                    { data: 'category', name: 'category' },
                    { data: 'requires_justification', name: 'requires_justification' },
                    { data: 'active', name: 'active' },
                    { data: 'created_at', name: 'created_at' },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-end text-nowrap',
                    },
                ],
                {
                    ajax: {
                        url: ROUTES.serviceCatalog.datatable,
                        data: (request) => {
                            const searchName = String($('#filter-service-catalog-name').val() ?? '').trim();
                            const searchCategory = String($('#filter-service-catalog-category').val() ?? '').trim();
                            const searchRequiresJustification = String($('#filter-service-catalog-requires-justification').val() ?? '').trim();
                            const searchActive = String($('#filter-service-catalog-active').val() ?? '').trim();

                            request.search_name = searchName;
                            request.search_category = searchCategory;

                            if (searchRequiresJustification !== '') {
                                request.search_requires_justification = searchRequiresJustification;
                            }

                            if (searchActive !== '') {
                                request.search_active = searchActive;
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
        async fetchService(serviceCatalogId) {
            const response = await $.get(ROUTES.serviceCatalog.show(serviceCatalogId));

            return response?.data;
        },

        async fetchStats() {
            const response = await $.get(ROUTES.serviceCatalog.stats);

            return response?.data ?? {};
        },

        async createService(payload) {
            return $.ajax({
                url: ROUTES.serviceCatalog.store,
                method: 'POST',
                data: $.param(payload),
            });
        },

        async updateService(serviceCatalogId, payload) {
            return $.ajax({
                url: ROUTES.serviceCatalog.update(serviceCatalogId),
                method: 'POST',
                data: $.param([...payload, { name: '_method', value: 'PUT' }]),
            });
        },

        async deleteService(serviceCatalogId) {
            return $.ajax({
                url: ROUTES.serviceCatalog.destroy(serviceCatalogId),
                method: 'POST',
                data: { _method: 'DELETE' },
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
                '#summary-service-catalog': stats.serviceCatalog?.count ?? 0,
                '#summary-active-services': stats.activeServices?.count ?? 0,
                '#summary-requires-justification': stats.requiresJustification?.count ?? 0,
                '#summary-categories': stats.categories?.count ?? 0,
            });

            Utils.setLastUpdated(stats.serviceCatalog?.lastUpdateTime ?? null);
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

    const FormManager = {
        reset() {
            DOM.form.reset();

            $('#service_catalog_id').val('');
            $('#form-service-catalog-active').val('1').trigger('change.select2');
            $('#form-service-catalog-requires-justification').val('0').trigger('change.select2');
            Utils.clearFormErrors('#service-catalog-form-errors');
        },

        populate(serviceCatalogItem) {
            $('#service_catalog_id').val(serviceCatalogItem.id ?? '');
            $('#form-service-catalog-name').val(serviceCatalogItem.name ?? '');
            $('#form-service-catalog-category').val(serviceCatalogItem.category ?? '');
            $('#form-service-catalog-description').val(serviceCatalogItem.description ?? '');
            $('#form-service-catalog-active').val(String(serviceCatalogItem.active ?? '1')).trigger('change.select2');
            $('#form-service-catalog-requires-justification')
                .val(serviceCatalogItem.requires_justification ? '1' : '0')
                .trigger('change.select2');
        },

        getSubmitPayload() {
            const id = $('#service_catalog_id').val();
            const isEdit = Boolean(id);
            const payload = $('#service-catalog-form').serializeArray();

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
                '#view-name',
                '#view-category',
                '#view-requires-justification',
                '#view-active',
                '#view-created-by',
                '#view-updated-by',
                '#view-created-at',
                '#view-updated-at',
                '#view-description',
            ].forEach((selector) => {
                const node = document.querySelector(selector);
                if (node) {
                    node.textContent = TRANSLATION.loading.text;
                }
            });
        },

        populate(serviceCatalogItem) {
            $('#view-name').text(serviceCatalogItem.name ?? '-');
            $('#view-category').text(serviceCatalogItem.category ?? '-');
            $('#view-requires-justification').text(serviceCatalogItem.requires_justification_label ?? '-');
            $('#view-active').text(serviceCatalogItem.active_label ?? '-');
            $('#view-created-by').text(serviceCatalogItem.created_by_name ?? '-');
            $('#view-updated-by').text(serviceCatalogItem.updated_by_name ?? '-');
            $('#view-created-at').text(serviceCatalogItem.created_at_formatted ?? '-');
            $('#view-updated-at').text(serviceCatalogItem.updated_at_formatted ?? '-');
            $('#view-description').text(serviceCatalogItem.description || '-');
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
                formSelector: '#service-catalog-search-form',
                onReload: () => {
                    void TableManager.reload();
                },
                resetButtonSelector: '#btn-reset-service-catalog-filters',
                onReset: () => {
                    Utils.clearSelect2('#filter-service-catalog-requires-justification');
                    Utils.clearSelect2('#filter-service-catalog-active');
                },
            });
        },
    };

    const ModalFlowManager = {
        prepareCreate() {
            FormManager.reset();
            $('#service-catalog-modal-title').text(TRANSLATION.modal.createTitle);
            Utils.setFormLoading('#service-catalog-form', false);
            ScrollManager.update(DOM.formModal);
        },

        async prepareEdit(serviceCatalogId) {
            FormManager.reset();
            $('#service-catalog-modal-title').text(TRANSLATION.modal.editTitle);
            Utils.setFormLoading('#service-catalog-form', true);

            try {
                const serviceCatalogItem = await ApiManager.fetchService(serviceCatalogId);
                FormManager.populate(serviceCatalogItem);
            } catch (error) {
                Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.loadServiceEdit);
                ScrollManager.hideModal(DOM.formModal);
            } finally {
                Utils.setFormLoading('#service-catalog-form', false);
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
                    this.prepareCreate();

                    return;
                }

                const isEdit = trigger.dataset.modalMode === 'edit' || trigger.classList.contains('editServiceCatalogBtn');

                if (!isEdit) {
                    this.prepareCreate();

                    return;
                }

                const serviceCatalogId = trigger.dataset.id;

                if (!serviceCatalogId) {
                    Utils.showAlert('danger', TRANSLATION.error.missingServiceForEdit);
                    event.preventDefault();

                    return;
                }

                await this.prepareEdit(serviceCatalogId);
            });

            DOM.formModal.addEventListener('hidden.bs.modal', () => {
                Utils.setFormLoading('#service-catalog-form', false);
                Utils.clearFormErrors('#service-catalog-form-errors');
                ScrollManager.destroy(DOM.formModal);
            });

            DOM.viewModal.addEventListener('shown.bs.modal', () => {
                ScrollManager.init(DOM.viewModal);
                ScrollManager.update(DOM.viewModal);
            });

            DOM.viewModal.addEventListener('show.bs.modal', async (event) => {
                const trigger = event.relatedTarget;
                const serviceCatalogId = trigger instanceof HTMLElement ? trigger.dataset.id : null;

                if (!serviceCatalogId) {
                    event.preventDefault();
                    Utils.showAlert('danger', TRANSLATION.error.missingServiceForView);

                    return;
                }

                ViewManager.setLoading();

                try {
                    const serviceCatalogItem = await ApiManager.fetchService(serviceCatalogId);
                    ViewManager.populate(serviceCatalogItem);
                    ScrollManager.update(DOM.viewModal);
                } catch (error) {
                    Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.loadServiceView);
                    ScrollManager.hideModal(DOM.viewModal);
                }
            });

            DOM.viewModal.addEventListener('hidden.bs.modal', () => {
                ScrollManager.destroy(DOM.viewModal);
            });
        },
    };

    const DeleteManager = {
        init() {
            $(document).on('click', '.deleteServiceCatalogBtn', async function handleDeleteServiceCatalog() {
                const serviceCatalogId = $(this).data('id');
                const serviceName = $(this).data('name');

                if (!serviceCatalogId) {
                    Utils.showAlert('danger', TRANSLATION.error.missingService);

                    return;
                }

                const confirmed = await Utils.confirmAction({
                    title: `${TRANSLATION.confirm.delete.titlePrefix}${serviceName}?`,
                    confirmText: TRANSLATION.confirm.delete.confirmText,
                });

                if (!confirmed) {
                    return;
                }

                try {
                    const response = await ApiManager.deleteService(serviceCatalogId);
                    Utils.showAlert('success', response.message ?? TRANSLATION.success.delete);
                    await UIRefreshManager.refreshAll();
                } catch (error) {
                    Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.deleteService);
                }
            });
        },
    };

    const FormSubmissionManager = {
        init() {
            $('#service-catalog-form').on('submit', async function submitServiceCatalogForm(event) {
                event.preventDefault();

                const $submitBtn = Utils.getFormSubmitBtn(this);
                Utils.clearFormErrors('#service-catalog-form-errors');

                const { id, isEdit, payload } = FormManager.getSubmitPayload();
                Utils.setBtnLoading($submitBtn, true);

                try {
                    const response = isEdit
                        ? await ApiManager.updateService(id, payload)
                        : await ApiManager.createService(payload);

                    ScrollManager.hideModal(DOM.formModal);
                    Utils.showAlert('success', response.message ?? (isEdit ? TRANSLATION.success.saveUpdate : TRANSLATION.success.saveCreate));
                    await UIRefreshManager.refreshAll();
                } catch (error) {
                    if (error.status === 422 && error.responseJSON?.errors) {
                        Utils.showFormErrors('#service-catalog-form-errors', error.responseJSON.errors);

                        return;
                    }

                    Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.saveService);
                } finally {
                    Utils.setBtnLoading($submitBtn, false);
                }
            });
        },
    };

    const ServiceCatalogApp = {
        initPlugins() {
            Utils.initSelect2('.select2-filter');
            Utils.initSelect2('.select2-init', $('#employeeFormModal'));
        },

        initEvents() {
            FilterManager.init();
            ModalFlowManager.initLifecycleEvents();
            DeleteManager.init();
            FormSubmissionManager.init();
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

    ServiceCatalogApp.init();
}
