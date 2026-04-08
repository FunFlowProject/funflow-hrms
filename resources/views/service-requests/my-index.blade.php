<x-layouts.app title="My Service Requests">
    <div id="my-service-requests-page" data-store-url="{{ route('service-requests.store') }}"
        data-show-url-template="{{ route('service-requests.show', ['serviceRequest' => '__id__']) }}"
        data-update-url-template="{{ route('service-requests.update', ['serviceRequest' => '__id__']) }}"
        data-destroy-url-template="{{ route('service-requests.show', ['serviceRequest' => '__id__']) }}"
        data-in-progress-url-template="{{ route('service-requests.move-to-in-progress', ['serviceRequest' => '__id__']) }}"
        data-complete-url-template="{{ route('service-requests.complete', ['serviceRequest' => '__id__']) }}"
        data-reject-url-template="{{ route('service-requests.reject', ['serviceRequest' => '__id__']) }}"
        data-options-url="{{ route('service-requests.options') }}"
        data-stats-url="{{ route('service-requests.stats') }}"
        data-datatable-url="{{ route('service-requests.datatable') }}">

        <x-ui.page-header title="My Service Requests"
            description="Submit and track your own requests through the HR/Admin fulfillment workflow.">
            <x-slot:actions>
                <div class="d-flex flex-wrap justify-content-md-end gap-2">
                    <button type="button" class="btn btn-outline-primary px-4 py-2 fw-bold" data-bs-toggle="collapse"
                        data-bs-target="#service-requests-search-filters" aria-expanded="false"
                        aria-controls="service-requests-search-filters">
                        <i class="bx bx-filter-alt me-1"></i>Search & Filters
                    </button>

                    @can('service-requests.create')
                        <button id="btn-create-service-request" type="button"
                            class="btn btn-primary px-4 py-2 shadow-sm fw-bold" data-bs-toggle="modal"
                            data-bs-target="#employeeFormModal" data-modal-mode="create">
                            + New Service Request
                        </button>
                    @endcan
                </div>
            </x-slot:actions>
        </x-ui.page-header>

        @php
            $statusOptions = \App\Enums\ServiceRequestStatus::options();
        @endphp

        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-sm-6">
                <x-ui.stat-card label="Submitted" value-id="summary-submitted" value-class="text-primary" :loading="true"
                    border-tone="active" icon-tone="primary">
                    <i class="bx bx-send bx-sm"></i>
                </x-ui.stat-card>
            </div>

            <div class="col-xl-3 col-sm-6">
                <x-ui.stat-card label="In Progress" value-id="summary-in-progress" value-class="text-warning"
                    :loading="true" border-tone="pending" icon-tone="warning">
                    <i class="bx bx-loader-circle bx-sm"></i>
                </x-ui.stat-card>
            </div>

            <div class="col-xl-3 col-sm-6">
                <x-ui.stat-card label="Completed" value-id="summary-completed" value-class="text-success" :loading="true"
                    border-tone="active" icon-tone="success">
                    <i class="bx bx-check-shield bx-sm"></i>
                </x-ui.stat-card>
            </div>

            <div class="col-xl-3 col-sm-6">
                <x-ui.stat-card label="Rejected" value-id="summary-rejected" value-class="text-danger" :loading="true"
                    border-tone="inactive" icon-tone="danger">
                    <i class="bx bx-x-circle bx-sm"></i>
                </x-ui.stat-card>
            </div>
        </div>

        <x-ui.search-filters-panel id="service-requests-search-filters" title="Search & Filters"
            description="Search your requests by service name, category, and status.">
            <form id="service-requests-search-form" class="row g-3">
                <div class="col-xl-4 col-md-6">
                    <label for="filter-service-request-service" class="form-label fw-bold text-dark">Service</label>
                    <input type="text" id="filter-service-request-service" name="search_service" class="form-control"
                        placeholder="Search by service" />
                </div>

                <div class="col-xl-4 col-md-6">
                    <label for="filter-service-request-category" class="form-label fw-bold text-dark">Category</label>
                    <input type="text" id="filter-service-request-category" name="search_category" class="form-control"
                        placeholder="Search by category" />
                </div>

                <div class="col-xl-4 col-md-6">
                    <label for="filter-service-request-status" class="form-label fw-bold text-dark">Status</label>
                    <select id="filter-service-request-status" name="search_status" class="form-select select2-filter">
                        <option value="">All Statuses</option>
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 d-flex justify-content-end gap-2">
                    <button id="btn-reset-service-request-filters" type="button" class="btn btn-light border px-4 fw-bold">
                        Reset
                    </button>
                </div>
            </form>
        </x-ui.search-filters-panel>

        <x-ui.data-table-card title="My Service Requests" class="mb-5" body-class="ui-data-table-card-body px-0 pt-0 pb-3">
            <div class="table-responsive ui-data-table-scroll">
                <table id="service-requests-table" class="table table-hover align-middle w-100">
                    <thead class="table-custom-header">
                        <tr>
                            <th class="border-top-0 border-start-0 rounded-top-start">Service</th>
                            <th class="border-top-0">Category</th>
                            <th class="border-top-0">Status</th>
                            <th class="border-top-0">Handled By</th>
                            <th class="border-top-0">Submitted At</th>
                            <th class="border-top-0 border-end-0 rounded-top-end text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0"></tbody>
                </table>
            </div>
        </x-ui.data-table-card>

        <x-service-requests.form-modal />
        <x-service-requests.view-modal />

    </div>
</x-layouts.app>
