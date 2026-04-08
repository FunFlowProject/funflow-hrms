<x-layouts.app title="Squads">
    <div id="squads-page" data-store-url="{{ route('squads.store') }}"
        data-show-url-template="{{ route('squads.show', ['squad' => '__id__']) }}"
        data-update-url-template="{{ route('squads.update', ['squad' => '__id__']) }}"
        data-destroy-url-template="{{ route('squads.destroy', ['squad' => '__id__']) }}"
        data-sub-companies-all-url="{{ route('sub-companies.all') }}" data-stats-url="{{ route('squads.stats') }}"
        data-datatable-url="{{ route('squads.datatable') }}">

        <x-ui.page-header title="Squads"
            description="Manage squads, map them to sub-companies, and monitor workforce readiness.">
            <x-slot:actions>
                <div class="d-flex flex-wrap justify-content-md-end gap-2">
                    <button type="button" class="btn btn-outline-primary px-4 py-2 fw-bold" data-bs-toggle="collapse"
                        data-bs-target="#squads-search-filters" aria-expanded="false"
                        aria-controls="squads-search-filters">
                        <i class="bx bx-filter-alt me-1"></i>Search & Filters
                    </button>

                    @can('squads.create')
                        <button id="btn-create-squad" type="button" class="btn btn-primary px-4 py-2 shadow-sm fw-bold"
                            data-bs-toggle="modal" data-bs-target="#employeeFormModal" data-modal-mode="create">
                            + Add New Squad
                        </button>
                    @endcan
                </div>
            </x-slot:actions>
        </x-ui.page-header>

        @php
            $activeOptions = \App\Enums\ActiveStatus::options();
        @endphp

        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-sm-6">
                <x-ui.stat-card label="Squads" value-id="summary-squads" value-class="text-primary" :loading="true"
                    border-tone="active" icon-tone="primary">
                    <i class="bx bx-grid-alt bx-sm"></i>
                </x-ui.stat-card>
            </div>

            <div class="col-xl-3 col-sm-6">
                <x-ui.stat-card label="With Worker Assignments" value-id="summary-with-assignments" value-class="text-success"
                    :loading="true" border-tone="active" icon-tone="success">
                    <i class="bx bx-user-check bx-sm"></i>
                </x-ui.stat-card>
            </div>

            <div class="col-xl-3 col-sm-6">
                <x-ui.stat-card label="Without Worker Assignments" value-id="summary-without-assignments"
                    value-class="text-warning" :loading="true" border-tone="pending" icon-tone="warning">
                    <i class="bx bx-user-x bx-sm"></i>
                </x-ui.stat-card>
            </div>

            <div class="col-xl-3 col-sm-6">
                <x-ui.stat-card label="Covered Sub-Companies" value-id="summary-covered-sub-companies"
                    value-class="text-info" :loading="true" border-tone="active" icon-tone="info">
                    <i class="bx bx-buildings bx-sm"></i>
                </x-ui.stat-card>
            </div>
        </div>

        <x-ui.search-filters-panel id="squads-search-filters" title="Search & Filters"
            description="Search by squad name, sub-company, or active status to narrow the squads table.">
            <form id="squads-search-form" class="row g-3">
                <div class="col-lg-4 col-md-6">
                    <label for="filter-squad-name" class="form-label fw-bold text-dark">Name</label>
                    <input type="text" id="filter-squad-name" name="search_name" class="form-control"
                        placeholder="Search by squad name" />
                </div>

                <div class="col-lg-4 col-md-6">
                    <label for="filter-squad-sub-company-id" class="form-label fw-bold text-dark">Sub-Company</label>
                    <select id="filter-squad-sub-company-id" name="search_sub_company_id"
                        class="form-select select2-filter">
                        <option value="">All Sub-Companies</option>
                    </select>
                </div>

                <div class="col-lg-4 col-md-6">
                    <label for="filter-squad-active" class="form-label fw-bold text-dark">Status</label>
                    <select id="filter-squad-active" name="search_active" class="form-select select2-filter">
                        <option value="">All Statuses</option>
                        @foreach ($activeOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 d-flex justify-content-end gap-2">
                    <button id="btn-reset-squad-filters" type="button" class="btn btn-light border px-4 fw-bold">
                        Reset
                    </button>
                </div>
            </form>
        </x-ui.search-filters-panel>

        <x-ui.data-table-card title="Squads Table" class="mb-5" body-class="ui-data-table-card-body px-0 pt-0 pb-3">
            <div class="table-responsive ui-data-table-scroll">
                <table id="squads-table" class="table table-hover align-middle w-100">
                    <thead class="table-custom-header">
                        <tr>
                            <th class="border-top-0 border-start-0 rounded-top-start">Name</th>
                            <th class="border-top-0">Sub-Company</th>
                            <th class="border-top-0">Worker Assignments</th>
                            <th class="border-top-0">Created At</th>
                            <th class="border-top-0 border-end-0 rounded-top-end text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0"></tbody>
                </table>
            </div>
        </x-ui.data-table-card>

        <x-squads.form-modal />
        <x-squads.view-modal />

    </div>
</x-layouts.app>