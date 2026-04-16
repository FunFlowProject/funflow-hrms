/**
 * profit/index.js
 * Structured page controller for Profit Management.
 */

'use strict';

import * as helpers from '../helpers.js';

export function initProfitPage() {
    const pageNode = document.getElementById('profit-page');

    if (!pageNode || !window.$ || !window.bootstrap || !window.Swal) {
        return;
    }

    const $ = window.$;
    const Utils = helpers;

    const ROUTES = {
        profit: Utils.createEndpoints(pageNode.dataset),
    };

    ROUTES.profit.approveWithdrawal = (id) => Utils.resolveUrl(pageNode.dataset.approveUrlTemplate, id);
    ROUTES.profit.rejectWithdrawal = (id) => Utils.resolveUrl(pageNode.dataset.rejectUrlTemplate, id);

    const TRANSLATION = {
        error: {
            loadOptions: 'Unable to load profit options.',
            loadEmployees: 'Unable to load employees.',
            loadWithdrawals: 'Unable to load withdrawal requests.',
            distributeProfit: 'Unable to distribute profit.',
            approveWithdrawal: 'Unable to approve withdrawal request.',
            rejectWithdrawal: 'Unable to reject withdrawal request.',
            missingAmount: 'Please enter a valid amount.',
            missingEmployees: 'Please select at least one employee.',
        },
        success: {
            distributeProfit: 'Profit distributed successfully.',
            approveWithdrawal: 'Withdrawal request approved successfully.',
            rejectWithdrawal: 'Withdrawal request rejected successfully.',
        },
        confirm: {
            distributeTitle: 'Distribute profit to :count employees?',
            approveTitle: 'Approve withdrawal for :employee?',
            rejectTitle: 'Reject withdrawal for :employee?',
        },
    };

    const DOM = {
        employeeSearch: document.getElementById('profit-employee-search'),
        squadSelect: document.getElementById('profit-squad-select'),
        selectAllCheckbox: document.getElementById('profit-select-all-employees'),
        selectAllBtn: document.getElementById('btn-select-all-employees'),
        selectSquadBtn: document.getElementById('btn-select-squad-employees'),
        clearSelectionBtn: document.getElementById('btn-clear-employee-selection'),
        distributeForm: document.getElementById('profit-distribute-form'),
        amountInput: document.getElementById('profit-distribution-amount'),
        selectedCount: document.getElementById('profit-selected-count'),
        selectedAmount: document.getElementById('profit-selected-amount'),
        totalAmount: document.getElementById('profit-total-amount'),
        employeesTbody: document.getElementById('profit-employees-tbody'),
        withdrawalSearchForm: document.getElementById('profit-withdrawal-search-form'),
    };

    if (!DOM.employeeSearch || !DOM.squadSelect || !DOM.selectAllCheckbox || !DOM.distributeForm || !DOM.employeesTbody) {
        return;
    }

    const STATE = {
        employees: [],
        squads: [],
        selectedEmployeeIds: new Set(),
        withdrawalTable: null,
        searchTerm: '',
        selectedSquadId: '',
    };

    const formatMoney = (amount) => {
        const number = Number(amount ?? 0);
        return `USD ${number.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })}`;
    };

    const escapeHtml = (value) => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    const ApiManager = {
        async fetchOptions() {
            const response = await $.get(ROUTES.profit.options);
            return response?.data ?? {};
        },

        async fetchEmployees() {
            const response = await $.get(ROUTES.profit.employees);
            return response?.data ?? [];
        },

        async distributeProfit(payload) {
            return $.ajax({
                url: ROUTES.profit.distribute,
                method: 'POST',
                data: payload,
            });
        },

        async approveWithdrawal(withdrawalRequestId) {
            return $.ajax({
                url: ROUTES.profit.approveWithdrawal(withdrawalRequestId),
                method: 'POST',
            });
        },

        async rejectWithdrawal(withdrawalRequestId, reason = '') {
            return $.ajax({
                url: ROUTES.profit.rejectWithdrawal(withdrawalRequestId),
                method: 'POST',
                data: {
                    reason,
                },
            });
        },
    };

    const EmployeesManager = {
        setLoading(isLoading) {
            DOM.employeesTbody.innerHTML = isLoading
                ? '<tr><td colspan="6" class="text-center text-secondary py-4">Loading employees...</td></tr>'
                : DOM.employeesTbody.innerHTML;
        },

        filteredEmployees() {
            const search = String(DOM.employeeSearch.value ?? '').trim().toLowerCase();
            const squadId = String(DOM.squadSelect.value ?? '').trim();

            return STATE.employees.filter((employee) => {
                const matchesSearch = !search
                    || String(employee.full_name ?? '').toLowerCase().includes(search)
                    || String(employee.email ?? '').toLowerCase().includes(search);

                const matchesSquad = !squadId || String(employee.squad_id ?? '') === squadId;

                return matchesSearch && matchesSquad;
            });
        },

        render() {
            const employees = this.filteredEmployees();

            if (!employees.length) {
                DOM.employeesTbody.innerHTML = '<tr><td colspan="6" class="text-center text-secondary py-4">No employees found.</td></tr>';
                this.syncHeaderCheckbox();
                DistributionManager.updateSummary();
                return;
            }

            DOM.employeesTbody.innerHTML = employees.map((employee) => {
                const employeeId = String(employee.id);
                const isSelected = STATE.selectedEmployeeIds.has(employeeId);

                return `
                    <tr data-employee-id="${employeeId}" data-squad-id="${escapeHtml(employee.squad_id ?? '')}">
                        <td class="align-middle">
                            <input type="checkbox" class="form-check-input profit-employee-checkbox" data-id="${employeeId}" ${isSelected ? 'checked' : ''}>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">${escapeHtml(employee.full_name ?? '-')}</div>
                            <div class="text-secondary small">${escapeHtml(employee.email ?? '-')}</div>
                        </td>
                        <td>${escapeHtml(employee.squad_name ?? '-')}</td>
                        <td>${escapeHtml(employee.sub_company_name ?? '-')}</td>
                        <td class="fw-semibold text-success">${escapeHtml(employee.balance_formatted ?? formatMoney(employee.balance ?? 0))}</td>
                        <td><span class="badge bg-light text-dark rounded-pill px-3">${escapeHtml(employee.status_label ?? '-')}</span></td>
                    </tr>
                `;
            }).join('');

            this.syncHeaderCheckbox();
            DistributionManager.updateSummary();
        },

        syncHeaderCheckbox() {
            const visibleEmployees = this.filteredEmployees();
            const visibleIds = visibleEmployees.map((employee) => String(employee.id));
            const selectedVisibleCount = visibleIds.filter((id) => STATE.selectedEmployeeIds.has(id)).length;

            DOM.selectAllCheckbox.checked = visibleIds.length > 0 && selectedVisibleCount === visibleIds.length;
            DOM.selectAllCheckbox.indeterminate = selectedVisibleCount > 0 && selectedVisibleCount < visibleIds.length;
        },

        selectAllVisible() {
            this.filteredEmployees().forEach((employee) => {
                STATE.selectedEmployeeIds.add(String(employee.id));
            });

            this.render();
        },

        selectAllEmployees() {
            STATE.employees.forEach((employee) => {
                STATE.selectedEmployeeIds.add(String(employee.id));
            });

            this.render();
        },

        selectSquadMembers() {
            const squadId = String(DOM.squadSelect.value ?? '').trim();

            if (!squadId) {
                Utils.showAlert('warning', 'Please choose a squad or team first.');
                return;
            }

            STATE.employees
                .filter((employee) => String(employee.squad_id ?? '') === squadId)
                .forEach((employee) => {
                    STATE.selectedEmployeeIds.add(String(employee.id));
                });

            this.render();
        },

        clearSelection() {
            STATE.selectedEmployeeIds.clear();
            this.render();
        },

        getSelectedEmployeeIds() {
            return Array.from(STATE.selectedEmployeeIds);
        },

        async load() {
            this.setLoading(true);

            try {
                const [options, employees] = await Promise.all([
                    ApiManager.fetchOptions(),
                    ApiManager.fetchEmployees(),
                ]);

                STATE.squads = options.squads ?? [];
                STATE.employees = employees ?? [];

                Utils.buildSelect2Options(
                    '#profit-squad-select',
                    STATE.squads.map((squad) => ({
                        value: squad.id,
                        label: squad.label ?? squad.name ?? '-',
                    })),
                    'All Squads',
                );

                DOM.squadSelect.value = '';
                DOM.squadSelect.dispatchEvent(new Event('change', { bubbles: true }));

                this.render();
            } catch (error) {
                Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.loadEmployees);
            } finally {
                this.setLoading(false);
            }
        },
    };

    const WithdrawalManager = {
        initTable() {
            STATE.withdrawalTable = Utils.createDataTable(
                '#profit-withdrawal-requests-table',
                ROUTES.profit.withdrawalRequests,
                [
                    { data: 'employee_name', name: 'employee_name' },
                    { data: 'amount', name: 'amount' },
                    { data: 'request_date', name: 'created_at' },
                    { data: 'status', name: 'status' },
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
                        url: ROUTES.profit.withdrawalRequests,
                        data: (request) => {
                            request.search_employee = String($('#profit-withdrawal-search-employee').val() ?? '').trim();
                            request.search_status = String($('#profit-withdrawal-search-status').val() ?? '').trim();
                            request.search_from = String($('#profit-withdrawal-search-from').val() ?? '').trim();
                            request.search_to = String($('#profit-withdrawal-search-to').val() ?? '').trim();
                        },
                    },
                },
            );
        },

        async approve(withdrawalRequestId, employeeName, amount) {
            const confirmed = await Utils.confirmAction({
                title: TRANSLATION.confirm.approveTitle.replace(':employee', employeeName || 'this employee'),
                text: `Approve ${amount} for ${employeeName || 'this employee'}?`,
                confirmText: 'Yes, approve',
            });

            if (!confirmed) {
                return;
            }

            try {
                const response = await ApiManager.approveWithdrawal(withdrawalRequestId);
                Utils.showAlert('success', response.message ?? TRANSLATION.success.approveWithdrawal);
                await Utils.reloadDataTable(STATE.withdrawalTable);
            } catch (error) {
                Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.approveWithdrawal);
            }
        },

        async reject(withdrawalRequestId, employeeName, amount) {
            const result = await window.Swal.fire({
                icon: 'warning',
                title: TRANSLATION.confirm.rejectTitle.replace(':employee', employeeName || 'this employee'),
                text: `Reject ${amount} for ${employeeName || 'this employee'}?`,
                input: 'textarea',
                inputLabel: 'Rejection Reason',
                inputPlaceholder: 'Optional reason for rejection',
                showCancelButton: true,
                confirmButtonText: 'Yes, reject',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
            });

            if (!result.isConfirmed) {
                return;
            }

            try {
                const response = await ApiManager.rejectWithdrawal(withdrawalRequestId, String(result.value ?? '').trim());
                Utils.showAlert('success', response.message ?? TRANSLATION.success.rejectWithdrawal);
                await Utils.reloadDataTable(STATE.withdrawalTable);
            } catch (error) {
                Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.rejectWithdrawal);
            }
        },
    };

    const DistributionManager = {
        updateSummary() {
            const selectedCount = STATE.selectedEmployeeIds.size;
            const amount = Number(DOM.amountInput.value || 0);

            DOM.selectedCount.textContent = String(selectedCount);
            DOM.selectedAmount.textContent = formatMoney(amount);
            DOM.totalAmount.textContent = formatMoney(selectedCount * amount);
        },

        async submit(event) {
            event.preventDefault();

            const selectedEmployeeIds = EmployeesManager.getSelectedEmployeeIds();
            const amount = Number(DOM.amountInput.value || 0);

            if (!selectedEmployeeIds.length) {
                Utils.showAlert('warning', TRANSLATION.error.missingEmployees);
                return;
            }

            if (!Number.isFinite(amount) || amount <= 0) {
                Utils.showAlert('warning', TRANSLATION.error.missingAmount);
                return;
            }

            const confirmed = await Utils.confirmAction({
                title: TRANSLATION.confirm.distributeTitle.replace(':count', String(selectedEmployeeIds.length)),
                text: `Distribute ${formatMoney(amount)} to ${selectedEmployeeIds.length} employees?`,
                confirmText: 'Yes, distribute',
            });

            if (!confirmed) {
                return;
            }

            try {
                const response = await ApiManager.distributeProfit({
                    user_ids: selectedEmployeeIds,
                    amount,
                });

                Utils.showAlert('success', response.message ?? TRANSLATION.success.distributeProfit);
                STATE.selectedEmployeeIds.clear();
                DOM.amountInput.value = '';
                EmployeesManager.render();
                DistributionManager.updateSummary();
            } catch (error) {
                Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.distributeProfit);
            }
        },
    };

    const FilterManager = {
        init() {
            Utils.initSelect2('#profit-squad-select');
            Utils.initSelect2('#profit-withdrawal-search-status');

            DOM.employeeSearch.addEventListener('input', Utils.debounce(() => {
                EmployeesManager.render();
            }, 200));

            DOM.squadSelect.addEventListener('change', () => {
                EmployeesManager.render();
            });

            DOM.selectAllCheckbox.addEventListener('change', () => {
                if (DOM.selectAllCheckbox.checked) {
                    EmployeesManager.selectAllVisible();
                } else {
                    EmployeesManager.filteredEmployees().forEach((employee) => {
                        STATE.selectedEmployeeIds.delete(String(employee.id));
                    });
                    EmployeesManager.render();
                }
            });

            DOM.selectAllBtn.addEventListener('click', () => {
                EmployeesManager.selectAllEmployees();
            });

            DOM.selectSquadBtn.addEventListener('click', () => {
                EmployeesManager.selectSquadMembers();
            });

            DOM.clearSelectionBtn.addEventListener('click', () => {
                EmployeesManager.clearSelection();
            });

            DOM.amountInput.addEventListener('input', () => {
                DistributionManager.updateSummary();
            });

            Utils.initRealtimeFilters({
                formSelector: '#profit-withdrawal-search-form',
                onReload: () => Utils.reloadDataTable(STATE.withdrawalTable),
                resetButtonSelector: '#btn-reset-profit-withdrawal-filters',
                onReset: () => {
                    DOM.withdrawalSearchForm.reset();
                    $('#profit-withdrawal-search-status').val('').trigger('change.select2');
                },
            });

            DOM.distributeForm.addEventListener('submit', (event) => {
                DistributionManager.submit(event);
            });
        },
    };

    const ActionManager = {
        init() {
            $(document).on('change', '.profit-employee-checkbox', function () {
                const employeeId = String($(this).data('id') ?? '');

                if (!employeeId) {
                    return;
                }

                if (this.checked) {
                    STATE.selectedEmployeeIds.add(employeeId);
                } else {
                    STATE.selectedEmployeeIds.delete(employeeId);
                }

                EmployeesManager.syncHeaderCheckbox();
                DistributionManager.updateSummary();
            });

            $(document).on('click', '.btn-approve-withdrawal', function () {
                const withdrawalRequestId = String($(this).data('id') ?? '');
                const employeeName = String($(this).data('employee') ?? '');
                const amount = String($(this).data('amount') ?? '');

                if (!withdrawalRequestId) {
                    return;
                }

                WithdrawalManager.approve(withdrawalRequestId, employeeName, amount);
            });

            $(document).on('click', '.btn-reject-withdrawal', function () {
                const withdrawalRequestId = String($(this).data('id') ?? '');
                const employeeName = String($(this).data('employee') ?? '');
                const amount = String($(this).data('amount') ?? '');

                if (!withdrawalRequestId) {
                    return;
                }

                WithdrawalManager.reject(withdrawalRequestId, employeeName, amount);
            });
        },
    };

    const App = {
        async init() {
            FilterManager.init();
            ActionManager.init();
            WithdrawalManager.initTable();
            await EmployeesManager.load();
            DistributionManager.updateSummary();
        },
    };

    App.init();
}
