<x-layouts.app title="Service Catalog">
    <div id="service-catalog-page" data-store-url="{{ route('service-catalog.store') }}"
        data-show-url-template="{{ route('service-catalog.show', ['serviceCatalog' => '__id__']) }}"
        data-update-url-template="{{ route('service-catalog.update', ['serviceCatalog' => '__id__']) }}"
        data-destroy-url-template="{{ route('service-catalog.destroy', ['serviceCatalog' => '__id__']) }}"
        data-stats-url="{{ route('service-catalog.stats') }}"
        data-datatable-url="{{ route('service-catalog.datatable') }}">

        <x-ui.page-header title="Service Catalog"
            description="Manage available employee services, categories, and request requirements.">
            <x-slot:actions>
                <div class="d-flex flex-wrap justify-content-md-end gap-2">
                    <button type="button" class="btn btn-outline-primary px-4 py-2 fw-bold" data-bs-toggle="collapse"
                        data-bs-target="#service-catalog-search-filters" aria-expanded="false"
                        aria-controls="service-catalog-search-filters">
                        <i class="bx bx-filter-alt me-1"></i>Search & Filters
                    </button>

                    @can('service-catalog.create')
                        <button id="btn-create-service-catalog" type="button"
                            class="btn btn-primary px-4 py-2 shadow-sm fw-bold" data-bs-toggle="modal"
                            data-bs-target="#employeeFormModal" data-modal-mode="create">
                            + Add New Service
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
                <x-ui.stat-card label="Services" value-id="summary-service-catalog" value-class="text-primary" :loading="true"
                    border-tone="active" icon-tone="primary">
                    <i class="bx bx-briefcase bx-sm"></i>
                </x-ui.stat-card>
            </div>

            <div class="col-xl-3 col-sm-6">
                <x-ui.stat-card label="Active Services" value-id="summary-active-services" value-class="text-success" :loading="true"
                    border-tone="active" icon-tone="success">
                    <i class="bx bx-check-shield bx-sm"></i>
                </x-ui.stat-card>
            </div>

            <div class="col-xl-3 col-sm-6">
                <x-ui.stat-card label="Needs Justification" value-id="summary-requires-justification" value-class="text-warning"
                    :loading="true" border-tone="pending" icon-tone="warning">
                    <i class="bx bx-message-detail bx-sm"></i>
                </x-ui.stat-card>
            </div>

            <div class="col-xl-3 col-sm-6">
                <x-ui.stat-card label="Categories" value-id="summary-categories" value-class="text-info" :loading="true"
                    border-tone="active" icon-tone="info">
                    <i class="bx bx-category bx-sm"></i>
                </x-ui.stat-card>
            </div>
        </div>

        <x-ui.search-filters-panel id="service-catalog-search-filters" title="Search & Filters"
            description="Search by service name, category, requirement, or status.">
            <form id="service-catalog-search-form" class="row g-3">
                <div class="col-lg-3 col-md-6">
                    <label for="filter-service-catalog-name" class="form-label fw-bold text-dark">Service Name</label>
                    <input type="text" id="filter-service-catalog-name" name="search_name" class="form-control"
                        placeholder="Search by service name" />
                </div>

                <div class="col-lg-3 col-md-6">
                    <label for="filter-service-catalog-category" class="form-label fw-bold text-dark">Category</label>
                    <input type="text" id="filter-service-catalog-category" name="search_category" class="form-control"
                        placeholder="Search by category" />
                </div>

                <div class="col-lg-3 col-md-6">
                    <label for="filter-service-catalog-requires-justification" class="form-label fw-bold text-dark">Justification</label>
                    <select id="filter-service-catalog-requires-justification" name="search_requires_justification"
                        class="form-select select2-filter">
                        <option value="">All</option>
                        <option value="1">Required</option>
                        <option value="0">Not Required</option>
                    </select>
                </div>

                <div class="col-lg-3 col-md-6">
                    <label for="filter-service-catalog-active" class="form-label fw-bold text-dark">Status</label>
                    <select id="filter-service-catalog-active" name="search_active" class="form-select select2-filter">
                        <option value="">All Statuses</option>
                        @foreach ($activeOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 d-flex justify-content-end gap-2">
                    <button id="btn-reset-service-catalog-filters" type="button" class="btn btn-light border px-4 fw-bold">
                        Reset
                    </button>
                </div>
            </form>
        </x-ui.search-filters-panel>

        <x-ui.data-table-card title="Service Catalog Table" class="mb-5"
            body-class="ui-data-table-card-body px-0 pt-0 pb-3">
            <div class="table-responsive ui-data-table-scroll">
                <table id="service-catalog-table" class="table table-hover align-middle w-100">
                    <thead class="table-custom-header">
                        <tr>
                            <th class="border-top-0 border-start-0 rounded-top-start">Service</th>
                            <th class="border-top-0">Category</th>
                            <th class="border-top-0">Justification</th>
                            <th class="border-top-0">Status</th>
                            <th class="border-top-0">Created At</th>
                            <th class="border-top-0 border-end-0 rounded-top-end text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0"></tbody>
                </table>
            </div>
        </x-ui.data-table-card>

        <x-service-catalog.form-modal />
        <x-service-catalog.view-modal />

    </div>
</x-layouts.app>
