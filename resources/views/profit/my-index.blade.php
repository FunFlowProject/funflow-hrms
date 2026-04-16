<x-layouts.app title="My Profit">
    <div id="my-profit-page"
        data-balance-url="{{ route('my-profit.balance') }}"
        data-transactions-url="{{ route('my-profit.transactions') }}"
        data-withdrawal-requests-url="{{ route('my-profit.withdrawal-requests') }}"
        data-withdraw-url="{{ route('my-profit.withdraw') }}">

        <x-ui.page-header title="My Profit" description="View your current profit balance, request withdrawals, and review your transaction history.">
            <x-slot:actions>
                <button type="button" id="btn-open-profit-withdrawal-modal" class="btn btn-primary px-4 py-2 fw-bold shadow-sm"
                    data-bs-toggle="modal" data-bs-target="#profitWithdrawalModal">
                    Request Withdrawal
                </button>
            </x-slot:actions>
        </x-ui.page-header>

        <div class="row g-4 mb-4">
            <div class="col-xl-4 col-md-6">
                <x-ui.stat-card label="Available Profit" value-id="current-profit-balance" value-class="text-success" :loading="true"
                    border-tone="active" icon-tone="success" updated-text="Updated just now">
                    <i class="bx bx-wallet bx-sm"></i>
                </x-ui.stat-card>
            </div>
            <div class="col-xl-8 col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <h4 class="fw-bold text-dark mb-2">Withdraw Profit</h4>
                            <p class="text-secondary mb-0">Request a full or partial withdrawal from your available balance.</p>
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-outline-primary fw-bold px-4" data-bs-toggle="modal"
                                data-bs-target="#profitWithdrawalModal">
                                Open Withdrawal Form
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12">
                <x-ui.data-table-card title="Transaction History" class="mb-0"
                    body-class="ui-data-table-card-body px-0 pt-0 pb-3">
                    <div class="table-responsive ui-data-table-scroll">
                        <table id="profit-transactions-table" class="table table-hover align-middle w-100">
                            <thead class="table-custom-header">
                                <tr>
                                    <th class="border-top-0 border-start-0 rounded-top-start">Date</th>
                                    <th class="border-top-0">Type</th>
                                    <th class="border-top-0">Amount</th>
                                    <th class="border-top-0">Balance After</th>
                                    <th class="border-top-0">Description</th>
                                    <th class="border-top-0">Status</th>
                                    <th class="border-top-0 border-end-0 rounded-top-end">Performed By</th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0"></tbody>
                        </table>
                    </div>
                </x-ui.data-table-card>
            </div>

            <div class="col-12">
                <x-ui.data-table-card title="Withdrawal Requests" class="mb-0"
                    body-class="ui-data-table-card-body px-0 pt-0 pb-3">
                    <div class="table-responsive ui-data-table-scroll">
                        <table id="my-profit-withdrawal-requests-table" class="table table-hover align-middle w-100">
                            <thead class="table-custom-header">
                                <tr>
                                    <th class="border-top-0 border-start-0 rounded-top-start">Request Date</th>
                                    <th class="border-top-0">Amount</th>
                                    <th class="border-top-0">Status</th>
                                    <th class="border-top-0">Acted At</th>
                                    <th class="border-top-0 border-end-0 rounded-top-end">Rejection Reason</th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0"></tbody>
                        </table>
                    </div>
                </x-ui.data-table-card>
            </div>
        </div>

        <div class="modal fade" id="profitWithdrawalModal" tabindex="-1" aria-labelledby="profitWithdrawalModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header border-0 pb-0">
                        <div>
                            <h5 class="modal-title fw-bold" id="profitWithdrawalModalLabel">Request Withdrawal</h5>
                            <p class="text-secondary mb-0">Choose whether to withdraw the full balance or a partial amount.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body pt-3">
                        <form id="profit-withdrawal-form" class="vstack gap-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="withdrawal_mode"
                                            id="profit-withdrawal-mode-full" value="full" checked>
                                        <label class="form-check-label fw-semibold" for="profit-withdrawal-mode-full">Full Balance</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="withdrawal_mode"
                                            id="profit-withdrawal-mode-partial" value="partial">
                                        <label class="form-check-label fw-semibold" for="profit-withdrawal-mode-partial">Partial Amount</label>
                                    </div>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <span class="text-secondary d-block">Current Balance</span>
                                    <strong id="profit-withdrawal-current-balance" class="fs-5 text-dark">USD 0.00</strong>
                                </div>
                            </div>

                            <div>
                                <label for="profit-withdrawal-amount" class="form-label fw-bold text-dark">Withdrawal Amount</label>
                                <input type="number" id="profit-withdrawal-amount" name="amount" min="0" step="0.01"
                                    class="form-control form-control-lg" placeholder="0.00" readonly />
                            </div>

                            <div class="alert alert-info border-0 mb-0">
                                Your withdrawal request will be created with a pending status and processed by an administrator.
                            </div>
                        </form>
                    </div>

                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light border fw-bold" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" form="profit-withdrawal-form" id="profit-withdrawal-submit" class="btn btn-primary fw-bold px-4">
                            Submit Request
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
