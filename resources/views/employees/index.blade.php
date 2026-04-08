<x-layouts.app title="Employees">
    <div id="employees-page" data-store-url="{{ route('employees.store') }}"
        data-show-url-template="{{ route('employees.show', ['employee' => '__id__']) }}"
        data-update-url-template="{{ route('employees.update', ['employee' => '__id__']) }}"
        data-destroy-url-template="{{ route('employees.destroy', ['employee' => '__id__']) }}"
        data-onboarding-url-template="{{ route('employees.move-to-onboarding', ['employee' => '__id__']) }}"
        data-confirm-join-url-template="{{ route('employees.confirm-join', ['employee' => '__id__']) }}"
        data-options-url="{{ route('employees.options') }}"
        data-sub-companies-all-url="{{ route('sub-companies.all') }}" data-squads-all-url="{{ route('squads.all') }}"
        data-stats-url="{{ route('employees.stats') }}" data-datatable-url="{{ route('employees.datatable') }}">

        <x-ui.page-header title="Employees"
            description="Manage your organization's workforce with detailed profiles and people placement.">
            <x-slot:actions>
                <div class="d-flex flex-wrap justify-content-md-end gap-2">
                    <button type="button" class="btn btn-outline-primary px-4 py-2 fw-bold" data-bs-toggle="collapse"
                        data-bs-target="#employees-search-filters" aria-expanded="false"
                        aria-controls="employees-search-filters">
                        <i class="bx bx-filter-alt me-1"></i>Search & Filters
                    </button>

                    @can('employees.create')
                        <button id="btn-create-employee" type="button" class="btn btn-primary px-4 py-2 shadow-sm fw-bold"
                            data-bs-toggle="modal" data-bs-target="#employeeFormModal" data-modal-mode="create">
                            + Add New Employee
                        </button>
                    @endcan
                </div>
            </x-slot:actions>
        </x-ui.page-header>

        @php
            $employeeStatusOptions = \App\Enums\EmployeeStatus::options();
            $systemRoleOptions = \App\Enums\SystemRole::options();
        @endphp

        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-sm-6">
                <x-ui.stat-card label="Pending" value-id="summary-pending" value-class="text-success"
                    :loading="true" border-tone="active" icon-tone="success">
                    <i class="bx bx-time-five bx-sm"></i>
                </x-ui.stat-card>
            </div>

            <div class="col-xl-3 col-sm-6">
                <x-ui.stat-card label="Onboarding" value-id="summary-onboarding" value-class="text-warning"
                    :loading="true" border-tone="pending" icon-tone="warning">
                    <i class="bx bx-loader-circle bx-sm"></i>
                </x-ui.stat-card>
            </div>

            <div class="col-xl-3 col-sm-6">
                <x-ui.stat-card label="Joined" value-id="summary-joined" value-class="text-info"
                    :loading="true" border-tone="active" icon-tone="primary">
                    <i class="bx bx-user-check bx-sm"></i>
                </x-ui.stat-card>
            </div>

            <div class="col-xl-3 col-sm-6">
                <x-ui.stat-card label="Terminated" value-id="summary-terminated" value-class="text-danger"
                    :loading="true" border-tone="inactive" icon-tone="danger">
                    <i class="bx bx-user-x bx-sm"></i>
                </x-ui.stat-card>
            </div>
        </div>

        <x-ui.search-filters-panel id="employees-search-filters" title="Search & Filters"
            description="Filters update in real time while you type or change values.">
            <form id="employees-search-form" class="row g-3">
                <div class="col-xl-4 col-md-6">
                    <label for="filter-employee-username" class="form-label fw-bold text-dark">Username</label>
                    <input type="text" id="filter-employee-username" name="search_username" class="form-control"
                        placeholder="Search by username" />
                </div>

                <div class="col-xl-4 col-md-6">
                    <label for="filter-employee-full-name" class="form-label fw-bold text-dark">Full Name</label>
                    <input type="text" id="filter-employee-full-name" name="search_full_name" class="form-control"
                        placeholder="Search by full name" />
                </div>

                <div class="col-xl-4 col-md-6">
                    <label for="filter-employee-email" class="form-label fw-bold text-dark">Email</label>
                    <input type="text" id="filter-employee-email" name="search_email" class="form-control"
                        placeholder="Search by email" />
                </div>

                <div class="col-xl-4 col-md-6">
                    <label for="filter-employee-status" class="form-label fw-bold text-dark">Status</label>
                    <select id="filter-employee-status" name="search_status" class="form-select select2-filter">
                        <option value="">All Statuses</option>
                        @foreach ($employeeStatusOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-xl-4 col-md-6">
                    <label for="filter-employee-role" class="form-label fw-bold text-dark">System Role</label>
                    <select id="filter-employee-role" name="search_role" class="form-select select2-filter">
                        <option value="">All Roles</option>
                        @foreach ($systemRoleOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 d-flex justify-content-end gap-2">
                    <button id="btn-reset-employee-filters" type="button" class="btn btn-light border px-4 fw-bold">
                        Reset
                    </button>
                </div>
            </form>
        </x-ui.search-filters-panel>

        <x-ui.data-table-card title="Employees Table" class="mb-5" body-class="ui-data-table-card-body px-0 pt-0 pb-3">
            <div class="table-responsive ui-data-table-scroll">
                <table id="employees-table" class="table table-hover align-middle w-100">
                    <thead class="table-custom-header">
                        <tr>
                            <th class="border-top-0 border-start-0 rounded-top-start">Username</th>
                            <th class="border-top-0">Email</th>
                            <th class="border-top-0">Phone</th>
                            <th class="border-top-0">Contract</th>
                            <th class="border-top-0">System Role</th>
                            <th class="border-top-0">Status</th>
                            <th class="border-top-0">Hire Date</th>
                            <th class="border-top-0 border-end-0 rounded-top-end text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0"></tbody>
                </table>
            </div>
        </x-ui.data-table-card>

        <x-employees.form-modal />
        <x-employees.view-modal />

    </div>
</x-layouts.app>