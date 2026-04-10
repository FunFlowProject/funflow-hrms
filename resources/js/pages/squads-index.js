/**
 * squads/index.js
 * Structured page controller for Squads listing.
 */

'use strict';

import * as helpers from '../helpers.js';

export function initSquadsPage() {
    const pageNode = document.getElementById('squads-page');

    if (!pageNode || !window.$ || !window.bootstrap || !window.Swal) {
        return;
    }

    const $ = window.$;
    const Utils = helpers;

    const ROUTES = {
        squads: Utils.createEndpoints(pageNode.dataset, {
            subCompaniesAll: 'subCompaniesAllUrl',
        }),
    };

    const TRANSLATION = {
        error: {
            missingSquad: 'Unable to identify squad.',
            missingSquadForEdit: 'Unable to identify squad for editing.',
            missingSquadForView: 'Unable to identify squad details.',
            loadStats: 'Unable to load squad statistics.',
            loadSubCompanies: 'Unable to load sub-companies.',
            loadSquadEdit: 'Unable to load squad for editing.',
            loadSquadView: 'Unable to load squad details.',
            saveSquad: 'Unable to save squad.',
            deleteSquad: 'Unable to delete squad.',
        },
        success: {
            saveCreate: 'Squad created successfully.',
            saveUpdate: 'Squad updated successfully.',
            delete: 'Squad deleted successfully.',
        },
        confirm: {
            delete: {
                titlePrefix: 'Delete ',
                confirmText: 'Yes, delete',
            },
        },
        modal: {
            createTitle: 'Create Squad',
            editTitle: 'Edit Squad',
        },
        options: {
            subCompany: 'Select Sub-Company',
        },
        loading: {
            text: 'Loading...',
        },
    };

    const DOM = {
        formModal: document.getElementById('employeeFormModal'),
        viewModal: document.getElementById('employeeViewModal'),
        form: document.getElementById('squad-form'),
    };

    if (!DOM.formModal || !DOM.viewModal || !DOM.form) {
        return;
    }

    const STATE = {
        statsSelectors: [
            '#summary-squads',
            '#summary-with-assignments',
            '#summary-without-assignments',
            '#summary-covered-sub-companies',
        ],
    };

    const ScrollManager = Utils.createModalManager();

    const TableManager = {
        table: null,

        init() {
            this.table = Utils.createDataTable(
                '#squads-table',
                ROUTES.squads.datatable,
                [
                    { data: 'name', name: 'name' },
                    { data: 'sub_company', name: 'sub_company' },
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
                        url: ROUTES.squads.datatable,
                        data: (request) => {
                            const searchName = String($('#filter-squad-name').val() ?? '').trim();
                            const searchSubCompanyId = String($('#filter-squad-sub-company-id').val() ?? '').trim();
                            const searchActive = String($('#filter-squad-active').val() ?? '').trim();

                            request.search_name = searchName;

                            if (searchSubCompanyId !== '') {
                                request.search_sub_company_id = searchSubCompanyId;
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
        async fetchSquad(squadId) {
            const response = await $.get(ROUTES.squads.show(squadId));

            return response?.data;
        },

        async fetchStats() {
            const response = await $.get(ROUTES.squads.stats);

            return response?.data ?? {};
        },

        async fetchSubCompanies() {
            const response = await $.get(ROUTES.squads.subCompaniesAll);

            return response?.data ?? [];
        },

        async createSquad(payload) {
            return $.ajax({
                url: ROUTES.squads.store,
                method: 'POST',
                data: $.param(payload),
            });
        },

        async updateSquad(squadId, payload) {
            return $.ajax({
                url: ROUTES.squads.update(squadId),
                method: 'POST',
                data: $.param([...payload, { name: '_method', value: 'PUT' }]),
            });
        },

        async deleteSquad(squadId) {
            return $.ajax({
                url: ROUTES.squads.destroy(squadId),
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
                '#summary-squads': stats.squads?.count ?? 0,
                '#summary-with-assignments': stats.withAssignments?.count ?? 0,
                '#summary-without-assignments': stats.withoutAssignments?.count ?? 0,
                '#summary-covered-sub-companies': stats.coveredSubCompanies?.count ?? 0,
            });

            Utils.setLastUpdated(stats.squads?.lastUpdateTime ?? null);
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
        renderSubCompanies(subCompanies, selectedValue = '') {
            Utils.buildSelect2Options(
                '#form-sub-company-id',
                subCompanies.map((subCompany) => ({
                    value: subCompany.id,
                    label: subCompany.name,
                })),
                TRANSLATION.options.subCompany,
            );

            $('#form-sub-company-id').val(String(selectedValue ?? '')).trigger('change.select2');
        },

        renderFilterSubCompanies(subCompanies, selectedValue = '') {
            Utils.buildSelect2Options(
                '#filter-squad-sub-company-id',
                subCompanies.map((subCompany) => ({
                    value: subCompany.id,
                    label: subCompany.name,
                })),
                'All Sub-Companies',
            );

            $('#filter-squad-sub-company-id').val(String(selectedValue ?? '')).trigger('change.select2');
        },

        async load(selectedValue = '') {
            try {
                const subCompanies = await ApiManager.fetchSubCompanies();
                this.renderSubCompanies(subCompanies, selectedValue);
            } catch (error) {
                Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.loadSubCompanies);
            }
        },

        async loadFilters(selectedValue = '') {
            try {
                const subCompanies = await ApiManager.fetchSubCompanies();
                this.renderFilterSubCompanies(subCompanies, selectedValue);
            } catch (error) {
                Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.loadSubCompanies);
            }
        },
    };

    const FormManager = {
        reset() {
            DOM.form.reset();

            $('#squad_id').val('');
            Utils.clearSelect2('#form-sub-company-id');
            $('#form-active').val('1').trigger('change.select2');
            Utils.clearFormErrors('#squad-form-errors');
        },

        populate(squad) {
            $('#squad_id').val(squad.id ?? '');
            $('#form-squad-name').val(squad.name ?? '');
            $('#form-sub-company-id').val(String(squad.sub_company_id ?? '')).trigger('change.select2');
            $('#form-active').val(String(squad.active ?? '1')).trigger('change.select2');
        },

        getSubmitPayload() {
            const id = $('#squad_id').val();
            const isEdit = Boolean(id);
            const payload = $('#squad-form').serializeArray();

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
                '#view-sub-company',
                '#view-active',
                '#view-assignments-count',
                '#view-created-at',
                '#view-updated-at',
            ].forEach((selector) => {
                const node = document.querySelector(selector);
                if (node) {
                    node.textContent = TRANSLATION.loading.text;
                }
            });
        },

        populate(squad) {
            $('#view-name').text(squad.name ?? '-');
            $('#view-sub-company').text(squad.sub_company_name ?? '-');
            $('#view-active').text(squad.active_label ?? '-');
            $('#view-assignments-count').text(squad.assignments_count ?? 0);
            $('#view-created-at').text(squad.created_at_formatted ?? '-');
            $('#view-updated-at').text(squad.updated_at_formatted ?? '-');
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
                formSelector: '#squads-search-form',
                onReload: () => {
                    void TableManager.reload();
                },
                resetButtonSelector: '#btn-reset-squad-filters',
                onReset: () => {
                    Utils.clearSelect2('#filter-squad-sub-company-id');
                    Utils.clearSelect2('#filter-squad-active');
                },
            });
        },
    };

    const ModalFlowManager = {
        async prepareCreate() {
            FormManager.reset();
            $('#squad-modal-title').text(TRANSLATION.modal.createTitle);
            Utils.setFormLoading('#squad-form', true);

            try {
                await OptionsManager.load();
            } finally {
                Utils.setFormLoading('#squad-form', false);
                ScrollManager.update(DOM.formModal);
            }
        },

        async prepareEdit(squadId) {
            FormManager.reset();
            $('#squad-modal-title').text(TRANSLATION.modal.editTitle);
            Utils.setFormLoading('#squad-form', true);

            try {
                const squad = await ApiManager.fetchSquad(squadId);
                await OptionsManager.load(String(squad.sub_company_id ?? ''));
                FormManager.populate(squad);
            } catch (error) {
                Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.loadSquadEdit);
                ScrollManager.hideModal(DOM.formModal);
            } finally {
                Utils.setFormLoading('#squad-form', false);
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

                const isEdit = trigger.dataset.modalMode === 'edit' || trigger.classList.contains('editSquadBtn');

                if (!isEdit) {
                    await this.prepareCreate();

                    return;
                }

                const squadId = trigger.dataset.id;

                if (!squadId) {
                    Utils.showAlert('danger', TRANSLATION.error.missingSquadForEdit);
                    event.preventDefault();

                    return;
                }

                await this.prepareEdit(squadId);
            });

            DOM.formModal.addEventListener('hidden.bs.modal', () => {
                Utils.setFormLoading('#squad-form', false);
                Utils.clearFormErrors('#squad-form-errors');
                ScrollManager.destroy(DOM.formModal);
            });

            DOM.viewModal.addEventListener('shown.bs.modal', () => {
                ScrollManager.init(DOM.viewModal);
                ScrollManager.update(DOM.viewModal);
            });

            DOM.viewModal.addEventListener('show.bs.modal', async (event) => {
                const trigger = event.relatedTarget;
                const squadId = trigger instanceof HTMLElement ? trigger.dataset.id : null;

                if (!squadId) {
                    event.preventDefault();
                    Utils.showAlert('danger', TRANSLATION.error.missingSquadForView);

                    return;
                }

                ViewManager.setLoading();

                try {
                    const squad = await ApiManager.fetchSquad(squadId);
                    ViewManager.populate(squad);
                    ScrollManager.update(DOM.viewModal);
                } catch (error) {
                    Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.loadSquadView);
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
            $(document).on('click', '.deleteSquadBtn', async function handleDeleteSquad() {
                const squadId = $(this).data('id');
                const squadName = $(this).data('name');

                if (!squadId) {
                    Utils.showAlert('danger', TRANSLATION.error.missingSquad);

                    return;
                }

                const confirmed = await Utils.confirmAction({
                    title: `${TRANSLATION.confirm.delete.titlePrefix}${squadName}?`,
                    confirmText: TRANSLATION.confirm.delete.confirmText,
                });

                if (!confirmed) {
                    return;
                }

                try {
                    const response = await ApiManager.deleteSquad(squadId);
                    Utils.showAlert('success', response.message ?? TRANSLATION.success.delete);
                    await UIRefreshManager.refreshAll();
                } catch (error) {
                    Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.deleteSquad);
                }
            });
        },
    };

    const FormSubmissionManager = {
        init() {
            $('#squad-form').on('submit', async function submitSquadForm(event) {
                event.preventDefault();

                const $submitBtn = $(this).find('button[type="submit"]');
                Utils.clearFormErrors('#squad-form-errors');

                const { id, isEdit, payload } = FormManager.getSubmitPayload();
                Utils.setBtnLoading($submitBtn, true);

                try {
                    const response = isEdit
                        ? await ApiManager.updateSquad(id, payload)
                        : await ApiManager.createSquad(payload);

                    ScrollManager.hideModal(DOM.formModal);
                    Utils.showAlert('success', response.message ?? (isEdit ? TRANSLATION.success.saveUpdate : TRANSLATION.success.saveCreate));
                    await UIRefreshManager.refreshAll();
                } catch (error) {
                    if (error.status === 422 && error.responseJSON?.errors) {
                        Utils.showFormErrors('#squad-form-errors', error.responseJSON.errors);

                        return;
                    }

                    Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.saveSquad);
                } finally {
                    Utils.setBtnLoading($submitBtn, false);
                }
            });
        },
    };

    const SquadApp = {
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
            void OptionsManager.loadFilters();
        },

        init() {
            this.initPlugins();
            TableManager.init();
            this.initEvents();
            this.initData();
        },
    };

    SquadApp.init();
}
