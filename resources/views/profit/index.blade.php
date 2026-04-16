<x-layouts.app title="Profit Management">
    @php
        $withdrawalStatusOptions = \App\Enums\WithdrawalRequestStatus::options();
    @endphp

    <div id="profit-page"
        data-options-url="{{ route('profit.options') }}"
        data-employees-url="{{ route('profit.employees') }}"
        data-withdrawal-requests-url="{{ route('profit.withdrawal-requests') }}"
        data-distribute-url="{{ route('profit.distribute') }}"
        data-approve-url-template="{{ route('profit.withdrawal-requests.approve', ['id' => '__id__']) }}"
        data-reject-url-template="{{ route('profit.withdrawal-requests.reject', ['id' => '__id__']) }}">

        <x-ui.page-header title="Profit Management"
            description="Distribute profit to employees and manage withdrawal requests.">
            <x-slot:actions>
                <div class="d-flex flex-wrap justify-content-md-end gap-2">
                    <button type="button" class="btn btn-outline-primary px-4 py-2 fw-bold" data-bs-toggle="tab"
                        data-bs-target="#profit-distribution-tab" role="tab" aria-controls="profit-distribution-tab"
                        aria-selected="true">
                        Distribute Profit
                    </button>
                    <button type="button" class="btn btn-outline-secondary px-4 py-2 fw-bold" data-bs-toggle="tab"
                        data-bs-target="#profit-withdrawals-tab" role="tab" aria-controls="profit-withdrawals-tab"
                        aria-selected="false">
                        Withdrawal Requests
                    </button>
                </div>
            </x-slot:actions>
        </x-ui.page-header>

        <ul class="nav nav-tabs border-0 mb-4 d-none" id="profit-tabs" role="tablist" aria-hidden="true">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="profit-distribution-tab-link" data-bs-toggle="tab"
                    data-bs-target="#profit-distribution-tab" type="button" role="tab" aria-controls="profit-distribution-tab"
                    aria-selected="true">Distribute Profit</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="profit-withdrawals-tab-link" data-bs-toggle="tab"
                    data-bs-target="#profit-withdrawals-tab" type="button" role="tab" aria-controls="profit-withdrawals-tab"
                    aria-selected="false">Withdrawal Requests</button>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="profit-distribution-tab" role="tabpanel"
                aria-labelledby="profit-distribution-tab-link">
                <div class="row g-4">
                    <div class="col-xl-8">
                        <x-ui.data-table-card title="Employees" class="h-100"
                            body-class="ui-data-table-card-body px-0 pt-0 pb-3">
                            <div class="p-3 border-bottom bg-light-subtle">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-5">
                                        <label for="profit-employee-search" class="form-label fw-bold text-dark">Search Employee</label>
                                        <input type="search" id="profit-employee-search" class="form-control"
                                            placeholder="Search by name or email" />
                                    </div>
                                    <div class="col-md-4">
                                        <label for="profit-squad-select" class="form-label fw-bold text-dark">Select Squad / Team</label>
                                        <select id="profit-squad-select" class="form-select select2-filter" data-placeholder="All Squads">
                                            <option value="">All Squads</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 d-grid gap-2">
                                        <button type="button" id="btn-select-all-employees" class="btn btn-primary fw-bold">
                                            Select All Employees
                                        </button>
                                        <button type="button" id="btn-select-squad-employees" class="btn btn-outline-primary fw-bold">
                                            Select Squad Members
                                        </button>
                                        <button type="button" id="btn-clear-employee-selection" class="btn btn-light border fw-bold">
                                            Clear Selection
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive ui-data-table-scroll">
                                <table id="profit-employees-table" class="table table-hover align-middle w-100 mb-0">
                                    <thead class="table-custom-header">
                                        <tr>
                                            <th class="border-top-0 border-start-0 rounded-top-start" style="width: 52px;">
                                                <input type="checkbox" id="profit-select-all-employees" class="form-check-input mt-0">
                                            </th>
                                            <th class="border-top-0">Employee</th>
                                            <th class="border-top-0">Squad</th>
                                            <th class="border-top-0">Sub-Company</th>
                                            <th class="border-top-0">Current Balance</th>
                                            <th class="border-top-0 border-end-0 rounded-top-end">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="profit-employees-tbody"></tbody>
                                </table>
                            </div>
                        </x-ui.data-table-card>
                    </div>

                    <div class="col-xl-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body p-4">
                                <h4 class="fw-bold text-dark mb-2">Distribute Profit</h4>
                                <p class="text-secondary mb-4">Select the employees, set an amount per employee, then submit the distribution.</p>

                                <form id="profit-distribute-form" class="vstack gap-3">
                                    <div>
                                        <label for="profit-distribution-amount" class="form-label fw-bold text-dark">Amount per Employee</label>
                                        <input type="number" id="profit-distribution-amount" name="amount" min="0" step="0.01"
                                            class="form-control form-control-lg" placeholder="0.00" />
                                    </div>

                                    <div class="bg-light rounded-4 p-3 border">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-secondary fw-semibold">Selected Employees</span>
                                            <strong id="profit-selected-count" class="text-dark">0</strong>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-secondary fw-semibold">Amount Per Employee</span>
                                            <strong id="profit-selected-amount" class="text-dark">USD 0.00</strong>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-secondary fw-semibold">Total Distribution</span>
                                            <strong id="profit-total-amount" class="text-primary">USD 0.00</strong>
                                        </div>
                                    </div>

                                    <div class="alert alert-info border-0 mb-0">
                                        Profit distribution is recorded atomically for each employee and each recipient receives an email notification.
                                    </div>

                                    <button type="submit" id="btn-distribute-profit" class="btn btn-primary btn-lg fw-bold w-100">
                                        Distribute Profit
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="profit-withdrawals-tab" role="tabpanel" aria-labelledby="profit-withdrawals-tab-link">
                <x-ui.search-filters-panel id="profit-withdrawal-search-filters" title="Search & Filters"
                    description="Filter withdrawal requests by employee name, status, or date range.">
                    <form id="profit-withdrawal-search-form" class="row g-3">
                        <div class="col-xl-4 col-md-6">
                            <label for="profit-withdrawal-search-employee" class="form-label fw-bold text-dark">Employee</label>
                            <input type="text" id="profit-withdrawal-search-employee" name="search_employee" class="form-control"
                                placeholder="Search by employee name" />
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <label for="profit-withdrawal-search-status" class="form-label fw-bold text-dark">Status</label>
                            <select id="profit-withdrawal-search-status" name="search_status" class="form-select select2-filter">
                                <option value="">All Statuses</option>
                                @foreach ($withdrawalStatusOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-xl-2 col-md-6">
                            <label for="profit-withdrawal-search-from" class="form-label fw-bold text-dark">From</label>
                            <input type="date" id="profit-withdrawal-search-from" name="search_from" class="form-control" />
                        </div>

                        <div class="col-xl-2 col-md-6">
                            <label for="profit-withdrawal-search-to" class="form-label fw-bold text-dark">To</label>
                            <input type="date" id="profit-withdrawal-search-to" name="search_to" class="form-control" />
                        </div>

                        <div class="col-12 d-flex justify-content-end gap-2">
                            <button id="btn-reset-profit-withdrawal-filters" type="button" class="btn btn-light border px-4 fw-bold">
                                Reset
                            </button>
                        </div>
                    </form>
                </x-ui.search-filters-panel>

                <x-ui.data-table-card title="Withdrawal Requests" class="mb-5"
                    body-class="ui-data-table-card-body px-0 pt-0 pb-3">
                    <div class="table-responsive ui-data-table-scroll">
                        <table id="profit-withdrawal-requests-table" class="table table-hover align-middle w-100">
                            <thead class="table-custom-header">
                                <tr>
                                    <th class="border-top-0 border-start-0 rounded-top-start">Employee</th>
                                    <th class="border-top-0">Amount</th>
                                    <th class="border-top-0">Request Date</th>
                                    <th class="border-top-0">Status</th>
                                    <th class="border-top-0 border-end-0 rounded-top-end text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0"></tbody>
                        </table>
                    </div>
                </x-ui.data-table-card>
            </div>
        </div>
    </div>
</x-layouts.app>
