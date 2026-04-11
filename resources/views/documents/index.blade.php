<x-layouts.app title="Documents">
    <div id="documents-page" data-store-url="{{ route('documents.store') }}"
        data-show-url-template="{{ route('documents.show', ['document' => '__id__']) }}" 
        data-status-info-url-template="{{ route('documents.status-info', ['document' => '__id__']) }}"
        data-update-url-template="{{ route('documents.update', ['document' => '__id__']) }}"
        data-destroy-url-template="{{ route('documents.destroy', ['document' => '__id__']) }}"
        data-sub-companies-all-url="{{ route('sub-companies.all') }}"
        data-squads-all-url="{{ route('squads.all') }}"
        data-stats-url="{{ route('documents.stats') }}"
        data-datatable-url="{{ route('documents.datatable') }}">

        <x-ui.page-header title="Document Management"
            description="Manage company documents, classifications, and scopes.">
            <x-slot:actions>
                <div class="d-flex flex-wrap justify-content-md-end gap-2">
                    <button type="button" class="btn btn-outline-primary px-4 py-2 fw-bold" data-bs-toggle="collapse"
                        data-bs-target="#documents-search-filters" aria-expanded="false"
                        aria-controls="documents-search-filters">
                        <i class="bx bx-filter-alt me-1"></i>Search & Filters
                    </button>

                    @can('documents.create')
                        <button id="btn-create-document" type="button" class="btn btn-primary px-4 py-2 shadow-sm fw-bold"
                            data-bs-toggle="modal" data-bs-target="#documentFormModal" data-modal-mode="create">
                            + Add New Document
                        </button>
                    @endcan
                </div>
            </x-slot:actions>
        </x-ui.page-header>

        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-sm-6">
                <x-ui.stat-card label="Total Documents" value-id="summary-total" value-class="text-primary" :loading="true"
                    border-tone="active" icon-tone="primary">
                    <i class="bx bx-file bx-sm"></i>
                </x-ui.stat-card>
            </div>

            <div class="col-xl-3 col-sm-6">
                <x-ui.stat-card label="Public" value-id="summary-public" value-class="text-success"
                    :loading="true" border-tone="active" icon-tone="success">
                    <i class="bx bx-world bx-sm"></i>
                </x-ui.stat-card>
            </div>

            <div class="col-xl-3 col-sm-6">
                <x-ui.stat-card label="Internal Use Only" value-id="summary-internal"
                    value-class="text-info" :loading="true" border-tone="active" icon-tone="info">
                    <i class="bx bx-building bx-sm"></i>
                </x-ui.stat-card>
            </div>

            <div class="col-xl-3 col-sm-6">
                <x-ui.stat-card label="Confidential" value-id="summary-confidential"
                    value-class="text-danger" :loading="true" border-tone="pending" icon-tone="danger">
                    <i class="bx bx-shield-quarter bx-sm"></i>
                </x-ui.stat-card>
            </div>
        </div>

        <x-ui.search-filters-panel id="documents-search-filters" title="Search & Filters"
            description="Search by document name, classification, or scope.">
            <form id="documents-search-form" class="row g-3">
                <div class="col-lg-4 col-md-6">
                    <label for="filter-document-name" class="form-label fw-bold text-dark">Name</label>
                    <input type="text" id="filter-document-name" name="search_name" class="form-control"
                        placeholder="Search by document name" />
                </div>

                <div class="col-lg-4 col-md-6">
                    <label for="filter-document-classification" class="form-label fw-bold text-dark">Classification</label>
                    <select id="filter-document-classification" name="search_classification" class="form-select select2-filter">
                        <option value="">All Classifications</option>
                        <option value="public">Public</option>
                        <option value="internal_use_only">Internal Use Only</option>
                        <option value="confidential">Confidential</option>
                    </select>
                </div>

                <div class="col-lg-4 col-md-6">
                    <label for="filter-document-scope" class="form-label fw-bold text-dark">Scope</label>
                    <select id="filter-document-scope" name="search_scope" class="form-select select2-filter">
                        <option value="">All Scopes</option>
                        <option value="company">Company-wide</option>
                        <option value="sub_company">Sub-Company</option>
                        <option value="squad">Squad</option>
                    </select>
                </div>

                <div class="col-12 d-flex justify-content-end gap-2">
                    <button id="btn-reset-document-filters" type="button" class="btn btn-light border px-4 fw-bold">
                        Reset
                    </button>
                </div>
            </form>
        </x-ui.search-filters-panel>

        <x-ui.data-table-card title="Documents Table" class="mb-5" body-class="ui-data-table-card-body px-0 pt-0 pb-3">
            <div class="table-responsive ui-data-table-scroll">
                <table id="documents-table" class="table table-hover align-middle w-100">
                    <thead class="table-custom-header">
                        <tr>
                            <th class="border-top-0 border-start-0 rounded-top-start">Name</th>
                            <th class="border-top-0">Classification</th>
                            <th class="border-top-0">Scope</th>
                            <th class="border-top-0">Requires Acknowledgment</th>
                            <th class="border-top-0">Created At</th>
                            <th class="border-top-0 border-end-0 rounded-top-end text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0"></tbody>
                </table>
            </div>
        </x-ui.data-table-card>

        <x-documents.form-modal />
        <x-documents.status-modal />

    </div>
</x-layouts.app>
