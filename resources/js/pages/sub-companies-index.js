/**
 * sub-companies/index.js
 * Structured page controller for Sub-Companies listing.
 */

'use strict';

import * as helpers from '../helpers.js';

export function initSubCompaniesPage() {
    const pageNode = document.getElementById('sub-companies-page');

    if (!pageNode || !window.$ || !window.bootstrap || !window.Swal) {
        return;
    }

    const $ = window.$;
    const Utils = helpers;

    const ROUTES = {
        subCompanies: Utils.createEndpoints(pageNode.dataset),
    };

    const TRANSLATION = {
        error: {
            missingSubCompany: 'Unable to identify sub-company.',
            missingSubCompanyForEdit: 'Unable to identify sub-company for editing.',
            missingSubCompanyForView: 'Unable to identify sub-company details.',
            loadStats: 'Unable to load sub-company statistics.',
            loadSubCompanyEdit: 'Unable to load sub-company for editing.',
            loadSubCompanyView: 'Unable to load sub-company details.',
            saveSubCompany: 'Unable to save sub-company.',
            deleteSubCompany: 'Unable to delete sub-company.',
        },
        success: {
            saveCreate: 'Sub-company created successfully.',
            saveUpdate: 'Sub-company updated successfully.',
            delete: 'Sub-company deleted successfully.',
        },
        confirm: {
            delete: {
                titlePrefix: 'Delete ',
                confirmText: 'Yes, delete',
            },
        },
        modal: {
            createTitle: 'Create Sub-Company',
            editTitle: 'Edit Sub-Company',
        },
        loading: {
            text: 'Loading...',
        },
    };

    const DOM = {
        formModal: document.getElementById('employeeFormModal'),
        viewModal: document.getElementById('employeeViewModal'),
        form: document.getElementById('sub-company-form'),
    };

    if (!DOM.formModal || !DOM.viewModal || !DOM.form) {
        return;
    }

    const STATE = {
        statsSelectors: [
            '#summary-sub-companies',
            '#summary-with-squads',
            '#summary-without-squads',
            '#summary-with-assignments',
        ],
    };

    const ScrollManager = Utils.createModalManager();

    const TableManager = {
        table: null,

        init() {
            this.table = Utils.createDataTable(
                '#sub-companies-table',
                ROUTES.subCompanies.datatable,
                [
                    { data: 'name', name: 'name' },
                    { data: 'description', name: 'description' },
                    { data: 'squads_count', name: 'squads_count' },
                    { data: 'assignments_count', name: 'assignments_count' },
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
                        url: ROUTES.subCompanies.datatable,
                        data: (request) => {
                            const searchName = String($('#filter-sub-company-name').val() ?? '').trim();
                            const searchDescription = String($('#filter-sub-company-description').val() ?? '').trim();
                            const searchActive = String($('#filter-sub-company-active').val() ?? '').trim();

                            request.search_name = searchName;
                            request.search_description = searchDescription;

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
        async fetchSubCompany(subCompanyId) {
            const response = await $.get(ROUTES.subCompanies.show(subCompanyId));

            return response?.data;
        },

        async fetchStats() {
            const response = await $.get(ROUTES.subCompanies.stats);

            return response?.data ?? {};
        },

        async createSubCompany(payload) {
            return $.ajax({
                url: ROUTES.subCompanies.store,
                method: 'POST',
                data: $.param(payload),
            });
        },

        async updateSubCompany(subCompanyId, payload) {
            return $.ajax({
                url: ROUTES.subCompanies.update(subCompanyId),
                method: 'POST',
                data: $.param([...payload, { name: '_method', value: 'PUT' }]),
            });
        },

        async deleteSubCompany(subCompanyId) {
            return $.ajax({
                url: ROUTES.subCompanies.destroy(subCompanyId),
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
                '#summary-sub-companies': stats.subCompanies?.count ?? 0,
                '#summary-with-squads': stats.withSquads?.count ?? 0,
                '#summary-without-squads': stats.withoutSquads?.count ?? 0,
                '#summary-with-assignments': stats.withAssignments?.count ?? 0,
            });

            Utils.setLastUpdated(stats.subCompanies?.lastUpdateTime ?? null);
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

            $('#sub_company_id').val('');
            $('#form-active').val('1').trigger('change.select2');
            Utils.clearFormErrors('#sub-company-form-errors');
        },

        populate(subCompany) {
            $('#sub_company_id').val(subCompany.id ?? '');
            $('#form-sub-company-name').val(subCompany.name ?? '');
            $('#form-sub-company-description').val(subCompany.description ?? '');
            $('#form-active').val(String(subCompany.active ?? '1')).trigger('change.select2');
        },

        getSubmitPayload() {
            const id = $('#sub_company_id').val();
            const isEdit = Boolean(id);
            const payload = $('#sub-company-form').serializeArray();

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
                '#view-active',
                '#view-squads-count',
                '#view-assignments-count',
                '#view-created-at',
                '#view-updated-at',
                '#view-description',
                '#view-squads',
            ].forEach((selector) => {
                const node = document.querySelector(selector);
                if (node) {
                    node.textContent = TRANSLATION.loading.text;
                }
            });
        },

        populate(subCompany) {
            $('#view-name').text(subCompany.name ?? '-');
            $('#view-active').text(subCompany.active_label ?? '-');
            $('#view-squads-count').text(subCompany.squads_count ?? 0);
            $('#view-assignments-count').text(subCompany.assignments_count ?? 0);
            $('#view-created-at').text(subCompany.created_at_formatted ?? '-');
            $('#view-updated-at').text(subCompany.updated_at_formatted ?? '-');
            $('#view-description').text(subCompany.description || '-');

            const squads = subCompany.squads ?? [];
            const squadsHtml = squads.length
                ? squads
                    .map((squad) => `${squad.name} (${squad.active_label ?? Utils.humanize(squad.active ?? '')})`)
                    .join('<br>')
                : '-';

            $('#view-squads').html(squadsHtml);
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
                formSelector: '#sub-companies-search-form',
                onReload: () => {
                    void TableManager.reload();
                },
                resetButtonSelector: '#btn-reset-sub-company-filters',
                onReset: () => {
                    Utils.clearSelect2('#filter-sub-company-active');
                },
            });
        },
    };

    const ModalFlowManager = {
        prepareCreate() {
            FormManager.reset();
            $('#sub-company-modal-title').text(TRANSLATION.modal.createTitle);
            Utils.setFormLoading('#sub-company-form', false);
            ScrollManager.update(DOM.formModal);
        },

        async prepareEdit(subCompanyId) {
            FormManager.reset();
            $('#sub-company-modal-title').text(TRANSLATION.modal.editTitle);
            Utils.setFormLoading('#sub-company-form', true);

            try {
                const subCompany = await ApiManager.fetchSubCompany(subCompanyId);
                FormManager.populate(subCompany);
            } catch (error) {
                Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.loadSubCompanyEdit);
                ScrollManager.hideModal(DOM.formModal);
            } finally {
                Utils.setFormLoading('#sub-company-form', false);
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

                const isEdit = trigger.dataset.modalMode === 'edit' || trigger.classList.contains('editSubCompanyBtn');

                if (!isEdit) {
                    this.prepareCreate();

                    return;
                }

                const subCompanyId = trigger.dataset.id;

                if (!subCompanyId) {
                    Utils.showAlert('danger', TRANSLATION.error.missingSubCompanyForEdit);
                    event.preventDefault();

                    return;
                }

                await this.prepareEdit(subCompanyId);
            });

            DOM.formModal.addEventListener('hidden.bs.modal', () => {
                Utils.setFormLoading('#sub-company-form', false);
                Utils.clearFormErrors('#sub-company-form-errors');
                ScrollManager.destroy(DOM.formModal);
            });

            DOM.viewModal.addEventListener('shown.bs.modal', () => {
                ScrollManager.init(DOM.viewModal);
                ScrollManager.update(DOM.viewModal);
            });

            DOM.viewModal.addEventListener('show.bs.modal', async (event) => {
                const trigger = event.relatedTarget;
                const subCompanyId = trigger instanceof HTMLElement ? trigger.dataset.id : null;

                if (!subCompanyId) {
                    event.preventDefault();
                    Utils.showAlert('danger', TRANSLATION.error.missingSubCompanyForView);

                    return;
                }

                ViewManager.setLoading();

                try {
                    const subCompany = await ApiManager.fetchSubCompany(subCompanyId);
                    ViewManager.populate(subCompany);
                    ScrollManager.update(DOM.viewModal);
                } catch (error) {
                    Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.loadSubCompanyView);
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
            $(document).on('click', '.deleteSubCompanyBtn', async function handleDeleteSubCompany() {
                const subCompanyId = $(this).data('id');
                const subCompanyName = $(this).data('name');

                if (!subCompanyId) {
                    Utils.showAlert('danger', TRANSLATION.error.missingSubCompany);

                    return;
                }

                const confirmed = await Utils.confirmAction({
                    title: `${TRANSLATION.confirm.delete.titlePrefix}${subCompanyName}?`,
                    confirmText: TRANSLATION.confirm.delete.confirmText,
                });

                if (!confirmed) {
                    return;
                }

                try {
                    const response = await ApiManager.deleteSubCompany(subCompanyId);
                    Utils.showAlert('success', response.message ?? TRANSLATION.success.delete);
                    await UIRefreshManager.refreshAll();
                } catch (error) {
                    Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.deleteSubCompany);
                }
            });
        },
    };

    const FormSubmissionManager = {
        init() {
            $('#sub-company-form').on('submit', async function submitSubCompanyForm(event) {
                event.preventDefault();
                Utils.clearFormErrors('#sub-company-form-errors');

                const { id, isEdit, payload } = FormManager.getSubmitPayload();

                try {
                    const response = isEdit
                        ? await ApiManager.updateSubCompany(id, payload)
                        : await ApiManager.createSubCompany(payload);

                    ScrollManager.hideModal(DOM.formModal);
                    Utils.showAlert('success', response.message ?? (isEdit ? TRANSLATION.success.saveUpdate : TRANSLATION.success.saveCreate));
                    await UIRefreshManager.refreshAll();
                } catch (error) {
                    if (error.status === 422 && error.responseJSON?.errors) {
                        Utils.showFormErrors('#sub-company-form-errors', error.responseJSON.errors);

                        return;
                    }

                    Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.saveSubCompany);
                }
            });
        },
    };

    const SubCompanyApp = {
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

    SubCompanyApp.init();
}
