/**
 * profit/my.js
 * Structured page controller for the employee Profit view.
 */

'use strict';

import * as helpers from '../helpers.js';

export function initMyProfitPage() {
    const pageNode = document.getElementById('my-profit-page');

    if (!pageNode || !window.$ || !window.bootstrap || !window.Swal) {
        return;
    }

    const $ = window.$;
    const Utils = helpers;

    const ROUTES = {
        myProfit: Utils.createEndpoints(pageNode.dataset),
    };

    const TRANSLATION = {
        error: {
            loadBalance: 'Unable to load profit balance.',
            loadTransactions: 'Unable to load profit transactions.',
            loadWithdrawalRequests: 'Unable to load withdrawal requests.',
            requestWithdrawal: 'Unable to submit withdrawal request.',
            missingAmount: 'Please enter a valid withdrawal amount.',
            amountExceedsBalance: 'The requested amount cannot exceed your current profit balance.',
        },
        success: {
            requestWithdrawal: 'Withdrawal request submitted successfully.',
        },
    };

    const DOM = {
        balanceValue: document.getElementById('current-profit-balance'),
        balanceUpdated: document.querySelector('#current-profit-balance')?.closest('.ui-stat-card')?.querySelector('.summary-last-updated'),
        withdrawalModal: document.getElementById('profitWithdrawalModal'),
        withdrawalForm: document.getElementById('profit-withdrawal-form'),
        withdrawalAmount: document.getElementById('profit-withdrawal-amount'),
        withdrawalCurrentBalance: document.getElementById('profit-withdrawal-current-balance'),
        withdrawalSubmit: document.getElementById('profit-withdrawal-submit'),
        modeFull: document.getElementById('profit-withdrawal-mode-full'),
        modePartial: document.getElementById('profit-withdrawal-mode-partial'),
    };

    if (!DOM.balanceValue || !DOM.withdrawalModal || !DOM.withdrawalForm || !DOM.withdrawalAmount) {
        return;
    }

    const STATE = {
        balance: 0,
        balanceFormatted: 'USD 0.00',
        transactionsTable: null,
        withdrawalRequestsTable: null,
        withdrawalMode: 'full',
    };

    const formatMoney = (amount) => {
        const number = Number(amount ?? 0);
        return `USD ${number.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })}`;
    };

    const ApiManager = {
        async fetchBalance() {
            const response = await $.get(ROUTES.myProfit.balance);
            return response?.data ?? {};
        },

        async fetchTransactions() {
            return ROUTES.myProfit.transactions;
        },

        async fetchWithdrawalRequests() {
            return ROUTES.myProfit.withdrawalRequests;
        },

        async requestWithdrawal(amount) {
            return $.ajax({
                url: ROUTES.myProfit.withdraw,
                method: 'POST',
                data: {
                    amount,
                },
            });
        },
    };

    const BalanceManager = {
        setLoading(isLoading) {
            Utils.setStatsLoading({
                valueSelectors: ['#current-profit-balance'],
                isLoading,
            });
        },

        updateCard() {
            Utils.setStatsLoading({
                valueSelectors: ['#current-profit-balance'],
                isLoading: false,
            });
            Utils.setStatCards({
                '#current-profit-balance': STATE.balanceFormatted,
            });

            if (DOM.balanceUpdated) {
                DOM.balanceUpdated.textContent = `Updated ${new Date().toLocaleString()}`;
            }

            DOM.withdrawalCurrentBalance.textContent = STATE.balanceFormatted;
        },

        async refresh() {
            this.setLoading(true);

            try {
                const balance = await ApiManager.fetchBalance();
                STATE.balance = Number(balance.balance ?? 0);
                STATE.balanceFormatted = balance.balance_formatted ?? formatMoney(STATE.balance);
                this.updateCard();
                WithdrawalFormManager.syncAmount();
            } catch (error) {
                Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.loadBalance);
            } finally {
                this.setLoading(false);
            }
        },
    };

    const TableManager = {
        initTransactions() {
            STATE.transactionsTable = Utils.createDataTable(
                '#profit-transactions-table',
                ROUTES.myProfit.transactions,
                [
                    { data: 'created_at', name: 'created_at' },
                    { data: 'type', name: 'type' },
                    { data: 'amount', name: 'amount' },
                    { data: 'balance_after', name: 'balance_after' },
                    { data: 'description', name: 'description' },
                    { data: 'status', name: 'status' },
                    { data: 'performed_by_name', name: 'performed_by_name' },
                ]
            );
        },

        initWithdrawalRequests() {
            STATE.withdrawalRequestsTable = Utils.createDataTable(
                '#my-profit-withdrawal-requests-table',
                ROUTES.myProfit.withdrawalRequests,
                [
                    { data: 'request_date', name: 'created_at' },
                    { data: 'amount', name: 'amount' },
                    { data: 'status', name: 'status' },
                    { data: 'acted_at', name: 'acted_at' },
                    { data: 'rejection_reason', name: 'rejection_reason' },
                ]
            );
        },

        async refreshAll() {
            await Promise.all([
                Utils.reloadDataTable(STATE.transactionsTable),
                Utils.reloadDataTable(STATE.withdrawalRequestsTable),
            ]);
        },
    };

    const WithdrawalFormManager = {
        setMode(mode) {
            STATE.withdrawalMode = mode;

            if (mode === 'full') {
                DOM.withdrawalAmount.readOnly = true;
                DOM.withdrawalAmount.value = STATE.balance > 0 ? STATE.balance.toFixed(2) : '0.00';
            } else {
                DOM.withdrawalAmount.readOnly = false;

                if (!DOM.withdrawalAmount.value || Number(DOM.withdrawalAmount.value) === STATE.balance) {
                    DOM.withdrawalAmount.value = '';
                }
            }
        },

        syncAmount() {
            if (STATE.withdrawalMode === 'full') {
                DOM.withdrawalAmount.value = STATE.balance > 0 ? STATE.balance.toFixed(2) : '0.00';
            } else if (DOM.withdrawalAmount.value && Number(DOM.withdrawalAmount.value) > STATE.balance) {
                DOM.withdrawalAmount.value = STATE.balance > 0 ? STATE.balance.toFixed(2) : '0.00';
            }

            DOM.withdrawalCurrentBalance.textContent = STATE.balanceFormatted;
        },

        reset() {
            DOM.withdrawalForm.reset();
            this.setMode('full');
            this.syncAmount();
        },

        validate() {
            const amount = Number(DOM.withdrawalAmount.value || 0);

            if (!Number.isFinite(amount) || amount <= 0) {
                Utils.showAlert('warning', TRANSLATION.error.missingAmount);
                return false;
            }

            if (amount > STATE.balance) {
                Utils.showAlert('warning', TRANSLATION.error.amountExceedsBalance);
                return false;
            }

            return true;
        },

        async submit(event) {
            event.preventDefault();

            if (!this.validate()) {
                return;
            }

            const amount = Number(DOM.withdrawalAmount.value || 0);
            const confirmed = await Utils.confirmAction({
                title: 'Request withdrawal?',
                text: `Submit a withdrawal request for ${formatMoney(amount)}?`,
                confirmText: 'Yes, request',
            });

            if (!confirmed) {
                return;
            }

            try {
                const response = await ApiManager.requestWithdrawal(amount);
                Utils.showAlert('success', response.message ?? TRANSLATION.success.requestWithdrawal);

                const modalInstance = window.bootstrap.Modal.getInstance(DOM.withdrawalModal)
                    ?? new window.bootstrap.Modal(DOM.withdrawalModal);
                modalInstance.hide();

                this.reset();
                await Promise.all([
                    BalanceManager.refresh(),
                    TableManager.refreshAll(),
                ]);
            } catch (error) {
                Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.requestWithdrawal);
            }
        },

        init() {
            DOM.modeFull.addEventListener('change', () => {
                if (DOM.modeFull.checked) {
                    this.setMode('full');
                }
            });

            DOM.modePartial.addEventListener('change', () => {
                if (DOM.modePartial.checked) {
                    this.setMode('partial');
                }
            });

            DOM.withdrawalAmount.addEventListener('input', () => {
                if (STATE.withdrawalMode === 'partial') {
                    const amount = Number(DOM.withdrawalAmount.value || 0);
                    if (Number.isFinite(amount) && amount > STATE.balance) {
                        DOM.withdrawalAmount.value = STATE.balance > 0 ? STATE.balance.toFixed(2) : '0.00';
                    }
                }
            });

            DOM.withdrawalModal.addEventListener('show.bs.modal', () => {
                this.reset();
            });

            DOM.withdrawalForm.addEventListener('submit', (event) => {
                this.submit(event);
            });
        },
    };

    const App = {
        async init() {
            WithdrawalFormManager.init();
            TableManager.initTransactions();
            TableManager.initWithdrawalRequests();
            await BalanceManager.refresh();
            WithdrawalFormManager.syncAmount();
        },
    };

    App.init();
}
