<x-layouts.app title="Sub-Companies">
    <div id="sub-companies-page" data-store-url="{{ route('sub-companies.store') }}"
        data-show-url-template="{{ route('sub-companies.show', ['subCompany' => '__id__']) }}"
        data-update-url-template="{{ route('sub-companies.update', ['subCompany' => '__id__']) }}"
        data-destroy-url-template="{{ route('sub-companies.destroy', ['subCompany' => '__id__']) }}"
        data-stats-url="{{ route('sub-companies.stats') }}" data-datatable-url="{{ route('sub-companies.datatable') }}">

        <x-ui.page-header title="Sub-Companies"
            description="Manage sub-company structures, ownership scope, and workforce boundaries.">
            <x-slot:actions>
                <div class="d-flex flex-wrap justify-content-md-end gap-2">
                    <button type="button" class="btn btn-outline-primary px-4 py-2 fw-bold" data-bs-toggle="collapse"
                        data-bs-target="#sub-companies-search-filters" aria-expanded="false"
                        aria-controls="sub-companies-search-filters">
                        <i class="bx bx-filter-alt me-1"></i>Search & Filters
                    </button>

                    @can('sub-companies.create')
                        <button id="btn-create-sub-company" type="button"
                            class="btn btn-primary px-4 py-2 shadow-sm fw-bold" data-bs-toggle="modal"
                            data-bs-target="#employeeFormModal" data-modal-mode="create">
                            + Add New Sub-Company
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
                <x-ui.stat-card label="Sub-Companies" value-id="summary-sub-companies" value-class="text-primary"
                    :loading="true" border-tone="active" icon-tone="primary">
                    <i class="bx bx-buildings bx-sm"></i>
                </x-ui.stat-card>
            </div>

            <div class="col-xl-3 col-sm-6">
                <x-ui.stat-card label="With Squads" value-id="summary-with-squads" value-class="text-success"
                    :loading="true" border-tone="active" icon-tone="success">
                    <i class="bx bx-layer-plus bx-sm"></i>
                </x-ui.stat-card>
            </div>

            <div class="col-xl-3 col-sm-6">
                <x-ui.stat-card label="Without Squads" value-id="summary-without-squads" value-class="text-warning"
                    :loading="true" border-tone="pending" icon-tone="warning">
                    <i class="bx bx-layer-minus bx-sm"></i>
                </x-ui.stat-card>
            </div>

            <div class="col-xl-3 col-sm-6">
                <x-ui.stat-card label="With Worker Assignments" value-id="summary-with-assignments" value-class="text-info"
                    :loading="true" border-tone="active" icon-tone="info">
                    <i class="bx bx-user-pin bx-sm"></i>
                </x-ui.stat-card>
            </div>
        </div>

        <x-ui.search-filters-panel id="sub-companies-search-filters" title="Search & Filters"
            description="Search by name, description, or active status to narrow the sub-company table.">
            <form id="sub-companies-search-form" class="row g-3">
                <div class="col-lg-4 col-md-6">
                    <label for="filter-sub-company-name" class="form-label fw-bold text-dark">Name</label>
                    <input type="text" id="filter-sub-company-name" name="search_name" class="form-control"
                        placeholder="Search by name" />
                </div>

                <div class="col-lg-4 col-md-6">
                    <label for="filter-sub-company-description" class="form-label fw-bold text-dark">Description</label>
                    <input type="text" id="filter-sub-company-description" name="search_description"
                        class="form-control" placeholder="Search by description" />
                </div>

                <div class="col-lg-4 col-md-6">
                    <label for="filter-sub-company-active" class="form-label fw-bold text-dark">Status</label>
                    <select id="filter-sub-company-active" name="search_active" class="form-select select2-filter">
                        <option value="">All Statuses</option>
                        @foreach ($activeOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 d-flex justify-content-end gap-2">
                    <button id="btn-reset-sub-company-filters" type="button" class="btn btn-light border px-4 fw-bold">
                        Reset
                    </button>
                </div>
            </form>
        </x-ui.search-filters-panel>

        <x-ui.data-table-card title="Sub-Companies Table" class="mb-5"
            body-class="ui-data-table-card-body px-0 pt-0 pb-3">
            <div class="table-responsive ui-data-table-scroll">
                <table id="sub-companies-table" class="table table-hover align-middle w-100">
                    <thead class="table-custom-header">
                        <tr>
                            <th class="border-top-0 border-start-0 rounded-top-start">Name</th>
                            <th class="border-top-0">Description</th>
                            <th class="border-top-0">Squads</th>
                            <th class="border-top-0">Worker Assignments</th>
                            <th class="border-top-0">Created At</th>
                            <th class="border-top-0 border-end-0 rounded-top-end text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0"></tbody>
                </table>
            </div>
        </x-ui.data-table-card>

        <x-sub-companies.form-modal />
        <x-sub-companies.view-modal />

    </div>
</x-layouts.app>