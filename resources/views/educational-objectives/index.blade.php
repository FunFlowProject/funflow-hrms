<x-layouts.app title="Educational Objectives">
    <div id="educational-objectives-page" data-store-url="{{ route('educational-objectives.store') }}"
        data-destroy-url-template="{{ route('educational-objectives.destroy', ['objective' => '__id__']) }}"
        data-sub-companies-all-url="{{ route('sub-companies.all') }}"
        data-squads-all-url="{{ route('squads.all') }}"
        data-stats-url="{{ route('educational-objectives.stats') }}"
        data-datatable-url="{{ route('educational-objectives.datatable') }}"
        data-employees-all-url="{{ route('educational-objectives.employees-all') }}"
        data-progress-url-template="{{ route('educational-objectives.progress', ['objective' => '__id__']) }}">

        <x-ui.page-header title="Learning & Development"
            description="Manage, assign, and track employee educational objectives.">
            <x-slot:actions>
                <div class="d-flex flex-wrap justify-content-md-end gap-2">
                    <button type="button" class="btn btn-outline-primary px-4 py-2 fw-bold" data-bs-toggle="collapse"
                        data-bs-target="#objectives-search-filters" aria-expanded="false"
                        aria-controls="objectives-search-filters">
                        <i class="bx bx-filter-alt me-1"></i>Search & Filters
                    </button>

                    @can('educational-objectives.manage')
                        <button id="btn-create-objective" type="button" class="btn btn-primary px-4 py-2 shadow-sm fw-bold"
                            data-bs-toggle="modal" data-bs-target="#objectiveFormModal" data-modal-mode="create">
                            + Assign Objective
                        </button>
                    @endcan
                </div>
            </x-slot:actions>
        </x-ui.page-header>

        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-sm-6">
                <x-ui.stat-card label="Total Assignments" value-id="summary-total" value-class="text-primary" :loading="true"
                    border-tone="active" icon-tone="primary">
                    <i class="bx bx-book-open bx-sm"></i>
                </x-ui.stat-card>
            </div>

            <div class="col-xl-3 col-sm-6">
                <x-ui.stat-card label="In Progress" value-id="summary-in-progress" value-class="text-info"
                    :loading="true" border-tone="active" icon-tone="info">
                    <i class="bx bx-loader-circle bx-sm"></i>
                </x-ui.stat-card>
            </div>

            <div class="col-xl-3 col-sm-6">
                <x-ui.stat-card label="Overdue" value-id="summary-overdue"
                    value-class="text-danger" :loading="true" border-tone="pending" icon-tone="danger">
                    <i class="bx bx-time-five bx-sm"></i>
                </x-ui.stat-card>
            </div>

            <div class="col-xl-3 col-sm-6">
                <x-ui.stat-card label="Completed" value-id="summary-completed"
                    value-class="text-success" :loading="true" border-tone="active" icon-tone="success">
                    <i class="bx bx-check-shield bx-sm"></i>
                </x-ui.stat-card>
            </div>
        </div>

        <x-ui.search-filters-panel id="objectives-search-filters" title="Search & Filters"
            description="Search by objective name, priority, or scope.">
            <form id="objectives-search-form" class="row g-3">
                <div class="col-lg-4 col-md-6">
                    <label for="filter-objective-name" class="form-label fw-bold text-dark">Name</label>
                    <input type="text" id="filter-objective-name" name="search_name" class="form-control"
                        placeholder="Search by objective name" />
                </div>

                <div class="col-lg-4 col-md-6">
                    <label for="filter-objective-priority" class="form-label fw-bold text-dark">Priority</label>
                    <select id="filter-objective-priority" name="search_priority" class="form-select select2-filter">
                        <option value="">All Priorities</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>

                <div class="col-lg-4 col-md-6">
                    <label for="filter-objective-scope" class="form-label fw-bold text-dark">Scope</label>
                    <select id="filter-objective-scope" name="search_scope" class="form-select select2-filter">
                        <option value="">All Scopes</option>
                        <option value="company">Company-wide</option>
                        <option value="sub_company">Sub-Company</option>
                        <option value="squad">Squad</option>
                        <option value="individual">Individual</option>
                    </select>
                </div>

                <div class="col-12 d-flex justify-content-end gap-2">
                    <button id="btn-reset-objective-filters" type="button" class="btn btn-light border px-4 fw-bold">
                        Reset
                    </button>
                </div>
            </form>
        </x-ui.search-filters-panel>

        <x-ui.data-table-card title="Objectives Table" class="mb-5" body-class="ui-data-table-card-body px-0 pt-0 pb-3">
            <div class="table-responsive ui-data-table-scroll">
                <table id="objectives-table" class="table table-hover align-middle w-100">
                    <thead class="table-custom-header">
                        <tr>
                            <th class="border-top-0 border-start-0 rounded-top-start">Name</th>
                            <th class="border-top-0">Priority</th>
                            <th class="border-top-0">Scope</th>
                            <th class="border-top-0">Target Date</th>
                            <th class="border-top-0">Progress</th>
                            <th class="border-top-0 border-end-0 rounded-top-end text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0"></tbody>
                </table>
            </div>
        </x-ui.data-table-card>

        <x-educational-objectives.form-modal />
        <x-educational-objectives.progress-modal />

    </div>
</x-layouts.app>
