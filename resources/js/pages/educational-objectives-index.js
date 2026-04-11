/**
 * educational-objectives/index.js
 * Structured page controller for Educational Objectives listing.
 */

'use strict';

import * as helpers from '../helpers.js';

export function initEducationalObjectivesPage() {
    const pageNode = document.getElementById('educational-objectives-page');

    if (!pageNode || !window.$ || !window.bootstrap || !window.Swal) {
        return;
    }

    const $ = window.$;
    const Utils = helpers;

    const ROUTES = {
        objectives: Utils.createEndpoints(pageNode.dataset, {
            subCompaniesAll: 'subCompaniesAllUrl',
            squadsAll: 'squadsAllUrl',
            employeesAll: 'employeesAllUrl',
        }),
    };

    const TRANSLATION = {
        error: {
            missingObjective: 'Unable to identify objective.',
            loadStats: 'Unable to load statistics.',
            loadTargets: 'Unable to load organizational units.',
            saveObjective: 'Unable to save objective.',
            deleteObjective: 'Unable to delete objective.',
        },
        success: {
            saveCreate: 'Educational objective assigned successfully.',
            delete: 'Objective deleted successfully.',
        },
        confirm: {
            delete: {
                titlePrefix: 'Delete ',
                confirmText: 'Yes, delete',
            },
        },
        modal: {
            createTitle: 'Assign Educational Objective',
        },
        options: {
            subCompany: 'Select Sub-Company',
            squad: 'Select Squad',
        },
    };

    const DOM = {
        formModal: document.getElementById('objectiveFormModal'),
        form: document.getElementById('objective-form'),
    };

    if (!DOM.formModal || !DOM.form) {
        return;
    }

    const STATE = {
        statsSelectors: [
            '#summary-total',
            '#summary-in-progress',
            '#summary-overdue',
            '#summary-completed',
        ],
    };

    const ScrollManager = Utils.createModalManager();

    const TableManager = {
        table: null,

        init() {
            this.table = Utils.createDataTable(
                '#objectives-table',
                ROUTES.objectives.datatable,
                [
                    { data: 'name', name: 'name' },
                    { 
                        data: 'priority', 
                        name: 'priority',
                        render: function(data) {
                            let color = 'secondary';
                            if (data === 'High') color = 'danger';
                            if (data === 'Medium') color = 'warning';
                            if (data === 'Low') color = 'info';
                            return `<span class="badge bg-${color}">${data}</span>`;
                        }
                    },
                    { data: 'scope_label', name: 'scope_type' },
                    { data: 'target_date', name: 'target_date' },
                    { data: 'progress', name: 'progress' },
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
                        url: ROUTES.objectives.datatable,
                        data: (request) => {
                            request.search_name = String($('#filter-objective-name').val() ?? '').trim();
                            request.search_priority = String($('#filter-objective-priority').val() ?? '').trim();
                            request.search_scope = String($('#filter-objective-scope').val() ?? '').trim();
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
        async fetchStats() {
            const response = await $.get(ROUTES.objectives.stats);
            return response?.data ?? {};
        },

        async fetchSubCompanies() {
            const response = await $.get(ROUTES.objectives.subCompaniesAll);
            return response?.data ?? [];
        },

        async fetchSquads() {
            const response = await $.get(ROUTES.objectives.squadsAll);
            return response?.data ?? [];
        },
        
        async fetchEmployees() {
            const response = await $.get(ROUTES.objectives.employeesAll);
            return response?.data ?? [];
        },
        
        async fetchProgress(id) {
            const response = await $.get(ROUTES.objectives.progress(id));
            return response?.data ?? [];
        },

        async createObjective(formData) {
            return $.ajax({
                url: ROUTES.objectives.store,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
            });
        },

        async deleteObjective(id) {
            return $.ajax({
                url: ROUTES.objectives.destroy(id),
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
                '#summary-total': stats.total ?? 0,
                '#summary-in-progress': stats.in_progress ?? 0,
                '#summary-overdue': stats.overdue ?? 0,
                '#summary-completed': stats.completed ?? 0,
            });

            Utils.setLastUpdated(stats.last_update ?? null);
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
        async loadScopeTargets(scopeType) {
            const $scopeIdSelect = $('#form-objective-scope-id');
            const $notice = $('#manager-squad-notice');
            $scopeIdSelect.empty().append('<option value=""></option>');
            
            if (scopeType === 'company') {
                return;
            }

            try {
                let data = [];
                let defaultText = '';
                
                if (scopeType === 'sub_company') {
                    data = await ApiManager.fetchSubCompanies();
                    defaultText = TRANSLATION.options.subCompany;
                    $notice.addClass('d-none');
                } else if (scopeType === 'squad') {
                    data = await ApiManager.fetchSquads();
                    defaultText = TRANSLATION.options.squad;
                    $notice.removeClass('d-none');
                } else if (scopeType === 'individual') {
                    data = await ApiManager.fetchEmployees();
                    defaultText = 'Select Employee';
                    $notice.addClass('d-none');
                }

                Utils.buildSelect2Options(
                    '#form-objective-scope-id',
                    data.map((item) => ({
                        value: item.id,
                        label: item.name,
                    })),
                    defaultText,
                );
            } catch (error) {
                Utils.showAlert('danger', TRANSLATION.error.loadTargets);
            }
        }
    };

    const ViewProgressManager = {
        init() {
            const self = this;
            $(document).on('click', '.viewObjectiveProgressBtn', async function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                
                $('#progress-objective-name').text(name);
                $('#progress-table-body').empty();
                $('#progress-loading-placeholder').removeClass('d-none');
                $('#progress-empty-placeholder').addClass('d-none');
                
                const modal = new bootstrap.Modal(document.getElementById('objectiveProgressModal'));
                modal.show();
                
                try {
                    const data = await ApiManager.fetchProgress(id);
                    self.render(data);
                } catch (error) {
                    Utils.showAlert('danger', 'Unable to load progress data.');
                    modal.hide();
                } finally {
                    $('#progress-loading-placeholder').addClass('d-none');
                }
            });
        },
        
        render(data) {
            const $body = $('#progress-table-body');
            $body.empty();
            
            if (!data || data.length === 0) {
                $('#progress-empty-placeholder').removeClass('d-none');
                return;
            }
            
            data.forEach(item => {
                let badgeColor = 'secondary';
                if (item.status_raw === 'completed') badgeColor = 'success';
                if (item.status_raw === 'in_progress') badgeColor = 'info';
                
                const row = `
                    <tr>
                        <td class="px-4 py-3 fw-bold text-dark">${item.employee_name}</td>
                        <td><span class="badge bg-${badgeColor}">${item.status}</span></td>
                        <td><div class="text-wrap" style="max-width: 300px;">${item.notes}</div></td>
                        <td class="px-4 py-3 text-end text-muted small">${item.completed_at}</td>
                    </tr>
                `;
                $body.append(row);
            });
        }
    };

    const FormManager = {
        reset() {
            DOM.form.reset();

            $('#form-objective-priority').val('medium').trigger('change.select2');
            
            // Re-trigger auth defaults if applicable
            const $scopeTypeOpts = $('#form-objective-scope-type option');
            if ($scopeTypeOpts.length > 0) {
               $('#form-objective-scope-type').val($scopeTypeOpts[0].value).trigger('change.select2');
            }
            
            $('#form-objective-upload-type').val('').trigger('change.select2');
            
            Utils.clearSelect2('#form-objective-scope-id');
            $('#scope-id-container').addClass('d-none');
            
            $('#file-upload-container').addClass('d-none');
            $('#file-url-container').addClass('d-none');
            
            Utils.clearFormErrors('#objective-form-errors');
        },

        getSubmitPayload() {
            return new FormData(DOM.form);
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
                formSelector: '#objectives-search-form',
                onReload: () => {
                    void TableManager.reload();
                },
                resetButtonSelector: '#btn-reset-objective-filters',
                onReset: () => {
                    Utils.clearSelect2('#filter-objective-priority');
                    Utils.clearSelect2('#filter-objective-scope');
                },
            });
        },
    };

    const ModalFlowManager = {
        async prepareCreate() {
            FormManager.reset();
            $('#objective-modal-title').text(TRANSLATION.modal.createTitle);
            ScrollManager.update(DOM.formModal);
        },

        initLifecycleEvents() {
            DOM.formModal.addEventListener('shown.bs.modal', () => {
                ScrollManager.init(DOM.formModal);
                ScrollManager.update(DOM.formModal);
            });

            DOM.formModal.addEventListener('show.bs.modal', async () => {
                await this.prepareCreate();
            });

            DOM.formModal.addEventListener('hidden.bs.modal', () => {
                Utils.setFormLoading('#objective-form', false);
                Utils.clearFormErrors('#objective-form-errors');
                ScrollManager.destroy(DOM.formModal);
            });
            
            $('#form-objective-scope-type').on('change', function() {
                const type = $(this).val();
                if (type === 'company') {
                    $('#scope-id-container').addClass('d-none');
                    $('#form-objective-scope-id').val('').trigger('change.select2');
                } else {
                    $('#scope-id-container').removeClass('d-none');
                    OptionsManager.loadScopeTargets(type);
                }
            });
            
            $('#form-objective-upload-type').on('change', function() {
                const type = $(this).val();
                $('#file-upload-container').toggleClass('d-none', type !== 'file');
                $('#file-url-container').toggleClass('d-none', type !== 'url');
            });
        },
    };

    const DeleteManager = {
        init() {
            $(document).on('click', '.deleteObjectiveBtn', async function() {
                const id = $(this).data('id');
                const name = $(this).data('name');

                if (!id) {
                    Utils.showAlert('danger', TRANSLATION.error.missingObjective);
                    return;
                }

                const confirmed = await Utils.confirmAction({
                    title: `${TRANSLATION.confirm.delete.titlePrefix}${name}?`,
                    confirmText: TRANSLATION.confirm.delete.confirmText,
                });

                if (!confirmed) return;

                try {
                    const response = await ApiManager.deleteObjective(id);
                    Utils.showAlert('success', response.message ?? TRANSLATION.success.delete);
                    await UIRefreshManager.refreshAll();
                } catch (error) {
                    Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.deleteObjective);
                }
            });
        },
    };

    const FormSubmissionManager = {
        init() {
            $('#objective-form').on('submit', async function(event) {
                event.preventDefault();

                const $submitBtn = Utils.getFormSubmitBtn(this);
                
                if (!this.checkValidity()) {
                    event.stopPropagation();
                    this.classList.add('was-validated');
                    return;
                }
                
                Utils.clearFormErrors('#objective-form-errors');
                const formData = FormManager.getSubmitPayload();
                Utils.setBtnLoading($submitBtn, true);

                try {
                    const response = await ApiManager.createObjective(formData);
                    ScrollManager.hideModal(DOM.formModal);
                    Utils.showAlert('success', response.message ?? TRANSLATION.success.saveCreate);
                    await UIRefreshManager.refreshAll();
                } catch (error) {
                    if (error.status === 422 && error.responseJSON?.errors) {
                        Utils.showFormErrors('#objective-form-errors', error.responseJSON.errors);
                        return;
                    }
                    Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.saveObjective);
                } finally {
                    Utils.setBtnLoading($submitBtn, false);
                }
            });
        },
    };

    const ObjectiveApp = {
        initPlugins() {
            Utils.initSelect2('.select2-filter');
            Utils.initSelect2('.select2-init', $('#objectiveFormModal'));
        },

        initEvents() {
            FilterManager.init();
            ModalFlowManager.initLifecycleEvents();
            DeleteManager.init();
            ViewProgressManager.init();
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

    ObjectiveApp.init();
}
