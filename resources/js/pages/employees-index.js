/**
 * employees/index.js
 * ─────────────────────────────────────────────────────────────
 * Structured page controller for Employees listing:
 *   - Summary stat cards
 *   - Server-side DataTable
 *   - Create / Edit / View / Delete via Bootstrap modals + AJAX
 * ─────────────────────────────────────────────────────────────
 */

'use strict';

import * as helpers from '../helpers.js';

export function initEmployeesPage() {
    const pageNode = document.getElementById('employees-page');

    if (!pageNode || !window.$ || !window.bootstrap || !window.Swal || !window.flatpickr) {
        return;
    }

    const $ = window.$;

    const Utils = helpers;

    // ===========================
    // ROUTES CONSTANTS
    // ===========================
    const ROUTES = {
        employees: Utils.createEndpoints(pageNode.dataset, {
            subCompaniesAll: 'subCompaniesAllUrl',
            squadsAll: 'squadsAllUrl',
        }),
    };

    ROUTES.employees.moveToOnboarding = (id) => Utils.resolveUrl(pageNode.dataset.onboardingUrlTemplate, id);
    ROUTES.employees.confirmJoin = (id) => Utils.resolveUrl(pageNode.dataset.confirmJoinUrlTemplate, id);

    // ===========================
    // TRANSLATION CONSTANTS
    // ===========================
    const TRANSLATION = {
        error: {
            missingEmployee: 'Unable to identify employee.',
            missingEmployeeForEdit: 'Unable to identify employee for editing.',
            missingEmployeeForView: 'Unable to identify employee details.',
            missingAssignmentPair: 'Please select both Sub-Company and Hierarchy for worker assignment, or leave both empty.',
            loadStats: 'Unable to load employee statistics.',
            loadOptions: 'Unable to load employee options.',
            loadEmployeeEdit: 'Unable to load employee for editing.',
            loadEmployeeView: 'Unable to load details.',
            saveEmployee: 'Unable to save employee.',
            deleteEmployee: 'Unable to delete.',
            transitionEmployeeStatus: 'Unable to update employee status.',
        },
        success: {
            saveCreate: 'Created successfully.',
            saveUpdate: 'Updated successfully.',
            delete: 'Deleted successfully.',
            moveToOnboarding: 'Employee moved to onboarding.',
            confirmJoin: 'Employee marked as joined.',
        },
        confirm: {
            delete: {
                titlePrefix: 'Delete ',
                confirmText: 'Yes, delete',
            },
            onboarding: {
                titlePrefix: 'Move ',
                titleSuffix: ' to onboarding?',
                confirmText: 'Yes, move',
            },
            join: {
                titlePrefix: 'Confirm joining for ',
                titleSuffix: '?',
                confirmText: 'Yes, confirm',
            },
        },
        modal: {
            createTitle: 'Create Employee',
            editTitle: 'Edit Employee',
        },
        options: {
            contract: 'Select Contract',
            role: 'Select Role',
            subCompany: 'Select Sub-Company',
            hierarchy: 'Select Hierarchy',
            noSquad: 'No Squad',
        },
        loading: {
            text: 'Loading...',
        },
    };

    // ===========================
    // DOM REFERENCES
    // ===========================
    const DOM = {
        formModal: document.getElementById('employeeFormModal'),
        viewModal: document.getElementById('employeeViewModal'),
        form: document.getElementById('employee-form'),
    };

    if (!DOM.formModal || !DOM.viewModal || !DOM.form) {
        return;
    }

    // ===========================
    // STATE
    // ===========================
    const STATE = {
        statsSelectors: ['#summary-pending', '#summary-onboarding', '#summary-joined', '#summary-terminated'],
    };

    // ===========================
    // MODAL SCROLL MANAGER
    // ===========================
    const ScrollManager = Utils.createModalManager();

    // ===========================
    // DATA TABLE MANAGER
    // ===========================
    const TableManager = {
        table: null,

        init() {
            this.table = Utils.createDataTable(
                '#employees-table',
                ROUTES.employees.datatable,
                [
                    { data: 'username', name: 'username' },
                    { data: 'email', name: 'email' },
                    { data: 'phone', name: 'phone_number' },
                    { data: 'contract_type', name: 'contract_type' },
                    { data: 'system_role', name: 'system_role' },
                    { data: 'status', name: 'status' },
                    { data: 'hire_date', name: 'hire_date' },
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
                        url: ROUTES.employees.datatable,
                        data: (request) => {
                            const searchUsername = String($('#filter-employee-username').val() ?? '').trim();
                            const searchFullName = String($('#filter-employee-full-name').val() ?? '').trim();
                            const searchEmail = String($('#filter-employee-email').val() ?? '').trim();
                            const searchStatus = String($('#filter-employee-status').val() ?? '').trim();
                            const searchRole = String($('#filter-employee-role').val() ?? '').trim();

                            request.search_username = searchUsername;
                            request.search_full_name = searchFullName;
                            request.search_email = searchEmail;

                            if (searchStatus !== '') {
                                request.search_status = searchStatus;
                            }

                            if (searchRole !== '') {
                                request.search_role = searchRole;
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

    // ===========================
    // API MANAGER
    // ===========================
    const ApiManager = {
        async fetchEmployee(employeeId) {
            const response = await $.get(ROUTES.employees.show(employeeId));

            return response?.data;
        },

        async fetchStats() {
            const response = await $.get(ROUTES.employees.stats);

            return response?.data ?? {};
        },

        async fetchOptions() {
            const [optionsRes, subCompaniesRes, squadsRes] = await Promise.all([
                $.get(ROUTES.employees.options),
                $.get(ROUTES.employees.subCompaniesAll),
                $.get(ROUTES.employees.squadsAll),
            ]);

            return {
                options: optionsRes?.data ?? {},
                subCompanies: subCompaniesRes?.data ?? [],
                squads: squadsRes?.data ?? [],
            };
        },

        async fetchSquads() {
            const response = await $.get(ROUTES.employees.squadsAll);

            return response?.data ?? [];
        },

        async createEmployee(payload) {
            return $.ajax({
                url: ROUTES.employees.store,
                method: 'POST',
                data: $.param(payload),
            });
        },

        async updateEmployee(employeeId, payload) {
            return $.ajax({
                url: ROUTES.employees.update(employeeId),
                method: 'POST',
                data: $.param([...payload, { name: '_method', value: 'PUT' }]),
            });
        },

        async deleteEmployee(employeeId) {
            return $.ajax({
                url: ROUTES.employees.destroy(employeeId),
                method: 'POST',
                data: { _method: 'DELETE' },
            });
        },

        async moveToOnboarding(employeeId) {
            return $.ajax({
                url: ROUTES.employees.moveToOnboarding(employeeId),
                method: 'POST',
            });
        },

        async confirmJoin(employeeId) {
            return $.ajax({
                url: ROUTES.employees.confirmJoin(employeeId),
                method: 'POST',
            });
        },
    };

    // ===========================
    // STATS MANAGER
    // ===========================
    const StatsManager = {
        setLoading(isLoading) {
            Utils.setStatsLoading({
                valueSelectors: STATE.statsSelectors,
                isLoading,
            });
        },

        setCards(stats = {}) {
            Utils.setStatCards({
                '#summary-pending': stats.pendingEmployees?.count ?? 0,
                '#summary-onboarding': stats.onboardingEmployees?.count ?? 0,
                '#summary-joined': stats.joinedEmployees?.count ?? 0,
                '#summary-terminated': stats.terminatedEmployees?.count ?? 0,
            });

            Utils.setLastUpdated(stats.employees?.lastUpdateTime ?? null);
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

    // ===========================
    // OPTIONS MANAGER
    // ===========================
    const OptionsManager = {
        clearSquadOptions() {
            const squadSelect = $('#form-squad');

            squadSelect.empty().append(new Option(TRANSLATION.options.noSquad, ''));
            squadSelect.val('').trigger('change.select2');
        },

        renderSquadOptions(squads, selectedValue = null) {
            const subCompanyId = String($('#form-sub-company').val() ?? '');
            const squadSelect = $('#form-squad');
            const currentValue = selectedValue ?? squadSelect.val();

            squadSelect.empty().append(new Option(TRANSLATION.options.noSquad, ''));

            squads
                .filter((squad) => !subCompanyId || String(squad.sub_company_id) === subCompanyId)
                .forEach((squad) => squadSelect.append(new Option(squad.name, squad.id)));

            squadSelect.val(currentValue).trigger('change.select2');
        },

        async refreshSquadOptions(selectedValue = null) {
            try {
                const squads = await ApiManager.fetchSquads();
                this.renderSquadOptions(squads, selectedValue);
            } catch {
                this.clearSquadOptions();
            }
        },

        renderSelects({ options, subCompanies }) {
            Utils.buildSelect2Options(
                '#form-contract-type',
                (options.contractTypes ?? []).map((value) => ({ value, label: Utils.humanize(value) })),
                TRANSLATION.options.contract,
            );

            Utils.buildSelect2Options(
                '#form-system-role',
                (options.systemRoles ?? []).map((value) => ({ value, label: Utils.humanize(value) })),
                TRANSLATION.options.role,
            );

            Utils.buildSelect2Options(
                '#form-sub-company',
                subCompanies.map((company) => ({ value: company.id, label: company.name })),
                TRANSLATION.options.subCompany,
            );

            Utils.buildSelect2Options(
                '#form-hierarchy',
                (options.hierarchies ?? []).map((hierarchy) => ({
                    value: hierarchy.id,
                    label: `${String(hierarchy.type ?? '').toUpperCase()} L${hierarchy.level} - ${hierarchy.title}`,
                })),
                TRANSLATION.options.hierarchy,
            );
        },

        async load() {
            try {
                const { options, subCompanies, squads } = await ApiManager.fetchOptions();

                this.renderSelects({ options, subCompanies });
                this.renderSquadOptions(squads);
            } catch (error) {
                Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.loadOptions);
            }
        },
    };

    // ===========================
    // FORM MANAGER
    // ===========================
    const FormManager = {
        reset() {
            DOM.form.reset();

            $('#employee_id').val('');
            Utils.clearSelect2('#form-sub-company');
            Utils.clearSelect2('#form-hierarchy');
            Utils.getFlatpickrInstance('#form-date-of-birth')?.clear();
            Utils.getFlatpickrInstance('#form-hire-date')?.clear();

            OptionsManager.clearSquadOptions();
            Utils.clearFormErrors('#employee-form-errors');
            this.toggleAssignmentSection();
        },

        toggleAssignmentSection() {
            const role = String($('#form-system-role').val() ?? '').toLowerCase().trim();
            const $section = $('#primary-assignment-fields');

            if (role === 'employee') {
                $section.removeClass('d-none');
            } else {
                $section.addClass('d-none');
            }
        },

        async populate(employee) {
            $('#employee_id').val(employee.id);
            $('#form-full-name').val(employee.full_name ?? employee.name ?? '');
            $('#form-email').val(employee.email ?? '');
            $('#form-phone-number').val(employee.phone_number ?? employee.phone ?? '');

            if (employee.date_of_birth) {
                Utils.getFlatpickrInstance('#form-date-of-birth')?.setDate(employee.date_of_birth);
            }

            if (employee.hire_date) {
                Utils.getFlatpickrInstance('#form-hire-date')?.setDate(employee.hire_date);
            }

            $('#form-contract-type').val(employee.contract_type ?? 'full-time').trigger('change.select2');
            $('#form-system-role').val(employee.system_role ?? 'employee').trigger('change.select2');

            this.toggleAssignmentSection();

            const assignment = (employee.employee_assignments ?? employee.assignments ?? [])[0];

            $('#form-sub-company').val(assignment?.sub_company_id ?? '').trigger('change.select2');

            await OptionsManager.refreshSquadOptions(assignment?.squad_id ?? '');

            $('#form-hierarchy')
                .val(assignment?.hierarchy_id ?? assignment?.assignment_grade?.id ?? '')
                .trigger('change.select2');
        },

        validateAssignmentPair() {
            const subCompanyId = $('#form-sub-company').val();
            const hierarchyId = $('#form-hierarchy').val();

            if ((subCompanyId && !hierarchyId) || (!subCompanyId && hierarchyId)) {
                Utils.showAlert('danger', TRANSLATION.error.missingAssignmentPair);

                return false;
            }

            return true;
        },

        getSubmitPayload() {
            const id = $('#employee_id').val();
            const isEdit = Boolean(id);
            const subCompanyId = $('#form-sub-company').val();
            const hierarchyId = $('#form-hierarchy').val();
            let payload = $('#employee-form').serializeArray();

            if (!subCompanyId && !hierarchyId) {
                payload = Utils.filterPayload(payload, ['assignments[0]']);
            }

            return {
                id,
                isEdit,
                payload,
            };
        },
    };

    // ===========================
    // VIEW MANAGER
    // ===========================
    const ViewManager = {
        setLoading() {
            [
                '#view-username',
                '#view-name',
                '#view-email',
                '#view-phone',
                '#view-contract',
                '#view-role',
                '#view-status',
                '#view-dob',
                '#view-hire-date',
                '#view-assignments',
            ].forEach((selector) => {
                const node = document.querySelector(selector);
                if (node) {
                    node.textContent = TRANSLATION.loading.text;
                }
            });
        },

        populate(employee) {
            $('#view-username').text(employee.username ?? '-');
            $('#view-name').text(employee.full_name ?? employee.name ?? '-');
            $('#view-email').text(employee.email ?? '-');
            $('#view-phone').text(employee.phone_number ?? employee.phone ?? '-');
            $('#view-contract').text(employee.contract_type_label ?? (Utils.humanize(employee.contract_type ?? '') || '-'));
            $('#view-role').text(employee.system_role_label ?? (Utils.humanize(employee.system_role ?? '') || '-'));
            $('#view-status').text(employee.status_label ?? (Utils.humanize(employee.status ?? '') || '-'));
            $('#view-dob').text(employee.date_of_birth_formatted ?? employee.date_of_birth ?? '-');
            $('#view-hire-date').text(employee.hire_date_formatted ?? employee.hire_date ?? '-');

            const assignments = employee.employee_assignments ?? employee.assignments ?? [];
            const assignmentsHtml = assignments.length
                ? assignments
                    .map(
                        (assignment) => `${assignment.sub_company?.name ?? '-'} / ${assignment.squad?.name ?? 'No Squad'} — ${assignment.hierarchy?.title ?? assignment.assignment_grade?.title ?? '-'}`
                    )
                    .join('<br>')
                : '-';

            $('#view-assignments').html(assignmentsHtml);
        },
    };

    // ===========================
    // UI REFRESH MANAGER
    // ===========================
    const UIRefreshManager = {
        async refreshAll() {
            await Utils.refreshTableAndStats({
                table: TableManager.table,
                refreshStats: () => StatsManager.refresh(),
            });
        },
    };

    // ===========================
    // FILTER MANAGER
    // ===========================
    const FilterManager = {
        init() {
            Utils.initRealtimeFilters({
                formSelector: '#employees-search-form',
                onReload: () => {
                    void TableManager.reload();
                },
                resetButtonSelector: '#btn-reset-employee-filters',
                onReset: () => {
                    Utils.clearSelect2('#filter-employee-status');
                    Utils.clearSelect2('#filter-employee-role');
                },
            });
        },
    };

    // ===========================
    // MODAL FLOW MANAGER
    // ===========================
    const ModalFlowManager = {
        async prepareCreate() {
            FormManager.reset();
            await OptionsManager.load();
            $('#employee-modal-title').text(TRANSLATION.modal.createTitle);
            Utils.setFormLoading('#employee-form', false);
            ScrollManager.update(DOM.formModal);
        },

        async prepareEdit(employeeId) {
            FormManager.reset();
            $('#employee-modal-title').text(TRANSLATION.modal.editTitle);
            Utils.setFormLoading('#employee-form', true);

            try {
                await OptionsManager.load();
                const employee = await ApiManager.fetchEmployee(employeeId);
                await FormManager.populate(employee);
            } catch (error) {
                Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.loadEmployeeEdit);
                ScrollManager.hideModal(DOM.formModal);
            } finally {
                Utils.setFormLoading('#employee-form', false);
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

                const isEdit = trigger.dataset.modalMode === 'edit' || trigger.classList.contains('btn-edit-employee');

                if (!isEdit) {
                    await this.prepareCreate();

                    return;
                }

                const employeeId = trigger.dataset.id;

                if (!employeeId) {
                    Utils.showAlert('danger', TRANSLATION.error.missingEmployeeForEdit);
                    event.preventDefault();

                    return;
                }

                await this.prepareEdit(employeeId);
            });

            DOM.formModal.addEventListener('hidden.bs.modal', () => {
                Utils.setFormLoading('#employee-form', false);
                Utils.clearFormErrors('#employee-form-errors');
                ScrollManager.destroy(DOM.formModal);
            });

            DOM.viewModal.addEventListener('shown.bs.modal', () => {
                ScrollManager.init(DOM.viewModal);
                ScrollManager.update(DOM.viewModal);
            });

            DOM.viewModal.addEventListener('show.bs.modal', async (event) => {
                const trigger = event.relatedTarget;
                const employeeId = trigger instanceof HTMLElement ? trigger.dataset.id : null;

                if (!employeeId) {
                    event.preventDefault();
                    Utils.showAlert('danger', TRANSLATION.error.missingEmployeeForView);

                    return;
                }

                ViewManager.setLoading();

                try {
                    const employee = await ApiManager.fetchEmployee(employeeId);
                    ViewManager.populate(employee);
                    ScrollManager.update(DOM.viewModal);
                } catch (error) {
                    Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.loadEmployeeView);
                    ScrollManager.hideModal(DOM.viewModal);
                }
            });

            DOM.viewModal.addEventListener('hidden.bs.modal', () => {
                ScrollManager.destroy(DOM.viewModal);
            });
        },
    };

    // ===========================
    // STATUS TRANSITION MANAGER
    // ===========================
    const StatusTransitionManager = {
        init() {
            $(document).on('click', '.btn-onboard-employee', async function handleOnboardEmployee() {
                const employeeId = $(this).data('id');
                const employeeName = String($(this).data('name') ?? 'this employee');

                if (!employeeId) {
                    Utils.showAlert('danger', TRANSLATION.error.missingEmployee);

                    return;
                }

                const confirmed = await Utils.confirmAction({
                    title: `${TRANSLATION.confirm.onboarding.titlePrefix}${employeeName}${TRANSLATION.confirm.onboarding.titleSuffix}`,
                    confirmText: TRANSLATION.confirm.onboarding.confirmText,
                });

                if (!confirmed) {
                    return;
                }

                try {
                    const response = await ApiManager.moveToOnboarding(employeeId);
                    Utils.showAlert('success', response.message ?? TRANSLATION.success.moveToOnboarding);
                    await UIRefreshManager.refreshAll();
                } catch (error) {
                    Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.transitionEmployeeStatus);
                }
            });

            $(document).on('click', '.btn-join-employee', async function handleConfirmJoinEmployee() {
                const employeeId = $(this).data('id');
                const employeeName = String($(this).data('name') ?? 'this employee');

                if (!employeeId) {
                    Utils.showAlert('danger', TRANSLATION.error.missingEmployee);

                    return;
                }

                const confirmed = await Utils.confirmAction({
                    title: `${TRANSLATION.confirm.join.titlePrefix}${employeeName}${TRANSLATION.confirm.join.titleSuffix}`,
                    confirmText: TRANSLATION.confirm.join.confirmText,
                });

                if (!confirmed) {
                    return;
                }

                try {
                    const response = await ApiManager.confirmJoin(employeeId);
                    Utils.showAlert('success', response.message ?? TRANSLATION.success.confirmJoin);
                    await UIRefreshManager.refreshAll();
                } catch (error) {
                    Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.transitionEmployeeStatus);
                }
            });
        },
    };

    // ===========================
    // DELETE MANAGER
    // ===========================
    const DeleteManager = {
        init() {
            $(document).on('click', '.btn-delete-employee', async function handleDeleteEmployee() {
                const employeeId = $(this).data('id');
                const employeeName = $(this).data('name');

                if (!employeeId) {
                    Utils.showAlert('danger', TRANSLATION.error.missingEmployee);

                    return;
                }

                const confirmed = await Utils.confirmAction({
                    title: `${TRANSLATION.confirm.delete.titlePrefix}${employeeName}?`,
                    confirmText: TRANSLATION.confirm.delete.confirmText,
                });

                if (!confirmed) {
                    return;
                }

                try {
                    const response = await ApiManager.deleteEmployee(employeeId);
                    Utils.showAlert('success', response.message ?? TRANSLATION.success.delete);
                    await UIRefreshManager.refreshAll();
                } catch (error) {
                    Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.deleteEmployee);
                }
            });
        },
    };

    // ===========================
    // FORM SUBMISSION MANAGER
    // ===========================
    const FormSubmissionManager = {
        init() {
            $('#employee-form').on('submit', async function submitEmployeeForm(event) {
                event.preventDefault();

                const $submitBtn = $(this).find('button[type="submit"]');
                Utils.clearFormErrors('#employee-form-errors');

                if (!FormManager.validateAssignmentPair()) {
                    return;
                }

                const { id, isEdit, payload } = FormManager.getSubmitPayload();
                Utils.setBtnLoading($submitBtn, true);

                try {
                    const response = isEdit
                        ? await ApiManager.updateEmployee(id, payload)
                        : await ApiManager.createEmployee(payload);

                    ScrollManager.hideModal(DOM.formModal);
                    Utils.showAlert('success', response.message ?? (isEdit ? TRANSLATION.success.saveUpdate : TRANSLATION.success.saveCreate));
                    await UIRefreshManager.refreshAll();
                } catch (error) {
                    if (error.status === 422 && error.responseJSON?.errors) {
                        Utils.showFormErrors('#employee-form-errors', error.responseJSON.errors);

                        return;
                    }

                    Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.saveEmployee);
                } finally {
                    Utils.setBtnLoading($submitBtn, false);
                }
            });
        },
    };

    // ===========================
    // MAIN APP INITIALIZER
    // ===========================
    const EmployeeApp = {
        initPlugins() {
            Utils.initSelect2('.select2-filter');
            Utils.initSelect2('.select2-init', $('#employeeFormModal'));
            Utils.initFlatpickr('.flatpickr-date');
        },

        initEvents() {
            FilterManager.init();
            $('#form-sub-company').on('change', () => {
                void OptionsManager.refreshSquadOptions();
            });

            $(document).on('change', '#form-system-role', () => {
                FormManager.toggleAssignmentSection();
            });

            ModalFlowManager.initLifecycleEvents();
            StatusTransitionManager.init();
            DeleteManager.init();
            FormSubmissionManager.init();
        },

        initData() {
            StatsManager.setLoading(true);
            StatsManager.refresh();
        },

        init() {
            this.initPlugins();
            TableManager.init();
            this.initEvents();
            this.initData();
        },
    };

    EmployeeApp.init();
}